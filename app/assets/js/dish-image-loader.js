/**
 * Dish Image Loader
 * Sistema frontend per caricare e gestire immagini piatti
 */

class DishImageLoader {
    constructor(apiUrl = '/app/admin/dish-image-handler.php') {
        this.apiUrl = apiUrl;
        this.imageCache = new Map();
        this.loadingQueue = [];
        this.maxConcurrent = 3;
        this.activeRequests = 0;
    }

    /**
     * Ottieni immagine per un piatto singolo
     */
    async getDishImage(dishName, ingredients = '', category = '') {
        const cacheKey = `${dishName}_${category}`;
        
        // Controlla cache locale
        if (this.imageCache.has(cacheKey)) {
            return this.imageCache.get(cacheKey);
        }

        // Aggiungi alla coda
        return new Promise((resolve) => {
            this.loadingQueue.push({
                dishName,
                ingredients,
                category,
                resolve
            });
            this.processQueue();
        });
    }

    /**
     * Processa coda di richieste
     */
    async processQueue() {
        while (this.loadingQueue.length > 0 && this.activeRequests < this.maxConcurrent) {
            this.activeRequests++;
            const { dishName, ingredients, category, resolve } = this.loadingQueue.shift();

            try {
                const image = await this.fetchImage(dishName, ingredients, category);
                const cacheKey = `${dishName}_${category}`;
                this.imageCache.set(cacheKey, image);
                resolve(image);
            } catch (e) {
                console.error('Error loading image for ' + dishName, e);
                resolve(this.getFallbackImage(dishName, category));
            } finally {
                this.activeRequests--;
                this.processQueue();
            }
        }
    }

    /**
     * Fetch immagine da API
     */
    async fetchImage(dishName, ingredients, category) {
        const params = new URLSearchParams({
            action: 'get-dish-image',
            name: dishName,
            ingredients: ingredients,
            category: category
        });

        const response = await fetch(`${this.apiUrl}?${params}`);
        
        if (!response.ok) {
            throw new Error(`HTTP ${response.status}`);
        }

        return response.json();
    }

    /**
     * Carica immagini per batch di piatti (POST)
     */
    async getDishesImages(dishes) {
        try {
            const response = await fetch(this.apiUrl + '?action=get-dishes-images', {
                method: 'POST',
                headers: { 'Content-Type': 'application/json' },
                body: JSON.stringify({ dishes })
            });

            if (!response.ok) {
                throw new Error(`HTTP ${response.status}`);
            }

            const result = await response.json();
            
            // Salva in cache
            if (result.data) {
                result.data.forEach(item => {
                    const key = `${item.name}_${item.image.category || ''}`;
                    this.imageCache.set(key, item.image);
                });
            }

            return result;
        } catch (e) {
            console.error('Error loading batch images:', e);
            return {
                success: false,
                data: dishes.map(d => ({
                    id: d.id,
                    name: d.name,
                    image: this.getFallbackImage(d.name, d.category)
                }))
            };
        }
    }

    /**
     * Immagine fallback
     */
    getFallbackImage(dishName, category = '') {
        const colors = {
            'pizza': 'FF6B6B',
            'pasta': 'FFD93D',
            'piatti': '6BCB77',
            'dolci': 'E8B4B8',
            'bevande': '4D96FF',
            'antipasti': 'A8D8EA',
            'secondi': 'F0A6CA',
            'insalate': '95E1D3'
        };

        const color = colors[category?.toLowerCase()] || 'CCCCCC';
        const initials = dishName.substring(0, 2).toUpperCase();

        return {
            url: `https://ui-avatars.com/api/?name=${encodeURIComponent(initials)}&background=${color}&color=fff&size=300&bold=true`,
            source: 'placeholder',
            success: false,
            fallback: true
        };
    }

    /**
     * Applica immagine a elemento HTML
     */
    async applyImageToElement(element, dishName, ingredients, category) {
        element.style.opacity = '0.5';
        element.innerHTML = '<span class="spinner"></span>';

        const image = await this.getDishImage(dishName, ingredients, category);
        
        element.style.backgroundImage = `url('${image.url}')`;
        element.style.backgroundSize = 'cover';
        element.style.backgroundPosition = 'center';
        element.style.opacity = '1';
        element.innerHTML = '';

        // Aggiungi badge sorgente
        if (image.source !== 'placeholder') {
            const badge = document.createElement('div');
            badge.className = 'image-source-badge';
            badge.textContent = image.source;
            badge.style.cssText = `
                position: absolute;
                bottom: 5px;
                right: 5px;
                background: rgba(0,0,0,0.6);
                color: white;
                padding: 3px 8px;
                border-radius: 4px;
                font-size: 0.7em;
                font-weight: bold;
            `;
            element.appendChild(badge);
        }

        return image;
    }

    /**
     * Aggiorna tutti gli elementi piatto nella pagina
     */
    async updateAllDishImages() {
        const dishElements = document.querySelectorAll('[data-dish-image]');
        const dishes = [];

        dishElements.forEach((el, index) => {
            dishes.push({
                id: index,
                name: el.dataset.dishName || el.textContent,
                ingredients: el.dataset.ingredients || '',
                category: el.dataset.dishCategory || ''
            });
        });

        if (dishes.length === 0) return;

        // Carica immagini batch
        const result = await this.getDishesImages(dishes);

        // Applica alle pagine
        dishElements.forEach((el, index) => {
            if (result.data && result.data[index]) {
                const image = result.data[index].image;
                el.style.backgroundImage = `url('${image.url}')`;
                el.style.backgroundSize = 'cover';
                el.style.backgroundPosition = 'center';
            }
        });
    }

    /**
     * Cache storage (localStorage)
     */
    saveToLocalStorage(key, value) {
        try {
            localStorage.setItem(`dish_image_${key}`, JSON.stringify(value));
        } catch (e) {
            console.warn('LocalStorage quota exceeded');
        }
    }

    /**
     * Recupera da localStorage
     */
    getFromLocalStorage(key) {
        try {
            const item = localStorage.getItem(`dish_image_${key}`);
            return item ? JSON.parse(item) : null;
        } catch (e) {
            return null;
        }
    }

    /**
     * Pulisci cache
     */
    clearCache() {
        this.imageCache.clear();
    }

    /**
     * Statistiche
     */
    getStats() {
        return {
            cachedImages: this.imageCache.size,
            queuedRequests: this.loadingQueue.length,
            activeRequests: this.activeRequests
        };
    }
}

// Esporta globalmente
window.DishImageLoader = DishImageLoader;

// Auto-init se presente elemento data-dish-image
document.addEventListener('DOMContentLoaded', function() {
    if (document.querySelector('[data-dish-image]')) {
        window.dishImageLoader = new DishImageLoader();
    }
});
