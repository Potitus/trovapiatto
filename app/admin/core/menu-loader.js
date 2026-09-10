/**
 * Menu Items Manager
 * Gestisce il caricamento e la visualizzazione dinamica dei piatti del menu
 */

class MenuItemsManager {
    constructor() {
        this.data = null;
        this.currentCategory = null;
        this.isInitialized = false;
        this.db = window.menuDB || null;
        this.initPromise = this.init();
    }

    async init() {
        try {
            let items = [];

            // Prova prima dal database locale se disponibile
            if (this.db) {
                if (!this.db.initialized) {
                    await this.db.init();
                }
                items = await this.db.getItems();
            }

            // Se non ci sono dati dal DB, carica dalla API
            if (!items || items.length === 0) {
                try {
                    const response = await fetch('./api-crud.php?action=get-all-dishes');
                    const result = await response.json();
                    if (result.success && result.data) {
                        items = result.data;
                        console.log('📊 Menu caricato da API:', items.length);
                    }
                } catch (e) {
                    console.warn('⚠️ API non disponibile, provo file backup');
                }
            }

            // Se non ci sono dati dalla API, carica da menu-items.json
            if (!items || items.length === 0) {
                try {
                    const response = await fetch('./admin/menu-items.json');
                    const jsonData = await response.json();
                    items = jsonData;
                    console.log('📊 Menu caricato da file backup (menu-items.json):', items.length);
                } catch (e) {
                    console.warn('⚠️ Nessun dato disponibile');
                    items = [];
                }
            }
            
            // Organizza per categoria
            const categoryDefs = [
                { id: 'antipasti', name: 'Antipasti' },
                { id: 'primi', name: 'Primi' },
                { id: 'secondi', name: 'Secondi' },
                { id: 'pizze', name: 'Pizze' },
                { id: 'dessert', name: 'Dessert' },
                { id: 'vini-rossi', name: 'Vini Rossi' },
                { id: 'vini-bianchi', name: 'Vini Bianchi' },
                { id: 'menu-fisso', name: 'Menu Fisso' }
            ];

            // Raggruppa items per categoria
            const categories = categoryDefs.map(cat => ({
                ...cat,
                items: items.filter(item => item.category_id === cat.id || item.category === cat.id)
            }));

            // Aggiungi categorie trovate nei dati che non sono nella lista predefinita
            const knownIds = categoryDefs.map(c => c.id);
            const extraCats = [...new Set(items.map(i => i.category_id || i.category).filter(id => id && !knownIds.includes(id)))];
            extraCats.forEach(catId => {
                categories.push({
                    id: catId,
                    name: catId,
                    items: items.filter(item => (item.category_id || item.category) === catId)
                });
            });

            this.data = {
                categories: categories,
                items: items,
                allergens: ['Glutine', 'Lattosio', 'Uova', 'Pesce', 'Crostacei', 'Molluschi', 'Frutta a guscio', 'Arachidi', 'Sesamo', 'Soia', 'Sedano', 'Solfiti'],
                tags: {
                    'vegano': { icon: 'vegano.png', label: 'Vegano' },
                    'vegetariano': { icon: 'vegetariano.png', label: 'Vegetariano' },
                    'senza-glutine': { icon: 'noglutine.png', label: 'Senza Glutine' },
                    'piccante': { icon: 'piccante.png', label: 'Piccante' },
                    'bio': { icon: 'biologico.png', label: 'Biologico' }
                }
            };

            this.isInitialized = true;
            console.log('📊 Menu items caricati da database:', items.length);
            return this.data;
        } catch (error) {
            console.error('Errore nel caricamento menu items:', error);
            this.isInitialized = false;
        }
    }

    /**
     * Attende che il manager sia completamente inizializzato
     */
    async ready() {
        if (this.isInitialized) return true;
        await this.initPromise;
        return this.isInitialized;
    }

    /**
     * Aggiunge un nuovo piatto a una categoria
     */
    addItem(categoryId, item) {
        if (!this.data) return false;
        
        const category = this.data.categories.find(c => c.id === categoryId);
        if (!category) return false;

        // Genera nuovo ID se non presente
        if (!item.id) {
            item.id = Date.now();
        }

        category.items.push(item);
        this.save();
        return true;
    }

    /**
     * Aggiorna un piatto esistente
     */
    updateItem(itemId, updatedData) {
        if (!this.data) return false;

        for (let category of this.data.categories) {
            const itemIndex = category.items.findIndex(i => i.id === itemId);
            if (itemIndex !== -1) {
                category.items[itemIndex] = { ...category.items[itemIndex], ...updatedData };
                this.save();
                return true;
            }
        }
        return false;
    }

    /**
     * Elimina un piatto
     */
    deleteItem(itemId) {
        if (!this.data) return false;

        for (let category of this.data.categories) {
            const itemIndex = category.items.findIndex(i => i.id === itemId);
            if (itemIndex !== -1) {
                category.items.splice(itemIndex, 1);
                this.save();
                return true;
            }
        }
        return false;
    }

    /**
     * Ottiene tutti i piatti di una categoria
     */
    getItemsByCategory(categoryId) {
        if (!this.data || !this.data.items) return [];
        return this.data.items.filter(item => item.category_id === categoryId);
    }

    /**
     * Ottiene un singolo piatto
     */
    getItem(itemId) {
        if (!this.data || !this.data.items) return null;
        return this.data.items.find(i => i.id === itemId);
    }

    /**
     * Salva i dati nel database
     */
    async save() {
        if (this.data && this.data.items) {
            // Sincronizza ogni piatto con il database
            for (let item of this.data.items) {
                if (item.id && !item.saved) {
                    await this.db.updateItem(item.id, item);
                    item.saved = true;
                }
            }
            console.log('✅ Menu salvato su database');
        }
    }

    /**
     * Renderizza i piatti di una categoria
     */
    renderCategory(categoryId, containerId) {
        const items = this.getItemsByCategory(categoryId);
        const container = document.getElementById(containerId);

        if (!container) return;

        let html = '';
        items.forEach(item => {
            html += this.renderItem(item);
        });

        container.innerHTML = html;
    }

    /**
     * Renderizza un singolo piatto
     */
    renderItem(item) {
        const tagsHtml = item.tags.map(tag => {
            const tagInfo = this.data.tags[tag];
            return `<a href="javascript:void(0);" data-menu="menu-caratteristiche">
                <img src="/assets/images/icons/${tagInfo.icon}" 
                     alt="${tagInfo.label}" class="preload-img mr-3" width="25">
            </a>`;
        }).join('');

        const allergenHtml = item.allergens.length > 0 
            ? `<a href="javascript:void(0);" class="font-12 color-theme" data-menu="menu-allergeni">
                <i class="fa fa-exclamation-circle mr-2"></i><b>Allergeni:</b> ${item.allergens.join(', ')}
            </a>`
            : '';

        return `
            <!-- Voce Card -->
            <div class="card card-style" id="voce-${item.id}">
                ${item.image ? `<img data-src="${item.image}" class="img-fluid bottom-20" src="${item.image}">` : ''}
                
                <div class="content mb-0">
                    <div class="d-flex mb-0">
                        <h2 class="mb-0 font-18">${item.title}</h2>
                        <div class="flex-grow-1 pr-1">
                            <div class="flex-shrink-1 pl-1 text-right prezzo">
                                <h2 class="font-20 font-weight-bold mt-0 mb-0 notranslate">€ ${item.price.toFixed(2)}</h2>
                            </div>
                        </div>
                    </div>
                    
                    <div class="clearfix"></div>
                    
                    ${item.description ? `<p class="descrizione mb-0">${item.description}</p>` : ''}
                    
                    <div class="divider mt-3 mb-3"></div>
                    
                    ${tagsHtml ? `<div class="d-flex justify-content-lg-start">${tagsHtml}</div>` : ''}
                    
                    ${tagsHtml ? '<div class="divider mt-3 mb-3"></div>' : ''}
                    ${tagsHtml ? '<div class="clearfix"></div>' : ''}
                    
                    ${allergenHtml ? `<div class="mb-0">${allergenHtml}<div class="divider mt-3 mb-3"></div></div>` : ''}
                    
                    <a href="javascript:void(0);"
                        class="lmcart-add btn btn-m btn-full mb-3 rounded-xl text-uppercase font-900 shadow-s bg-green1-dark btn-icon text-left"
                        data-id="${item.id}"
                        data-title="${item.title}"
                        data-cat="${item.category}"
                        data-price="${item.price.toFixed(2)}"
                        data-custom="0"
                        data-ing=""
                        data-note=""
                        data-vibrate="50">
                        <i class="far fa-list-alt font-15 text-center"></i>
                        Ordina
                    </a>
                    
                    <div class="clearfix"></div>
                    <div class="mb-3"></div>
                </div>
            </div>
        `;
    }

    /**
     * Esporta menu in JSON
     */
    export() {
        return JSON.stringify(this.data, null, 2);
    }

    /**
     * Importa menu da JSON
     */
    import(jsonString) {
        try {
            this.data = JSON.parse(jsonString);
            this.save();
            return true;
        } catch (error) {
            console.error('Errore nell\'import:', error);
            return false;
        }
    }
}

// Inizializza quando il DOM è pronto
let menuItemsManager;
if (document.readyState === 'loading') {
    document.addEventListener('DOMContentLoaded', () => {
        menuItemsManager = new MenuItemsManager();
    });
} else {
    menuItemsManager = new MenuItemsManager();
}
