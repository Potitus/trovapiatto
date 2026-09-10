/**
 * Map Manager
 * Gestisce la visualizzazione della mappa con l'indirizzo del ristorante
 * Utilizza Leaflet + OpenStreetMap (gratis, no API key)
 */

class MapManager {
    constructor() {
        this.map = null;
        this.marker = null;
        this.latitude = null;
        this.longitude = null;
        this.restaurantName = 'Ristorante';
        this.restaurantAddress = null;
        this.geocodingCache = {}; // Cache per geocoding
        
        this.initPromise = this.init();
    }

    /**
     * Inizializza la mappa
     */
    async init() {
        try {
            const mapContainer = document.getElementById('restaurant-map-container');
            if (!mapContainer) {
                console.warn('Container #restaurant-map-container non trovato');
                return;
            }

            // Carica configurazione (indirizzo dal config.json)
            await this.loadConfiguration();

            // Se abbiamo indirizzo o coordinate, mostra la mappa
            if (this.restaurantAddress || (this.latitude && this.longitude)) {
                this.initializeMap(mapContainer);
            } else {
                console.warn('Nessun indirizzo o coordinate configurate per la mappa');
                mapContainer.style.display = 'none';
            }

            console.log('✓ MapManager inizializzato');
        } catch (error) {
            console.error('Errore inizializzazione MapManager:', error);
        }
    }

    /**
     * Carica la configurazione dal config.json
     */
    async loadConfiguration() {
        try {
            const response = await fetch('./admin/config.json');
            const config = await response.json();

            // Legge dai dati della sezione restaurant
            if (config.restaurant) {
                this.restaurantName = config.restaurant.restaurantName || 'Ristorante';
                this.restaurantAddress = config.restaurant.restaurantAddress;
                
                // Se sono presenti coordinate dirette
                if (config.restaurant.latitude && config.restaurant.longitude) {
                    this.latitude = parseFloat(config.restaurant.latitude);
                    this.longitude = parseFloat(config.restaurant.longitude);
                } else if (this.restaurantAddress) {
                    // Geocodifica l'indirizzo per ottenere le coordinate
                    await this.geocodeAddress(this.restaurantAddress);
                }
            } else {
                console.warn('Sezione "restaurant" non trovata in config.json');
            }

            console.log('📍 Configurazione mappa caricata:', {
                name: this.restaurantName,
                address: this.restaurantAddress,
                lat: this.latitude,
                lng: this.longitude
            });
        } catch (error) {
            console.warn('Errore caricamento config mappa:', error);
        }
    }

    /**
     * Geocodifica un indirizzo per ottenere le coordinate
     * Usa Nominatim (OpenStreetMap) - gratis, no API key
     */
    async geocodeAddress(address) {
        try {
            // Controlla cache prima
            if (this.geocodingCache[address]) {
                const cached = this.geocodingCache[address];
                this.latitude = cached.lat;
                this.longitude = cached.lon;
                console.log('📍 Coordinate da cache per:', address);
                return;
            }

            // Nominatim API (OpenStreetMap)
            const encodedAddress = encodeURIComponent(address + ', Italia');
            const url = `https://nominatim.openstreetmap.org/search?format=json&q=${encodedAddress}&limit=1`;

            const response = await fetch(url);
            const results = await response.json();

            if (results && results.length > 0) {
                const result = results[0];
                this.latitude = parseFloat(result.lat);
                this.longitude = parseFloat(result.lon);
                
                // Salva in cache
                this.geocodingCache[address] = {
                    lat: this.latitude,
                    lon: this.longitude
                };
                
                console.log('📍 Indirizzo geocodificato:', address);
            } else {
                console.warn('⚠️ Indirizzo non trovato:', address);
                // Default fallback (Roma, Italia)
                this.latitude = 41.9028;
                this.longitude = 12.4964;
            }
        } catch (error) {
            console.error('Errore geocodifica:', error);
            // Fallback: Roma
            this.latitude = 41.9028;
            this.longitude = 12.4964;
        }
    }

    /**
     * Inizializza la mappa Leaflet
     */
    initializeMap(container) {
        try {
            // Crea la mappa centrata sulle coordinate
            this.map = L.map(container).setView([this.latitude, this.longitude], 16);

            // Aggiungi il tile layer (OpenStreetMap)
            L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png', {
                attribution: '© OpenStreetMap contributors',
                maxZoom: 19,
                minZoom: 2
            }).addTo(this.map);

            // Aggiungi il marker con popup
            this.marker = L.marker([this.latitude, this.longitude], {
                title: this.restaurantName
            }).addTo(this.map);

            // Popup con informazioni
            const popupContent = `
                <div style="font-family: 'Roboto', sans-serif; text-align: center; min-width: 200px;">
                    <h4 style="margin: 5px 0; color: #EF3D26; font-weight: bold;">${this.restaurantName}</h4>
                    <p style="margin: 5px 0; font-size: 12px; color: #666;">
                        ${this.restaurantAddress || 'Indirizzo non disponibile'}
                    </p>
                    <a href="https://www.google.com/maps/search/${encodeURIComponent(this.restaurantAddress)}" 
                       target="_blank" 
                       style="color: #EF3D26; text-decoration: none; font-size: 12px;">
                        Apri in Google Maps →
                    </a>
                </div>
            `;
            
            this.marker.bindPopup(popupContent);
            this.marker.openPopup();

            // Mostra il contenitore
            container.style.display = 'block';

            console.log('✓ Mappa inizializzata e visualizzata');
        } catch (error) {
            console.error('Errore inizializzazione Leaflet:', error);
        }
    }

    /**
     * Aggiorna la mappa con un nuovo indirizzo
     */
    async updateAddress(newAddress) {
        try {
            this.restaurantAddress = newAddress;
            
            // Geocodifica il nuovo indirizzo
            await this.geocodeAddress(newAddress);

            if (this.map && this.marker) {
                // Sposta il marker
                this.marker.setLatLng([this.latitude, this.longitude]);
                this.map.setView([this.latitude, this.longitude], 16);

                // Aggiorna il popup
                const popupContent = `
                    <div style="font-family: 'Roboto', sans-serif; text-align: center; min-width: 200px;">
                        <h4 style="margin: 5px 0; color: #EF3D26; font-weight: bold;">${this.restaurantName}</h4>
                        <p style="margin: 5px 0; font-size: 12px; color: #666;">
                            ${newAddress}
                        </p>
                        <a href="https://www.google.com/maps/search/${encodeURIComponent(newAddress)}" 
                           target="_blank" 
                           style="color: #EF3D26; text-decoration: none; font-size: 12px;">
                            Apri in Google Maps →
                        </a>
                    </div>
                `;
                
                this.marker.setPopupContent(popupContent);
                this.marker.openPopup();

                console.log('✓ Mappa aggiornata con nuovo indirizzo');
            }
        } catch (error) {
            console.error('Errore aggiornamento mappa:', error);
        }
    }

    /**
     * Mostra/Nascondi la mappa
     */
    toggleMap(show = true) {
        const container = document.getElementById('restaurant-map-container');
        if (container) {
            container.style.display = show ? 'block' : 'none';
        }
    }
}

// Inizializza al caricamento della pagina
document.addEventListener('DOMContentLoaded', () => {
    window.mapManager = new MapManager();
});
