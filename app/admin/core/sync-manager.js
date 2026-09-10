/**
 * Sync Manager - Sincronizzazione Real-time Database ↔ UI
 * Trovapiatto.it - Menu Digitale
 * 
 * Gestisce:
 * - Sincronizzazione dati in entrata (database → UI)
 * - Sincronizzazione dati in uscita (UI → database)
 * - Aggiornamenti real-time di visualizzazione
 * - Conflict resolution e fallback
 */

class SyncManager {
    constructor(apiUrl = './api-crud.php') {
        this.apiUrl = apiUrl;
        this.syncInterval = 5000; // 5 secondi
        this.lastSync = {};
        this.syncQueue = [];
        this.isSyncing = false;
        this.isSyncActive = false; // Disattivato per default
        this.listeners = []; // UI listeners per aggiornamenti
        this.offlineMode = false;
        this.syncStatus = 'idle'; // idle, syncing, offline, error
        
        // Cache locale con timestamp
        this.cache = {
            restaurants: { data: [], timestamp: 0 },
            items: { data: [], timestamp: 0 },
            config: { data: {}, timestamp: 0 }
        };
    }

    /**
     * Inizializza il manager di sincronizzazione
     */
    async init() {
        console.log('🔄 Inizializzazione Sync Manager...');
        
        // Verifica connessione
        await this.checkConnection();
        
        // NON avvia sincronizzazione automatica (disattivata per default)
        // this.startAutoSync();
        
        // Ascolta offline/online events
        window.addEventListener('offline', () => this.setOfflineMode(true));
        window.addEventListener('online', () => this.setOfflineMode(false));
        
        console.log('✅ Sync Manager pronto (sync disattivata)');
    }

    /**
     * Verifica connessione al server
     */
    async checkConnection() {
        try {
            const response = await fetch(this.apiUrl + '?action=get-restaurants', {
                method: 'GET',
                signal: AbortSignal.timeout(5000) // Timeout 5 secondi
            });
            this.offlineMode = !response.ok;
            return response.ok;
        } catch (e) {
            console.warn('⚠️ Connessione offline');
            this.offlineMode = true;
            return false;
        }
    }

    /**
     * Imposta modalità offline
     */
    setOfflineMode(offline) {
        this.offlineMode = offline;
        this.syncStatus = offline ? 'offline' : 'idle';
        this.notifyListeners({
            type: 'connection-status',
            status: this.syncStatus,
            message: offline ? 'Offline - usando cache locale' : 'Online - sincronizzazione attiva'
        });
        console.log(`📡 Modalità: ${offline ? 'OFFLINE' : 'ONLINE'}`);
    }

    /**
     * Registra un listener per aggiornamenti UI
     */
    addListener(callback) {
        this.listeners.push(callback);
    }

    /**
     * Rimuovi un listener
     */
    removeListener(callback) {
        this.listeners = this.listeners.filter(l => l !== callback);
    }

    /**
     * Notifica tutti i listener
     */
    notifyListeners(event) {
        this.listeners.forEach(listener => {
            try {
                listener(event);
            } catch (e) {
                console.error('Errore nel listener:', e);
            }
        });
    }

    /**
     * Avvia sincronizzazione automatica periodica
     */
    startAutoSync() {
        if (this.autoSyncTimer) {
            clearInterval(this.autoSyncTimer);
        }
        
        this.autoSyncTimer = setInterval(() => {
            if (this.isSyncActive && !this.offlineMode && !this.isSyncing) {
                this.fullSync();
            }
        }, this.syncInterval);
        
        console.log('🔄 Timer sincronizzazione avviato');
    }

    /**
     * Ferma sincronizzazione automatica
     */
    stopAutoSync() {
        if (this.autoSyncTimer) {
            clearInterval(this.autoSyncTimer);
        }
    }

    /**
     * SINCRONIZZAZIONE COMPLETA: In entrata (DB → Cache → UI)
     */
    async fullSync() {
        if (this.isSyncing) return;
        this.isSyncing = true;
        this.syncStatus = 'syncing';

        try {
            // Sincronizza entrata (DB → UI)
            await this.syncInbound();
            
            // Sincronizza uscita (Queue → DB)
            await this.syncOutbound();
            
            this.syncStatus = 'idle';
            this.notifyListeners({ type: 'sync-complete', success: true });
            console.log('✅ Sincronizzazione completata');
            
        } catch (e) {
            console.error('❌ Errore sincronizzazione:', e);
            this.syncStatus = 'error';
            this.notifyListeners({ type: 'sync-error', error: e.message });
        } finally {
            this.isSyncing = false;
        }
    }

    /**
     * SINCRONIZZAZIONE ENTRATA: Database → Cache → UI
     * Carica i dati dal server e aggiorna cache
     */
    async syncInbound() {
        if (this.offlineMode) {
            console.log('📦 Modo offline: saltando sync entrata');
            return;
        }

        try {
            // Carica ristoranti
            const restaurantsData = await this.fetchFromServer('get-restaurants');
            if (restaurantsData) {
                this.cache.restaurants = {
                    data: restaurantsData,
                    timestamp: Date.now()
                };
                this.notifyListeners({
                    type: 'data-updated',
                    dataType: 'restaurants',
                    data: restaurantsData
                });
                console.log(`📥 Caricati ${restaurantsData.length} ristoranti`);
            }

            // Carica items (piatti)
            const itemsData = await this.fetchFromServer('get-items');
            if (itemsData) {
                this.cache.items = {
                    data: itemsData,
                    timestamp: Date.now()
                };
                this.notifyListeners({
                    type: 'data-updated',
                    dataType: 'items',
                    data: itemsData
                });
                console.log(`📥 Caricati ${itemsData.length} piatti`);
            }

        } catch (e) {
            console.error('Errore sync entrata:', e);
            throw e;
        }
    }

    /**
     * SINCRONIZZAZIONE USCITA: Queue → Database
     * Invia le modifiche in coda al server
     */
    async syncOutbound() {
        if (this.syncQueue.length === 0) {
            return;
        }

        console.log(`📤 Sincronizzando ${this.syncQueue.length} operazioni...`);
        
        const queue = [...this.syncQueue];
        this.syncQueue = [];

        for (const operation of queue) {
            try {
                const success = await this.executeOperation(operation);
                
                if (success) {
                    this.notifyListeners({
                        type: 'operation-completed',
                        operation: operation.type,
                        id: operation.id
                    });
                    console.log(`✅ Operazione completata: ${operation.type}`);
                } else {
                    // Re-aggiungi in coda se fallita
                    this.syncQueue.push(operation);
                    console.warn(`⚠️ Operazione fallita, riprovando: ${operation.type}`);
                }
            } catch (e) {
                // Re-aggiungi in coda
                this.syncQueue.push(operation);
                console.error(`❌ Errore operazione: ${operation.type}`, e);
            }
        }
    }

    /**
     * Esegui un'operazione sulla coda
     */
    async executeOperation(operation) {
        if (this.offlineMode) {
            // Offline: salva localmente
            return this.saveOperationLocally(operation);
        }

        switch(operation.type) {
            case 'add-item':
                return await this.serverAddItem(operation.data);
            case 'update-item':
                return await this.serverUpdateItem(operation.id, operation.data);
            case 'delete-item':
                return await this.serverDeleteItem(operation.id);
            case 'create-restaurant':
                return await this.serverCreateRestaurant(operation.data);
            case 'update-restaurant':
                return await this.serverUpdateRestaurant(operation.id, operation.data);
            case 'delete-restaurant':
                return await this.serverDeleteRestaurant(operation.id);
            default:
                console.warn('Operazione sconosciuta:', operation.type);
                return false;
        }
    }

    /**
     * Accoda un'operazione di modifica
     */
    queueOperation(type, data, id = null) {
        const operation = {
            type,
            data,
            id,
            timestamp: Date.now(),
            retries: 0
        };

        this.syncQueue.push(operation);
        this.notifyListeners({
            type: 'operation-queued',
            operation: type,
            queueSize: this.syncQueue.length
        });

        console.log(`📋 Operazione accodata: ${type} (coda: ${this.syncQueue.length})`);

        // Se online, sincronizza immediatamente
        if (!this.offlineMode) {
            setTimeout(() => this.syncOutbound(), 500);
        }

        return operation;
    }

    /**
     * Fetch generico dal server
     */
    async fetchFromServer(action, params = {}) {
        try {
            let url = `${this.apiUrl}?action=${action}`;
            Object.keys(params).forEach(key => {
                url += `&${key}=${params[key]}`;
            });

            const response = await fetch(url, {
                signal: AbortSignal.timeout(10000)
            });

            if (!response.ok) throw new Error(`HTTP ${response.status}`);
            
            const data = await response.json();
            return data.success ? data.data : null;
        } catch (e) {
            console.error(`Errore fetch ${action}:`, e);
            return null;
        }
    }

    /**
     * Server: Aggiungi piatto
     */
    async serverAddItem(item) {
        try {
            const response = await fetch(`${this.apiUrl}?action=add-item`, {
                method: 'POST',
                headers: { 'Content-Type': 'application/json' },
                body: JSON.stringify(item),
                signal: AbortSignal.timeout(10000)
            });

            const data = await response.json();
            return data.success;
        } catch (e) {
            console.error('Errore aggiunta piatto:', e);
            return false;
        }
    }

    /**
     * Server: Aggiorna piatto
     */
    async serverUpdateItem(id, item) {
        try {
            item.id = id;
            const response = await fetch(`${this.apiUrl}?action=update-item`, {
                method: 'PUT',
                headers: { 'Content-Type': 'application/json' },
                body: JSON.stringify(item),
                signal: AbortSignal.timeout(10000)
            });

            const data = await response.json();
            return data.success;
        } catch (e) {
            console.error('Errore aggiornamento piatto:', e);
            return false;
        }
    }

    /**
     * Server: Elimina piatto
     */
    async serverDeleteItem(id) {
        try {
            const response = await fetch(`${this.apiUrl}?action=delete-item`, {
                method: 'DELETE',
                headers: { 'Content-Type': 'application/json' },
                body: JSON.stringify({ id }),
                signal: AbortSignal.timeout(10000)
            });

            const data = await response.json();
            return data.success;
        } catch (e) {
            console.error('Errore eliminazione piatto:', e);
            return false;
        }
    }

    /**
     * Server: Crea ristorante
     */
    async serverCreateRestaurant(data) {
        try {
            const response = await fetch(`${this.apiUrl}?action=create-restaurant`, {
                method: 'POST',
                headers: { 'Content-Type': 'application/json' },
                body: JSON.stringify(data),
                signal: AbortSignal.timeout(10000)
            });

            const result = await response.json();
            return result.success;
        } catch (e) {
            console.error('Errore creazione ristorante:', e);
            return false;
        }
    }

    /**
     * Server: Aggiorna ristorante
     */
    async serverUpdateRestaurant(id, data) {
        try {
            data.id = id;
            const response = await fetch(`${this.apiUrl}?action=update-restaurant`, {
                method: 'PUT',
                headers: { 'Content-Type': 'application/json' },
                body: JSON.stringify(data),
                signal: AbortSignal.timeout(10000)
            });

            const result = await response.json();
            return result.success;
        } catch (e) {
            console.error('Errore aggiornamento ristorante:', e);
            return false;
        }
    }

    /**
     * Server: Elimina ristorante
     */
    async serverDeleteRestaurant(id) {
        try {
            const response = await fetch(`${this.apiUrl}?action=delete-restaurant`, {
                method: 'DELETE',
                headers: { 'Content-Type': 'application/json' },
                body: JSON.stringify({ id }),
                signal: AbortSignal.timeout(10000)
            });

            const result = await response.json();
            return result.success;
        } catch (e) {
            console.error('Errore eliminazione ristorante:', e);
            return false;
        }
    }

    /**
     * Salva operazione localmente (offline)
     */
    saveOperationLocally(operation) {
        try {
            let queue = JSON.parse(localStorage.getItem('syncQueue') || '[]');
            queue.push(operation);
            localStorage.setItem('syncQueue', JSON.stringify(queue));
            console.log(`💾 Operazione salvata localmente: ${operation.type}`);
            return true;
        } catch (e) {
            console.error('Errore salvataggio locale:', e);
            return false;
        }
    }

    /**
     * Ottieni dati dalla cache
     */
    getCachedData(dataType) {
        return this.cache[dataType]?.data || [];
    }

    /**
     * Invalida cache
     */
    invalidateCache(dataType = null) {
        if (dataType) {
            if (this.cache[dataType]) {
                this.cache[dataType].timestamp = 0;
            }
        } else {
            Object.keys(this.cache).forEach(key => {
                this.cache[key].timestamp = 0;
            });
        }
        console.log('🔄 Cache invalidata');
    }

    /**
     * Forza sincronizzazione completa
     */
    async forceSyncNow() {
        console.log('🔄 Forzando sincronizzazione completa...');
        this.invalidateCache();
        await this.fullSync();
    }

    /**
     * Ottieni stato coda
     */
    getQueueStatus() {
        return {
            queueSize: this.syncQueue.length,
            syncStatus: this.syncStatus,
            offlineMode: this.offlineMode,
            isSyncing: this.isSyncing,
            queue: this.syncQueue
        };
    }

    /**
     * Chiudi il manager
     */
    destroy() {
        this.stopAutoSync();
        this.listeners = [];
    }
}

// Esporta per uso globale
if (typeof module !== 'undefined' && module.exports) {
    module.exports = SyncManager;
}
