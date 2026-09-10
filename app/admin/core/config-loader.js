/**
 * Menu Config Loader
 * Carica la configurazione del menu da config.json e la applica alla pagina
 */

class MenuConfigLoader {
    constructor() {
        this.config = null;
        this.init();
    }

    async init() {
        try {
            // Controlla se siamo in admin
            const isAdmin = window.location.pathname.includes('/admin/');
            if (isAdmin) return; // Non caricare in admin

            // Carica config da localStorage prima (per modifiche live)
            const savedConfig = localStorage.getItem('menuConfig');
            if (savedConfig) {
                this.config = JSON.parse(savedConfig);
            } else {
                // Altrimenti carica da file
                const response = await fetch('./admin/config.json');
                this.config = await response.json();
            }

            this.applyConfig();
        } catch (error) {
            console.error('Errore nel caricamento della configurazione:', error);
        }
    }

    applyConfig() {
        if (!this.config) return;

        const cfg = this.config;

        // Aggiorna SEO
        this.updateSEO(cfg.siteConfig);

        // Aggiorna header
        this.updateHeader(cfg);

        // Aggiorna menu categorie
        this.updateCategories(cfg.menu.categories);

        // Aggiorna contatti
        this.updateContacts(cfg.contact);

        // Aggiorna social
        this.updateSocial(cfg.socialLinks);

        // Aggiorna colore tema
        this.updateTheme(cfg.siteConfig.highlightColor);
    }

    updateSEO(siteConfig) {
        // Aggiorna titolo
        document.title = siteConfig.title;

        // Aggiorna meta tags
        this.updateMetaTag('name', 'description', siteConfig.description);
        this.updateMetaTag('name', 'author', siteConfig.author);
        this.updateMetaTag('property', 'og:title', siteConfig.title);
        this.updateMetaTag('property', 'og:description', siteConfig.description);
        this.updateMetaTag('property', 'og:image', siteConfig.ogImage);
        this.updateMetaTag('property', 'og:url', siteConfig.ogUrl);
        this.updateMetaTag('name', 'theme-color', siteConfig.highlightColor);

        // Aggiorna GA
        if (siteConfig.analyticsId) {
            this.updateGoogleAnalytics(siteConfig.analyticsId);
        }
    }

    updateMetaTag(attr, name, content) {
        let tag = document.querySelector(`meta[${attr}="${name}"]`);
        if (!tag) {
            tag = document.createElement('meta');
            tag.setAttribute(attr, name);
            document.head.appendChild(tag);
        }
        tag.setAttribute('content', content);
    }

    updateHeader(config) {
        const logo = document.querySelector('.header-logo');
        if (logo) {
            logo.style.backgroundImage = `url(${config.siteConfig.logo})`;
        }

        // Aggiungi logo al Page Header banner
        const pageHeader = document.querySelector('.page-header.card');
        if (pageHeader) {
            let logoElement = pageHeader.querySelector('.page-header-logo');
            if (!logoElement) {
                // Crea elemento logo se non esiste
                logoElement = document.createElement('img');
                logoElement.className = 'page-header-logo';
                logoElement.style.cssText = 'max-height: 120px; max-width: 80%; margin-bottom: 15px; object-fit: contain;';
                
                // Inserisci prima del card-bottom
                const cardBottom = pageHeader.querySelector('.card-bottom');
                if (cardBottom) {
                    pageHeader.insertBefore(logoElement, cardBottom);
                }
            }
            logoElement.src = config.siteConfig.logo;
            logoElement.alt = 'Logo Ristorante';
        }
    }

    updateCategories(categories) {
        const list = document.querySelector('.list-group.list-custom-small.list-icon-0');
        if (!list) return;

        // Mantieni il primo elemento (divider)
        const firstChild = list.querySelector('.divider');
        list.innerHTML = '';
        if (firstChild) {
            list.appendChild(firstChild);
        }

        categories.forEach(cat => {
            const link = document.createElement('a');
            link.href = cat.link;
            link.className = 'cat-link main-lev cat-pl-0';
            link.innerHTML = `
                <span class="font-13 line-height-s">${cat.name}</span>
                <i class="fa fa-angle-right"></i>
            `;
            list.appendChild(link);
        });
    }

    updateContacts(contact) {
        // Aggiorna telefono
        const phoneLink = document.querySelector('a[href=""]');
        if (phoneLink && contact.phone) {
            phoneLink.href = `tel:${contact.phone}`;
            phoneLink.querySelector('span').textContent = contact.phone;
        }

        // Aggiorna WhatsApp
        const whatsappBtn = document.querySelector('.contactWhatsApp');
        if (whatsappBtn && contact.whatsapp) {
            whatsappBtn.dataset.number = contact.whatsapp;
        }

        // Aggiorna indirizzo
        const mapLink = document.querySelector('a[target="_blank"]');
        if (mapLink) {
            mapLink.href = contact.googleMapsLink || '';
            mapLink.querySelector('strong').textContent = contact.location;
        }
    }

    updateSocial(socialLinks) {
        const shareToFacebook = document.querySelector('.shareToFacebook');
        const shareToWhatsApp = document.querySelector('.shareToWhatsApp');

        if (shareToFacebook) {
            shareToFacebook.dataset.link = socialLinks.facebook || '';
        }
        if (shareToWhatsApp) {
            shareToWhatsApp.dataset.link = socialLinks.whatsapp || '';
        }
    }

    updateTheme(color) {
        document.body.style.setProperty('--highlight-color', color);
        // Crea uno stile dinamico per il colore tema
        const style = document.createElement('style');
        style.innerHTML = `
            :root {
                --color-highlight: ${color};
            }
            .color-highlight {
                color: ${color} !important;
            }
            .bg-highlight {
                background-color: ${color} !important;
            }
            .btn-highlight {
                background-color: ${color} !important;
                border-color: ${color} !important;
            }
        `;
        document.head.appendChild(style);
    }

    updateGoogleAnalytics(gaId) {
        // Aggiorna GA ID se necessario
        window.dataLayer = window.dataLayer || [];
        function gtag(){dataLayer.push(arguments);}
        gtag('js', new Date());
        gtag('config', gaId, { 'anonymize_ip': true });
    }

    // Invia un semplice pageview al backend admin (non visibile ai visitatori)
    sendPageviewToAdmin() {
        try {
            const payload = JSON.stringify({ path: window.location.pathname });
            const url = '/app/admin/log_pageview.php';
            if (navigator.sendBeacon) {
                const blob = new Blob([payload], { type: 'application/json' });
                navigator.sendBeacon(url, blob);
            } else {
                // fallback fetch non-blocking
                fetch(url, { method: 'POST', body: payload, headers: { 'Content-Type': 'application/json' }, keepalive: true }).catch(()=>{});
            }
        } catch (e) {
            // no-op
        }
    }
}

// Inizializza quando il DOM è pronto
if (document.readyState === 'loading') {
    document.addEventListener('DOMContentLoaded', () => {
        const loader = new MenuConfigLoader();
        // registra pageview (silezioso)
        try { loader.sendPageviewToAdmin(); } catch(e) {}
    });
} else {
    const loader = new MenuConfigLoader();
    try { loader.sendPageviewToAdmin(); } catch(e) {}
}
