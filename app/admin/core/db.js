/**
 * Database API Client - Trovapiatto Menu
 * Gestisce le operazioni CRUD con il backend PHP/SQLite
 */

class MenuDatabase {
    constructor(apiUrl = './admin/api-crud.php') {
        this.apiUrl = apiUrl;
        this.useLocal = true; // Fallback a localStorage se API non disponibile
        this.initialized = false;
    }

    /**
     * Verifica disponibilità API
     */
    async checkApi() {
        try {
            const response = await fetch(this.apiUrl + '?action=get-config');
            return response.ok;
        } catch(e) {
            console.warn('⚠️ API non disponibile, usando localStorage');
            return false;
        }
    }

    /**
     * Inizializza database
     */
    async init() {
        this.useLocal = !(await this.checkApi());
        this.initialized = true;
        console.log(`📊 Database: ${this.useLocal ? 'localStorage' : 'SQLite (Remote)'}`);
    }

    /**
     * Ottieni tutti i piatti
     */
    async getItems(categoryId = null) {
        if (this.useLocal) {
            return this._localGetItems(categoryId);
        }

        try {
            let url = this.apiUrl + '?action=get-items';
            if (categoryId) url += '&category=' + categoryId;
            
            const response = await fetch(url);
            const data = await response.json();
            return data.success ? data.data : [];
        } catch(e) {
            console.error('Errore fetch piatti:', e);
            return this._localGetItems(categoryId);
        }
    }

    /**
     * Aggiungi piatto
     */
    async addItem(item) {
        if (this.useLocal) {
            return this._localAddItem(item);
        }

        try {
            const response = await fetch(this.apiUrl + '?action=add-item', {
                method: 'POST',
                headers: { 'Content-Type': 'application/json' },
                body: JSON.stringify(item)
            });
            const data = await response.json();
            return data.success ? data.id : null;
        } catch(e) {
            console.error('Errore aggiunta piatto:', e);
            return this._localAddItem(item).id;
        }
    }

    /**
     * Aggiorna piatto
     */
    async updateItem(id, item) {
        if (this.useLocal) {
            return this._localUpdateItem(id, item);
        }

        try {
            item.id = id;
            const response = await fetch(this.apiUrl + '?action=update-item', {
                method: 'PUT',
                headers: { 'Content-Type': 'application/json' },
                body: JSON.stringify(item)
            });
            const data = await response.json();
            return data.success;
        } catch(e) {
            console.error('Errore aggiornamento piatto:', e);
            return this._localUpdateItem(id, item);
        }
    }

    /**
     * Elimina piatto
     */
    async deleteItem(id) {
        if (this.useLocal) {
            return this._localDeleteItem(id);
        }

        try {
            const response = await fetch(this.apiUrl + '?action=delete-item&id=' + id, {
                method: 'DELETE'
            });
            const data = await response.json();
            return data.success;
        } catch(e) {
            console.error('Errore eliminazione piatto:', e);
            return this._localDeleteItem(id);
        }
    }

    /**
     * Esporta dati
     */
    async exportData() {
        if (this.useLocal) {
            return this._localExportData();
        }

        try {
            window.location.href = this.apiUrl + '?action=export';
            return true;
        } catch(e) {
            console.error('Errore export:', e);
            return this._localExportData();
        }
    }

    /**
     * Importa dati
     */
    async importData(data) {
        if (this.useLocal) {
            return this._localImportData(data);
        }

        try {
            const response = await fetch(this.apiUrl + '?action=import', {
                method: 'POST',
                headers: { 'Content-Type': 'application/json' },
                body: JSON.stringify(data)
            });
            const result = await response.json();
            return result.success;
        } catch(e) {
            console.error('Errore import:', e);
            return this._localImportData(data);
        }
    }

    // ============ FALLBACK LOCALSTORAGE ============

    _localGetItems(categoryId) {
        const items = JSON.parse(localStorage.getItem('menu_items') || '[]');
        return categoryId ? items.filter(i => i.category_id === categoryId) : items;
    }

    _localAddItem(item) {
        const items = this._localGetItems();
        item.id = item.id || 'item_' + Date.now();
        items.push(item);
        localStorage.setItem('menu_items', JSON.stringify(items));
        return item;
    }

    _localUpdateItem(id, item) {
        const items = this._localGetItems();
        const index = items.findIndex(i => i.id === id);
        if (index !== -1) {
            items[index] = { ...items[index], ...item, id };
            localStorage.setItem('menu_items', JSON.stringify(items));
            return true;
        }
        return false;
    }

    _localDeleteItem(id) {
        const items = this._localGetItems().filter(i => i.id !== id);
        localStorage.setItem('menu_items', JSON.stringify(items));
        return true;
    }

    _localExportData() {
        const items = this._localGetItems();
        const config = JSON.parse(localStorage.getItem('config') || '{}');
        const data = {
            exported_at: new Date().toISOString(),
            version: '1.0',
            items,
            config
        };
        const blob = new Blob([JSON.stringify(data, null, 2)], { type: 'application/json' });
        const url = URL.createObjectURL(blob);
        const a = document.createElement('a');
        a.href = url;
        a.download = 'menu_export_' + new Date().toISOString().split('T')[0] + '.json';
        a.click();
        return true;
    }

    _localImportData(data) {
        if (!data.items || !Array.isArray(data.items)) {
            console.error('Dati import invalidi');
            return false;
        }
        localStorage.setItem('menu_items', JSON.stringify(data.items));
        if (data.config) {
            localStorage.setItem('config', JSON.stringify(data.config));
        }
        return true;
    }
}

// Singleton globale
window.menuDB = new MenuDatabase();
