/**
 * Restaurant Manager - Gestisce creazione e eliminazione ristoranti
 * Trovapiatto.it - Multi-Restaurant System
 */

class RestaurantManager {
    constructor(apiUrl = './api-crud.php') {
        this.apiUrl = apiUrl;
        this.restaurants = [];
        this.token = localStorage.getItem('auth_token') || '';
    }

    _authUrl(url) {
        if (this.token && !url.includes('token=')) {
            return url + (url.includes('?') ? '&' : '?') + 'token=' + this.token;
        }
        return url;
    }

    /**
     * Carica lista di tutti i ristoranti
     */
    async getRestaurants() {
        try {
            const response = await fetch(`${this.apiUrl}?action=get-restaurants`);
            const result = await response.json();
            
            if (result.success) {
                this.restaurants = result.data;
                return result.data;
            }
            console.error('Errore caricamento ristoranti:', result.error);
            return [];
        } catch (error) {
            console.error('Errore API:', error);
            return [];
        }
    }

    /**
     * Ottieni un ristorante specifico
     */
    async getRestaurant(id) {
        try {
            const response = await fetch(`${this.apiUrl}?action=get-restaurant&id=${id}`);
            const result = await response.json();
            
            if (result.success) {
                return result.data;
            }
            console.error('Errore caricamento ristorante:', result.error);
            return null;
        } catch (error) {
            console.error('Errore API:', error);
            return null;
        }
    }

    /**
     * Crea un nuovo ristorante
     * @param {Object} data - Dati ristorante {name, description, address, phone, email, logo_url, latitude, longitude}
     * @returns {Object} {id, slug} del nuovo ristorante
     */
    async createRestaurant(data) {
        try {
            const response = await fetch(this._authUrl(`${this.apiUrl}?action=create-restaurant`), {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json'
                },
                body: JSON.stringify(data)
            });
            
            const result = await response.json();
            
            if (result.success) {
                console.log('✅ Ristorante creato:', result.id, 'slug:', result.slug);
                console.log('📁 Menu creato:', result.menu_created ? 'SI' : 'NO');
                console.log('🔗 URL menu:', this.getRestaurantPageUrl(result.slug));
                return result;
            }
            
            console.error('Errore creazione ristorante:', result.error);
            return null;
        } catch (error) {
            console.error('Errore API:', error);
            return null;
        }
    }

    /**
     * Elimina un ristorante
     * @param {string} id - ID del ristorante
     */
    async deleteRestaurant(id) {
        try {
            const response = await fetch(this._authUrl(`${this.apiUrl}?action=delete-restaurant`), {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json'
                },
                body: JSON.stringify({id})
            });
            
            const result = await response.json();
            
            if (result.success) {
                console.log('✅ Ristorante eliminato:', id);
                return true;
            }
            
            console.error('Errore eliminazione ristorante:', result.error);
            return false;
        } catch (error) {
            console.error('Errore API:', error);
            return false;
        }
    }

    /**
     * Crea struttura directory per nuovo ristorante
     * Nota: Questa operazione richiede accesso server-side
     * Chiama l'endpoint create-restaurant che gestisce tutto
     */
    async setupRestaurantDirectories(restaurantSlug) {
        console.log('📁 Struttura directory configurata dal server per:', restaurantSlug);
        // Il server PHP crea automaticamente la cartella e i file
        return true;
    }

    /**
     * Esporta configurazione di un ristorante
     */
    async exportRestaurant(restaurantId) {
        try {
            const response = await fetch(`${this.apiUrl}?action=export&restaurant_id=${restaurantId}`);
            const data = await response.json();
            
            const element = document.createElement('a');
            element.setAttribute('href', 'data:text/json;charset=utf-8,' + encodeURIComponent(JSON.stringify(data, null, 2)));
            element.setAttribute('download', `restaurant_${restaurantId}_export.json`);
            element.style.display = 'none';
            document.body.appendChild(element);
            element.click();
            document.body.removeChild(element);
            
            return true;
        } catch (error) {
            console.error('Errore export:', error);
            return false;
        }
    }

    /**
     * Importa configurazione in un ristorante
     */
    async importRestaurant(restaurantId, data) {
        try {
            const response = await fetch(this._authUrl(`${this.apiUrl}?action=import&restaurant_id=${restaurantId}`), {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json'
                },
                body: JSON.stringify(data)
            });
            
            const result = await response.json();
            
            if (result.success) {
                console.log('✅ Configurazione importata:', result.imported);
                return true;
            }
            
            console.error('Errore import:', result.error);
            return false;
        } catch (error) {
            console.error('Errore API:', error);
            return false;
        }
    }

    /**
     * Crea una copia del template index.html per il nuovo ristorante
     * Nota: Questa operazione è gestita dal server PHP
     */
    async createRestaurantPage(restaurantSlug, restaurantConfig) {
        console.log('🌐 Pagina menu creata per:', restaurantSlug);
        // Il server PHP copia index.html in menu/[slug]/index.html
        return `menu/${restaurantSlug}/`;
    }

    /**
     * Ottieni URL completo della pagina menu del ristorante
     */
    getRestaurantPageUrl(slug) {
        const protocol = window.location.protocol;
        const host = window.location.host;
        const basePath = window.location.pathname.split('/admin/')[0];
        return `${protocol}//${host}${basePath}/menu/${slug}/`;
    }

    /**
     * Controlla se un slug è già utilizzato
     */
    async isSlugAvailable(slug) {
        const restaurants = await this.getRestaurants();
        return !restaurants.some(r => r.slug === slug);
    }

    /**
     * Genera slug da nome
     */
    generateSlug(name) {
        return name
            .toLowerCase()
            .trim()
            .replace(/[àáâãäå]/g, 'a')
            .replace(/[èéêë]/g, 'e')
            .replace(/[ìíîï]/g, 'i')
            .replace(/[òóôõö]/g, 'o')
            .replace(/[ùúûü]/g, 'u')
            .replace(/[ç]/g, 'c')
            .replace(/[ñ]/g, 'n')
            .replace(/[æ]/g, 'ae')
            .replace(/[œ]/g, 'oe')
            .replace(/\s+/g, '-')           // Spazi in trattini
            .replace(/[^a-z0-9-]/g, '')    // Rimuove altri caratteri
            .replace(/-+/g, '-')            // Molteplici trattini in uno
            .replace(/^-|-$/g, '');         // Rimuove trattini iniziali/finali
    }

    /**
     * Carica menu di un ristorante
     * @param {string} restaurantId - ID del ristorante
     * @returns {Array} Array di piatti ordinati per categoria
     */
    async getMenu(restaurantId) {
        try {
            const response = await fetch(`${this.apiUrl}?action=get-menu&restaurant_id=${restaurantId}`);
            const result = await response.json();
            
            if (result.success) {
                console.log('✅ Menu caricato:', result.data?.length || 0, 'piatti');
                return result.data || [];
            }
            console.error('Errore caricamento menu:', result.error);
            return [];
        } catch (error) {
            console.error('Errore API:', error);
            return [];
        }
    }

    /**
     * Aggiunge un piatto al menu
     * @param {string} restaurantId - ID del ristorante
     * @param {Object} dishData - Dati piatto {category, name, description, price, image_url, available}
     */
    async addDish(restaurantId, dishData) {
        try {
            const response = await fetch(this._authUrl(`${this.apiUrl}?action=add-dish`), {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json'
                },
                body: JSON.stringify({
                    restaurant_id: restaurantId,
                    ...dishData
                })
            });
            
            const result = await response.json();
            
            if (result.success) {
                console.log('✅ Piatto aggiunto:', dishData.name);
                return result;
            }
            
            console.error('Errore aggiunta piatto:', result.error);
            return null;
        } catch (error) {
            console.error('Errore API:', error);
            return null;
        }
    }

    /**
     * Elimina un piatto dal menu
     * @param {string} dishId - ID del piatto
     */
    async deleteDish(dishId) {
        try {
            const response = await fetch(this._authUrl(`${this.apiUrl}?action=delete-dish`), {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json'
                },
                body: JSON.stringify({id: dishId})
            });
            
            const result = await response.json();
            
            if (result.success) {
                console.log('✅ Piatto eliminato:', dishId);
                return true;
            }
            
            console.error('Errore eliminazione piatto:', result.error);
            return false;
        } catch (error) {
            console.error('Errore API:', error);
            return false;
        }
    }

    /**
     * Carica menu di esempio
     */
    async getExampleMenu() {
        try {
            const response = await fetch(`${this.apiUrl}?action=get-example-menu`);
            const result = await response.json();
            
            if (result.success) {
                return result.dishes || [];
            }
            return [];
        } catch (error) {
            console.error('Errore API:', error);
            return [];
        }
    }

    /**
     * Inserisce il menu di esempio per Gennaro (richiama insert-gennaro-menu.php)
     */
    async insertExampleMenuGennaro() {
        try {
            const response = await fetch(`${this.apiUrl.replace('api.php', 'insert-gennaro-menu.php')}`);
            const result = await response.json();
            
            if (result.success) {
                console.log('✅ Menu Gennaro inserito:', result.dishes_inserted, 'piatti');
                return result;
            }
            
            console.error('Errore inserimento menu:', result.error);
            return null;
        } catch (error) {
            console.error('Errore API:', error);
            return null;
        }
    }

    /**
     * Carica categorie di un ristorante
     * @param {string} restaurantId - ID del ristorante
     * @returns {Array} Array di categorie
     */
    async getCategories(restaurantId) {
        try {
            const response = await fetch(`${this.apiUrl}?action=get-categories&restaurant_id=${restaurantId}`);
            const result = await response.json();
            
            if (result.success) {
                console.log('✅ Categorie caricate:', result.data?.length || 0);
                return result.data || [];
            }
            console.error('Errore caricamento categorie:', result.error);
            return [];
        } catch (error) {
            console.error('Errore API:', error);
            return [];
        }
    }

    /**
     * Aggiunge una nuova categoria
     * @param {string} restaurantId - ID del ristorante
     * @param {Object} categoryData - Dati categoria {name, description, display_order}
     */
    async addCategory(restaurantId, categoryData) {
        try {
            const response = await fetch(this._authUrl(`${this.apiUrl}?action=add-category`), {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json'
                },
                body: JSON.stringify({
                    restaurant_id: restaurantId,
                    ...categoryData
                })
            });
            
            const result = await response.json();
            
            if (result.success) {
                console.log('✅ Categoria aggiunta:', categoryData.name);
                return result;
            }
            
            console.error('Errore aggiunta categoria:', result.error);
            return null;
        } catch (error) {
            console.error('Errore API:', error);
            return null;
        }
    }

    /**
     * Aggiorna una categoria
     * @param {string} categoryId - ID della categoria
     * @param {Object} categoryData - Dati da aggiornare {name, description, display_order}
     */
    async updateCategory(categoryId, categoryData) {
        try {
            const response = await fetch(this._authUrl(`${this.apiUrl}?action=update-category`), {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json'
                },
                body: JSON.stringify({
                    id: categoryId,
                    ...categoryData
                })
            });
            
            const result = await response.json();
            
            if (result.success) {
                console.log('✅ Categoria aggiornata:', categoryId);
                return result;
            }
            
            console.error('Errore aggiornamento categoria:', result.error);
            return null;
        } catch (error) {
            console.error('Errore API:', error);
            return null;
        }
    }

    /**
     * Elimina una categoria
     * @param {string} categoryId - ID della categoria
     */
    async deleteCategory(categoryId) {
        try {
            const response = await fetch(this._authUrl(`${this.apiUrl}?action=delete-category`), {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json'
                },
                body: JSON.stringify({id: categoryId})
            });
            
            const result = await response.json();
            
            if (result.success) {
                console.log('✅ Categoria eliminata:', categoryId);
                return true;
            }
            
            console.error('Errore eliminazione categoria:', result.error);
            return false;
        } catch (error) {
            console.error('Errore API:', error);
            return false;
        }
    }

    /**
     * Raggruppa piatti per categoria
     * @param {Array} dishes - Array di piatti
     * @returns {Object} Piatti raggruppati per categoria
     */
    groupDishesByCategory(dishes) {
        return dishes.reduce((acc, dish) => {
            const category = dish.category || 'Altro';
            if (!acc[category]) {
                acc[category] = [];
            }
            acc[category].push(dish);
            return acc;
        }, {});
    }

    /**
     * Renderizza HTML del menu per visualizzazione
     * @param {Array} dishes - Array di piatti
     * @returns {string} HTML del menu
     */
    renderMenuHtml(dishes) {
        const grouped = this.groupDishesByCategory(dishes);
        let html = '<div class="menu-container">';
        
        for (const [category, items] of Object.entries(grouped)) {
            html += `<div class="menu-category">
                    <h3>${category}</h3>
                    <div class="dishes-list">`;
            
            items.forEach(dish => {
                const available = dish.available ? '' : 'unavailable';
                html += `<div class="dish-card ${available}">
                        <div class="dish-header">
                            <h4>${dish.name}</h4>
                            <span class="price">€${parseFloat(dish.price).toFixed(2)}</span>
                        </div>
                        <p class="description">${dish.description}</p>
                    </div>`;
            });
            
            html += '</div></div>';
        }
        
        html += '</div>';
        return html;
    }
}

// Esporta per uso globale
if (typeof module !== 'undefined' && module.exports) {
    module.exports = RestaurantManager;
}
