/**
 * Menu Display Manager
 * Renderizza dinamicamente i piatti dal localStorage nella pagina principale
 * Si integra con menu-loader.js per la gestione dei dati
 */

class MenuDisplayManager {
    constructor() {
        this.menuManager = null;
        this.cachedCategories = [];
        // Avvia l'inizializzazione ma non l'aspettare nel constructor
        this.initPromise = this.init();
    }

    /**
     * Inizializza il manager
     */
    async init() {
        try {
            // Aspetta che MenuItemsManager sia disponibile
            if (typeof MenuItemsManager === 'undefined') {
                console.error('MenuItemsManager non trovato. Assicurati che menu-loader.js sia caricato.');
                return;
            }

            this.menuManager = new MenuItemsManager();
            // Aspetta che il manager sia pronto
            await this.menuManager.ready();
            
            if (!this.menuManager.data || !this.menuManager.data.categories) {
                console.error('Menu data non caricato correttamente');
                return;
            }

            this.cachedCategories = this.menuManager.data.categories;
            this.renderMenu();
            
            // Ascolta cambamenti nel localStorage
            window.addEventListener('storage', () => this.onStorageChange());
            
            console.log('✓ MenuDisplayManager inizializzato');
        } catch (error) {
            console.error('Errore inizializzazione MenuDisplayManager:', error);
        }
    }

    /**
     * Renderizza il menu completo nella pagina
     */
    renderMenu() {
        try {
            const menuContainer = document.getElementById('menu-items-container');
            if (!menuContainer) {
                console.warn('Container #menu-items-container non trovato');
                return;
            }

            // Cancella contenitore
            menuContainer.innerHTML = '';

            // Itera categorie
            this.cachedCategories.forEach((category, index) => {
                const categorySection = this.createCategorySection(category, index === 0);
                menuContainer.appendChild(categorySection);
            });

            console.log('✓ Menu renderizzato con successo');
        } catch (error) {
            console.error('Errore rendering menu:', error);
        }
    }

    /**
     * Crea la sezione di una categoria
     */
    createCategorySection(category, isFirst = false) {
        const section = document.createElement('div');
        section.className = 'menu-category-section mb-4';
        section.id = `category-${category.id}`;
        section.dataset.categoryId = category.id;
        section.dataset.expanded = isFirst ? 'true' : 'false';

        // Wrapper per titolo (cliccabile)
        const titleWrapper = document.createElement('div');
        titleWrapper.className = 'category-title-wrapper';
        titleWrapper.style.cssText = 'display: flex; align-items: center; gap: 10px; margin: 20px 0; cursor: pointer; padding: 15px; background: linear-gradient(135deg, rgba(239, 61, 38, 0.05) 0%, rgba(83, 123, 131, 0.05) 100%); border-radius: 10px; transition: all 0.3s ease;';
        titleWrapper.dataset.toggle = 'category';
        
        // Aggiungi evento click
        titleWrapper.addEventListener('click', (e) => this.toggleCategory(section, e));
        titleWrapper.addEventListener('mouseover', () => {
            titleWrapper.style.background = 'linear-gradient(135deg, rgba(239, 61, 38, 0.1) 0%, rgba(83, 123, 131, 0.1) 100%)';
            titleWrapper.style.transform = 'scale(1.01)';
        });
        titleWrapper.addEventListener('mouseout', () => {
            titleWrapper.style.background = 'linear-gradient(135deg, rgba(239, 61, 38, 0.05) 0%, rgba(83, 123, 131, 0.05) 100%)';
            titleWrapper.style.transform = 'scale(1)';
        });

        // Icona categoria
        const icon = document.createElement('span');
        icon.className = 'category-icon';
        icon.style.cssText = 'font-size: 24px;';
        icon.textContent = this.getCategoryIcon(category.id);

        // Titolo categoria
        const title = document.createElement('h2');
        title.className = 'font-22 font-700 color-highlight';
        title.style.cssText = 'margin: 0; flex: 1;';
        title.textContent = category.name;

        // Icona toggle
        const toggleIcon = document.createElement('span');
        toggleIcon.className = 'category-toggle-icon';
        toggleIcon.style.cssText = `font-size: 20px; transition: transform 0.3s ease; color: #EF3D26; transform: ${isFirst ? 'rotate(180deg)' : 'rotate(0deg)'};`;
        toggleIcon.textContent = '▼';

        titleWrapper.appendChild(icon);
        titleWrapper.appendChild(title);
        titleWrapper.appendChild(toggleIcon);
        section.appendChild(titleWrapper);

        // Contenitore item (collapsabile)
        const itemsContainer = document.createElement('div');
        itemsContainer.className = 'menu-items-row category-items-container';
        itemsContainer.setAttribute('data-items-container', 'true');
        
        // Se è la prima categoria, è espansa di default
        if (isFirst) {
            itemsContainer.classList.add('expanded');
            itemsContainer.style.cssText = 'max-height: 10000px; opacity: 1; overflow: hidden; transition: all 0.5s ease; padding: 20px 0 0 0; margin: 20px 0 0 0; display: grid; grid-template-columns: repeat(auto-fill, minmax(280px, 1fr)); gap: 20px;';
        } else {
            itemsContainer.style.cssText = 'max-height: 0; opacity: 0; overflow: hidden; transition: all 0.5s ease; padding: 0; margin: 0; display: grid; grid-template-columns: repeat(auto-fill, minmax(280px, 1fr)); gap: 20px;';
        }

        // Carica items della categoria
        const items = this.menuManager.getItemsByCategory(category.id);
        
        if (items.length === 0) {
            const emptyMsg = document.createElement('p');
            emptyMsg.className = 'text-center color-gray2-dark font-13 pl-3 pr-3';
            emptyMsg.textContent = `Nessun piatto disponibile in "${category.name}"`;
            itemsContainer.appendChild(emptyMsg);
        } else {
            items.forEach(item => {
                const itemCard = this.createItemCard(item);
                itemsContainer.appendChild(itemCard);
            });
        }

        section.appendChild(itemsContainer);
        return section;
    }

    /**
     * Crea la card di un piatto
     */
    createItemCard(item) {
        const card = document.createElement('div');
        card.className = 'card card-style mb-3';
        card.id = `item-${item.id}`;
        card.setAttribute('data-item-id', item.id);

        // Immagine
        let imageHtml = '';
        if (item.image) {
            imageHtml = `<img data-src="${item.image}" class="img-fluid bottom-20" src="${item.image}" alt="${item.title}">`;
        }

        // Tag icone
        let tagsHtml = '';
        if (item.tags && item.tags.length > 0) {
            tagsHtml = '<div class="d-flex justify-content-lg-start mt-3 mb-3">';
            item.tags.forEach(tag => {
                const tagIcon = this.getTagIcon(tag);
                if (tagIcon) {
                    tagsHtml += `<a href="javascript:void(0);" data-menu="menu-caratteristiche"><img src="${tagIcon}" alt="${tag}" class="preload-img mr-3" width="25"></a>`;
                }
            });
            tagsHtml += '</div>';
        }

        // Allergeni
        let allergeniHtml = '';
        if (item.allergens && item.allergens.length > 0) {
            const allergeniList = item.allergens.join(', ');
            allergeniHtml = `
                <div class="mb-3">
                    <a href="javascript:void(0);" class="font-12 color-theme" data-menu="menu-allergeni">
                        <i class="fa fa-exclamation-circle mr-2"></i><b>Allergeni:</b> ${allergeniList}
                    </a>
                    <div class="divider mt-3 mb-3"></div>
                </div>
            `;
        }

        // Disponibilità
        const availabilityClass = item.available ? 'bg-green1-dark' : 'bg-danger';
        const availabilityText = item.available ? 'Ordina' : 'Non Disponibile';
        const disabledAttr = !item.available ? 'disabled' : '';

        card.innerHTML = `
            ${imageHtml}
            <div class="content mb-0">
                <div class="d-flex mb-0">
                    <h2 class="mb-0 font-18">${this.escapeHtml(item.title)}</h2>
                    <div class="flex-grow-1 pr-1">
                        <div class="flex-shrink-1 pl-1 text-right prezzo">
                            <h2 class="font-20 font-weight-bold mt-0 mb-0 notranslate">&euro; ${item.price.toFixed(2)}</h2>
                        </div>
                    </div>
                </div>

                <div class="clearfix"></div>

                ${item.description ? `<p class="descrizione mb-0">${this.escapeHtml(item.description)}</p>` : ''}

                <div class="divider mt-3 mb-3"></div>

                ${tagsHtml}

                ${allergeniHtml}

                <a href="javascript:void(0);" 
                    class="lmcart-add btn btn-m btn-full mb-3 rounded-xl text-uppercase font-900 shadow-s ${availabilityClass} btn-icon text-left"
                    data-id="${item.id}"
                    data-title="${this.escapeHtml(item.title)}"
                    data-cat="${item.categoryId}"
                    data-price="${item.price.toFixed(2)}"
                    ${disabledAttr}>
                    <i class="far fa-list-alt font-15 text-center"></i>
                    ${availabilityText}
                </a>

                <div class="clearfix"></div>
                <div class="mb-3"></div>
            </div>
        `;

        return card;
    }

    /**
     * Mappa tag a icone
     */
    getTagIcon(tag) {
        const iconMap = {
            'biologico': '/assets/images/icons/biologico.png',
            'vegetariano': '/assets/images/icons/vegetariano.png',
            'vegano': '/assets/images/icons/vegano.png',
            'noglutine': '/assets/images/icons/noglutine.png',
            'piccante': '/assets/images/icons/piccante.png'
        };
        return iconMap[tag.toLowerCase()] || null;
    }

    /**
     * Ottiene icona per categoria
     */
    getCategoryIcon(categoryId) {
        const iconMap = {
            'antipasti': '🥗',
            'primi': '🍝',
            'secondi': '🍖',
            'pizze': '🍕',
            'dessert': '🍰',
            'vini-rossi': '🍷',
            'vini-bianchi': '🍷',
            'menu-fisso': '🍽️'
        };
        return iconMap[categoryId] || '📋';
    }

    /**
     * Escapa HTML per prevenire XSS
     */
    escapeHtml(text) {
        const div = document.createElement('div');
        div.textContent = text;
        return div.innerHTML;
    }

    /**
     * Toggle collapsible section - Espande/Collassa sezione categoria
     */
    toggleCategory(section, event) {
        event.preventDefault();
        event.stopPropagation();
        
        const toggleIcon = section.querySelector('.category-toggle-icon');
        const itemsContainer = section.querySelector('[data-items-container]');
        
        if (!toggleIcon || !itemsContainer) {
            console.warn('Elemento toggle o container non trovato');
            return;
        }
        
        const isExpanded = itemsContainer.classList.contains('expanded');
        
        if (isExpanded) {
            // Collassa
            itemsContainer.classList.remove('expanded');
            itemsContainer.style.maxHeight = '0';
            itemsContainer.style.opacity = '0';
            itemsContainer.style.padding = '0';
            itemsContainer.style.margin = '0';
            toggleIcon.style.transform = 'rotate(0deg)';
            console.log('📉 Collassando categoria');
        } else {
            // Espandi - calcola l'altezza del contenuto
            itemsContainer.classList.add('expanded');
            // Imposta temporaneamente a auto per calcolare l'altezza reale
            itemsContainer.style.maxHeight = 'none';
            const height = itemsContainer.scrollHeight;
            // Ritorna al valore calcolato
            itemsContainer.style.maxHeight = (height + 40) + 'px';
            itemsContainer.style.opacity = '1';
            itemsContainer.style.padding = '20px 0 0 0';
            itemsContainer.style.margin = '20px 0 0 0';
            toggleIcon.style.transform = 'rotate(180deg)';
            console.log('📈 Espandendo categoria - height:', height);
        }
    }

    /**
     * Gestisce cambamenti nel localStorage da altre tab
     */
    onStorageChange() {
        console.log('📢 Cambamenti rilevati nel localStorage, aggiornamento menu...');
        this.init();
    }
}

// Inizializza al caricamento della pagina
document.addEventListener('DOMContentLoaded', () => {
    window.menuDisplay = new MenuDisplayManager();
});
