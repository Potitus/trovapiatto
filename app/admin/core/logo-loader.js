/**
 * Logo Loader
 * Carica il logo del ristorante dall'API e lo visualizza nell'header
 */

class LogoLoader {
    constructor() {
        // Usa un percorso assoluto rispetto alla root del dominio
        this.apiUrl = '/app/admin/api-crud.php';
        this.init();
    }

    async init() {
        try {
            // Non fare nulla se siamo in admin
            const isAdmin = window.location.pathname.includes('/admin/');
            if (isAdmin) {
                console.log('📷 LogoLoader: disabilitato in admin');
                return;
            }

            console.log('📷 LogoLoader: inizializzazione...');

            // Estrai il restaurant ID dalla URL
            // URL pattern: /beta/app/menu/{restaurant_id}/
            const pathMatch = window.location.pathname.match(/\/menu\/([^\/]+)/);
            const restaurantId = pathMatch ? pathMatch[1] : null;
            
            if (!restaurantId) {
                console.warn('⚠️ Restaurant ID non trovato nella URL');
                return;
            }

            console.log('📷 Caricando logo per ristorante:', restaurantId);
            
            // Carica i dati del ristorante dall'API
            const apiUrl = `${this.apiUrl}?action=get-restaurant&id=${restaurantId}`;
            console.log('📷 Chiamando API:', apiUrl);
            
            const apiResponse = await fetch(apiUrl);
            
            if (!apiResponse.ok) {
                const responseText = await apiResponse.text();
                console.warn('⚠️ API non disponibile (status: ' + apiResponse.status + '). Response:', responseText);
                return;
            }

            const data = await apiResponse.json();
            console.log('📷 Risposta API:', data);
            
            const DEFAULT_LOGO = '/logo/default-ristorante.svg';
            if (data.success && data.data && data.data.logo_url) {
                this.applyLogo(data.data.logo_url);
                console.log('✅ Logo caricato:', data.data.logo_url);
            } else if (data.success && data.data) {
                this.applyLogo(DEFAULT_LOGO);
                console.log('✅ Logo di default applicato:', DEFAULT_LOGO);
            } else {
                console.warn('⚠️ Nessun logo trovato per il ristorante. Data:', data);
            }
        } catch (error) {
            console.error('⚠️ Errore nel caricamento del logo:', error.message, error);
        }
    }

    applyLogo(logoUrl) {
        try {
            // Applica il logo nell'header
            const headerLogo = document.querySelector('.header-logo');
            if (headerLogo) {
                headerLogo.style.backgroundImage = `url('${logoUrl}')`;
                headerLogo.style.backgroundSize = 'contain';
                headerLogo.style.backgroundRepeat = 'no-repeat';
                headerLogo.style.backgroundPosition = 'center';
                console.log('✅ Logo applicato all\'header');
            } else {
                console.warn('⚠️ .header-logo non trovato');
            }

            // Applica il logo nel preloader
            const preloadImages = document.querySelectorAll('#preloader img.preload-img');
            if (preloadImages.length > 0) {
                preloadImages.forEach((img, index) => {
                    img.src = logoUrl;
                    img.dataset.src = logoUrl;
                    console.log(`✅ Logo applicato al preloader ${index + 1}`);
                });
            }

            // Aggiorna il favicon
            this.updateFavicon(logoUrl);
        } catch (error) {
            console.error('⚠️ Errore nell\'applicazione del logo:', error.message);
        }
    }

    updateFavicon(logoUrl) {
        try {
            let link = document.querySelector("link[rel*='icon']");
            if (!link) {
                link = document.createElement('link');
                document.head.appendChild(link);
            }
            link.rel = 'shortcut icon';
            link.href = logoUrl;
            console.log('✅ Favicon aggiornato');
        } catch (error) {
            console.warn('⚠️ Errore nell\'aggiornamento favicon:', error.message);
        }
    }
}

// Inizializza il logo loader quando il DOM è pronto
if (document.readyState === 'loading') {
    document.addEventListener('DOMContentLoaded', () => {
        new LogoLoader();
    });
} else {
    new LogoLoader();
}

