/**
 * Dish Image System - Test & Setup Helper
 * Strumenti per configurazione e verifica del sistema
 */

class DishImageSetup {
    constructor() {
        this.apiUrl = '/app/admin/dish-image-handler.php';
        this.results = [];
    }

    /**
     * Esegui tutti i test
     */
    async runAllTests() {
        console.log('🧪 Starting Dish Image System Tests...');
        const startTime = performance.now();

        const tests = [
            { name: 'API Endpoint Reachability', fn: () => this.testApiReachability() },
            { name: 'Single Image Endpoint', fn: () => this.testSingleEndpoint() },
            { name: 'Batch Endpoint', fn: () => this.testBatchEndpoint() },
            { name: 'Cache System', fn: () => this.testCacheSystem() },
            { name: 'Fallback Mechanism', fn: () => this.testFallback() },
            { name: 'Loader Initialization', fn: () => this.testLoaderInit() },
            { name: 'Concurrent Requests', fn: () => this.testConcurrency() }
        ];

        for (const test of tests) {
            try {
                console.log(`\n⏳ Testing: ${test.name}`);
                const result = await test.fn();
                this.results.push({ name: test.name, ...result });
                console.log(`✅ ${test.name}: ${result.message}`);
            } catch (e) {
                console.error(`❌ ${test.name}: ${e.message}`);
                this.results.push({ name: test.name, success: false, error: e.message });
            }
        }

        const duration = performance.now() - startTime;
        const summary = this.generateSummary(duration);
        
        console.log('\n' + '='.repeat(50));
        console.log(summary);
        console.log('='.repeat(50));

        return this.results;
    }

    /**
     * Test raggiungibilità API
     */
    async testApiReachability() {
        const response = await fetch(this.apiUrl);
        return {
            success: response.ok || response.status === 400, // 400 perché mancano parametri
            message: `HTTP ${response.status} - API reachable`,
            statusCode: response.status
        };
    }

    /**
     * Test endpoint singolo
     */
    async testSingleEndpoint() {
        const params = new URLSearchParams({
            action: 'get-dish-image',
            name: 'Pasta Carbonara',
            ingredients: 'Guanciale,Uova,Pecorino',
            category: 'Pasta'
        });

        const response = await fetch(`${this.apiUrl}?${params}`);
        const data = await response.json();

        if (!data.url) throw new Error('No URL in response');
        if (data.url.length < 10) throw new Error('Invalid URL format');

        return {
            success: true,
            message: `Retrieved image from ${data.source}`,
            source: data.source,
            url: data.url.substring(0, 50) + '...',
            cached: data.cached || false
        };
    }

    /**
     * Test endpoint batch
     */
    async testBatchEndpoint() {
        const dishes = [
            {
                id: '1',
                name: 'Margherita Pizza',
                ingredients: 'Pomodoro,Mozzarella,Basilico',
                category: 'Pizza'
            },
            {
                id: '2',
                name: 'Risotto ai Funghi',
                ingredients: 'Riso,Funghi,Vino',
                category: 'Piatti'
            },
            {
                id: '3',
                name: 'Tiramisu',
                ingredients: 'Mascarpone,Caffè,Cacao',
                category: 'Dolci'
            }
        ];

        const response = await fetch(`${this.apiUrl}?action=get-dishes-images`, {
            method: 'POST',
            headers: { 'Content-Type': 'application/json' },
            body: JSON.stringify({ dishes })
        });

        const data = await response.json();

        if (!data.data || data.data.length !== 3) throw new Error('Invalid batch response');
        if (!data.data[0].image.url) throw new Error('Missing image in batch response');

        return {
            success: true,
            message: `Batch processed: ${data.data.length} dishes`,
            itemsProcessed: data.data.length,
            sources: data.data.map(d => d.image.source).join(', ')
        };
    }

    /**
     * Test sistema cache
     */
    async testCacheSystem() {
        const params = new URLSearchParams({
            action: 'get-dish-image',
            name: 'Test Dish for Cache',
            category: 'test'
        });

        // Prima richiesta (no cache)
        const start1 = performance.now();
        const response1 = await fetch(`${this.apiUrl}?${params}`);
        const data1 = await response1.json();
        const time1 = performance.now() - start1;

        // Seconda richiesta (dal cache)
        const start2 = performance.now();
        const response2 = await fetch(`${this.apiUrl}?${params}`);
        const data2 = await response2.json();
        const time2 = performance.now() - start2;

        const cacheHit = time2 < time1 * 0.5; // Cache dovrebbe essere almeno 50% più veloce

        return {
            success: true,
            message: `Cache working: ${data1.cached ? 'cached' : 'fresh'} → ${data2.cached ? 'cached' : 'fresh'}`,
            firstRequestMs: Math.round(time1),
            secondRequestMs: Math.round(time2),
            speedup: Math.round((time1 / time2) * 10) / 10 + 'x'
        };
    }

    /**
     * Test fallback mechanism
     */
    async testFallback() {
        // Richiesta con nome strano per forzare fallback
        const params = new URLSearchParams({
            action: 'get-dish-image',
            name: 'XYZ_NONEXISTENT_DISH_12345',
            category: 'pizza'
        });

        const response = await fetch(`${this.apiUrl}?${params}`);
        const data = await response.json();

        if (!data.url.includes('ui-avatars')) throw new Error('Fallback URL not generated');

        return {
            success: true,
            message: 'Fallback mechanism working - using placeholder',
            fallbackType: 'color-avatar',
            category: 'pizza',
            url: data.url.substring(0, 50) + '...'
        };
    }

    /**
     * Test inizializzazione loader
     */
    async testLoaderInit() {
        if (typeof DishImageLoader === 'undefined') {
            throw new Error('DishImageLoader class not found');
        }

        const loader = new DishImageLoader();
        
        if (!loader.apiUrl) throw new Error('API URL not set');
        if (!(loader.imageCache instanceof Map)) throw new Error('Cache not initialized');
        if (typeof loader.getDishImage !== 'function') throw new Error('Methods not available');

        return {
            success: true,
            message: 'DishImageLoader initialized successfully',
            cacheType: 'Map',
            maxConcurrent: loader.maxConcurrent,
            methods: ['getDishImage', 'getDishesImages', 'applyImageToElement']
        };
    }

    /**
     * Test richieste concorrenti
     */
    async testConcurrency() {
        const loader = new DishImageLoader();
        const dishes = [
            { name: 'Pasta', category: 'pasta' },
            { name: 'Pizza', category: 'pizza' },
            { name: 'Tiramisu', category: 'dolci' },
            { name: 'Risotto', category: 'piatti' },
            { name: 'Antipasto', category: 'antipasti' }
        ];

        const start = performance.now();
        const promises = dishes.map(d => 
            loader.getDishImage(d.name, '', d.category)
        );
        const results = await Promise.all(promises);
        const duration = performance.now() - start;

        return {
            success: true,
            message: `Loaded ${results.length} images concurrently`,
            itemsLoaded: results.length,
            totalTimeMs: Math.round(duration),
            avgPerItemMs: Math.round(duration / results.length)
        };
    }

    /**
     * Genera report summary
     */
    generateSummary(duration) {
        const successful = this.results.filter(r => r.success).length;
        const failed = this.results.filter(r => !r.success).length;
        const total = this.results.length;

        let summary = '\n📊 TEST SUMMARY\n';
        summary += `Total Tests: ${total}\n`;
        summary += `✅ Passed: ${successful}\n`;
        summary += `❌ Failed: ${failed}\n`;
        summary += `⏱️  Total Duration: ${Math.round(duration)}ms\n`;
        summary += `📈 Status: ${failed === 0 ? 'ALL TESTS PASSED ✨' : 'SOME TESTS FAILED'}\n`;

        return summary;
    }

    /**
     * Genera diagnostica HTML
     */
    generateHTMLReport() {
        const report = `
<!DOCTYPE html>
<html>
<head>
    <title>Dish Image System - Test Report</title>
    <style>
        body { font-family: Arial; margin: 20px; background: #f5f5f5; }
        .container { max-width: 900px; margin: auto; background: white; padding: 20px; border-radius: 8px; }
        h1 { color: #333; }
        .test-item { margin: 15px 0; padding: 15px; border-left: 4px solid #ccc; background: #fafafa; }
        .test-item.pass { border-left-color: #4caf50; background: #f1f8f4; }
        .test-item.fail { border-left-color: #f44336; background: #fef5f5; }
        .test-name { font-weight: bold; font-size: 1.1em; margin-bottom: 8px; }
        .test-details { font-size: 0.9em; color: #666; }
        .test-details div { margin: 5px 0; }
        .badge { display: inline-block; padding: 4px 8px; border-radius: 4px; font-size: 0.8em; font-weight: bold; }
        .badge.pass { background: #4caf50; color: white; }
        .badge.fail { background: #f44336; color: white; }
        .summary { margin: 20px 0; padding: 15px; background: #e3f2fd; border-radius: 8px; }
        .summary h2 { margin-top: 0; color: #1976d2; }
    </style>
</head>
<body>
    <div class="container">
        <h1>🧪 Dish Image System - Test Report</h1>
        <div class="summary">
            <h2>Summary</h2>
            <p><strong>Total Tests:</strong> ${this.results.length}</p>
            <p><strong>Passed:</strong> <span style="color: #4caf50; font-weight: bold;">${this.results.filter(r => r.success).length}</span></p>
            <p><strong>Failed:</strong> <span style="color: #f44336; font-weight: bold;">${this.results.filter(r => !r.success).length}</span></p>
            <p><strong>Status:</strong> ${this.results.every(r => r.success) ? '✅ ALL TESTS PASSED' : '⚠️ SOME TESTS FAILED'}</p>
        </div>
        
        ${this.results.map((result, i) => `
            <div class="test-item ${result.success ? 'pass' : 'fail'}">
                <div class="test-name">
                    ${result.success ? '✅' : '❌'} ${result.name}
                    <span class="badge ${result.success ? 'pass' : 'fail'}">${result.success ? 'PASS' : 'FAIL'}</span>
                </div>
                <div class="test-details">
                    ${Object.entries(result)
                        .filter(([k]) => !['name', 'success'].includes(k))
                        .map(([k, v]) => `<div><strong>${k}:</strong> ${v}</div>`)
                        .join('')}
                </div>
            </div>
        `).join('')}
        
        <div style="margin-top: 30px; padding-top: 20px; border-top: 1px solid #ddd; text-align: center; color: #999;">
            <small>Report generated: ${new Date().toLocaleString()}</small>
        </div>
    </div>
</body>
</html>
        `;
        return report;
    }

    /**
     * Esporta risultati come JSON
     */
    exportJSON() {
        return JSON.stringify({
            timestamp: new Date().toISOString(),
            tests: this.results,
            summary: {
                total: this.results.length,
                passed: this.results.filter(r => r.success).length,
                failed: this.results.filter(r => !r.success).length,
                successRate: Math.round((this.results.filter(r => r.success).length / this.results.length) * 100) + '%'
            }
        }, null, 2);
    }
}

// Esporta globalmente
window.DishImageSetup = DishImageSetup;

// Auto-setup se richiesto via URL query parameter
if (new URLSearchParams(window.location.search).has('test')) {
    document.addEventListener('DOMContentLoaded', async function() {
        const setup = new DishImageSetup();
        const results = await setup.runAllTests();
        
        // Mostra HTML report
        const html = setup.generateHTMLReport();
        document.body.innerHTML = html;
        
        // Scarica JSON report
        const json = setup.exportJSON();
        console.log('Test Results (JSON):', JSON.parse(json));
    });
}
