<?php require_once 'auth-check.php'; ?>
<!DOCTYPE html>
<html lang="it">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Editor Configurazione - trovapiatto.it</title>
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link rel="preconnect" href="https://cdnjs.cloudflare.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Roboto:wght@400;500;700&display=swap" rel="stylesheet">
    <style>
        body {
            background: linear-gradient(135deg, #f5f7fa 0%, #c3cfe2 100%) !important;
            min-height: 100vh !important;
            font-family: 'Roboto', sans-serif !important;
            color: #333 !important;
        }
        
        * {
            font-family: 'Roboto', sans-serif !important;
        }

        .form-control, .form-select {
            border-radius: 5px;
            border: 1px solid #ddd !important;
            padding: 10px 12px;
            color: #333 !important;
            background-color: #ffffff !important;
        }
        .form-control:focus, .form-select:focus {
            border-color: #537b83 !important;
            box-shadow: 0 0 0 0.2rem rgba(83, 123, 131, 0.25) !important;
            color: #333 !important;
            background-color: #ffffff !important;
        }
        .card {
            border: none;
            border-radius: 10px;
            box-shadow: 0 2px 8px rgba(0,0,0,0.1);
            margin-bottom: 20px;
        }
        .card-header {
            background-color: #537b83;
            color: white;
            border-radius: 10px 10px 0 0;
            padding: 15px;
            font-weight: 600;
        }
        .form-control, .form-select {
            border-radius: 5px;
            border: 1px solid #ddd !important;
            padding: 10px 12px;
            color: #333 !important;
            background-color: #ffffff !important;
        }
        .form-control:focus, .form-select:focus {
            border-color: #537b83 !important;
            box-shadow: 0 0 0 0.2rem rgba(83, 123, 131, 0.25) !important;
            color: #333 !important;
            background-color: #ffffff !important;
        }
        .btn-primary {
            background-color: #537b83;
            border: none;
            border-radius: 5px;
            padding: 10px 20px;
        }
        .btn-primary:hover {
            background-color: #3d5a62;
        }
        .btn-danger {
            background-color: #dc3545;
            border: none;
        }
        .alert {
            border-radius: 5px;
            margin-bottom: 20px;
        }
        .section-title {
            color: #537b83;
            font-weight: 600;
            margin-top: 30px;
            margin-bottom: 15px;
            padding-bottom: 10px;
            border-bottom: 2px solid #537b83;
        }
        .add-item-btn {
            margin-top: 10px;
        }
        .item-card {
            background-color: #f9f9f9;
            border-left: 4px solid #537b83;
            padding: 15px;
            margin-bottom: 10px;
            border-radius: 5px;
        }
        .remove-btn {
            float: right;
            cursor: pointer;
            color: #dc3545;
        }
        .remove-btn:hover {
            color: #c82333;
        }
        .info-text {
            color: #6c757d;
            font-size: 0.9em;
            margin-top: 5px;
        }
    </style>
</head>
<body>

<div class="header">
    <div class="container">
        <h1><i class="fas fa-cog"></i> Editor Configurazione Menu</h1>
        <p>Gestisci tutti i parametri del tuo menu digitale</p>
    </div>
</div>

<div class="container">
    
    <!-- Alert di Salvataggio -->
    <div id="successAlert" class="alert alert-success alert-dismissible fade show d-none" role="alert">
        <i class="fas fa-check-circle"></i> Configurazione salvata con successo!
        <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
    </div>

    <div id="errorAlert" class="alert alert-danger alert-dismissible fade d-none" role="alert">
        <i class="fas fa-exclamation-circle"></i> <span id="errorMsg"></span>
        <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
    </div>

    <!-- Form Principale -->
    <form id="configForm">
        
        <!-- SEZIONE: Configurazione Sito -->
        <div class="card">
            <div class="card-header">
                <i class="fas fa-globe"></i> Configurazione Sito
            </div>
            <div class="card-body">
                <div class="row">
                    <div class="col-md-6">
                        <label class="form-label">Titolo Pagina</label>
                        <input type="text" class="form-control" id="siteTitle" required>
                        <div class="info-text">Titolo che appare nella barra del browser</div>
                    </div>
                    <div class="col-md-6">
                        <label class="form-label">Descrizione SEO</label>
                        <input type="text" class="form-control" id="siteDescription" required>
                    </div>
                </div>
                <div class="row mt-3">
                    <div class="col-md-6">
                        <label class="form-label">Autore</label>
                        <input type="text" class="form-control" id="siteAuthor" required>
                    </div>
                    <div class="col-md-6">
                        <label class="form-label">URL Sito</label>
                        <input type="url" class="form-control" id="ogUrl" required>
                    </div>
                </div>
                <div class="row mt-3">
                    <div class="col-md-6">
                        <label class="form-label">URL Logo</label>
                        <input type="url" class="form-control" id="logoUrl" required>
                    </div>
                    <div class="col-md-6">
                        <label class="form-label">URL Immagine OG</label>
                        <input type="url" class="form-control" id="ogImage" required>
                    </div>
                </div>
                <div class="row mt-3">
                    <div class="col-md-6">
                        <label class="form-label">Google Analytics ID</label>
                        <input type="text" class="form-control" id="analyticsId" placeholder="G-XXXXXXXXXX">
                    </div>
                    <div class="col-md-3">
                        <label class="form-label">Colore Tema</label>
                        <input type="color" class="form-control form-control-color" id="highlightColor">
                    </div>
                    <div class="col-md-3">
                        <label class="form-label">Valuta</label>
                        <input type="text" class="form-control" id="currency" maxlength="3" value="€">
                    </div>
                </div>
            </div>
        </div>

        <!-- SEZIONE: Contatti -->
        <div class="card">
            <div class="card-header">
                <i class="fas fa-phone"></i> Informazioni Contatti
            </div>
            <div class="card-body">
                <div class="row">
                    <div class="col-md-6">
                        <label class="form-label">Telefono</label>
                        <input type="tel" class="form-control" id="contactPhone">
                    </div>
                    <div class="col-md-6">
                        <label class="form-label">Numero WhatsApp</label>
                        <input type="tel" class="form-control" id="whatsappNumber">
                        <div class="info-text">Formato: +39XXXXXXXXX</div>
                    </div>
                </div>
                <div class="row mt-3">
                    <div class="col-md-6">
                        <label class="form-label">Città/Indirizzo</label>
                        <input type="text" class="form-control" id="location">
                    </div>
                    <div class="col-md-6">
                        <label class="form-label">Link Google Maps</label>
                        <input type="url" class="form-control" id="googleMapsLink">
                    </div>
                </div>
            </div>
        </div>

        <!-- SEZIONE: Categorie Menu -->
        <div class="card">
            <div class="card-header">
                <i class="fas fa-list"></i> Categorie Menu
            </div>
            <div class="card-body">
                <div id="categoriesList"></div>
                <button type="button" class="btn btn-success add-item-btn" onclick="addCategory()">
                    <i class="fas fa-plus"></i> Aggiungi Categoria
                </button>
            </div>
        </div>

        <!-- SEZIONE: Social Links -->
        <div class="card">
            <div class="card-header">
                <i class="fas fa-share-alt"></i> Link Social
            </div>
            <div class="card-body">
                <div class="row">
                    <div class="col-md-6">
                        <label class="form-label">Facebook</label>
                        <input type="url" class="form-control" id="facebookLink">
                    </div>
                    <div class="col-md-6">
                        <label class="form-label">WhatsApp Link</label>
                        <input type="url" class="form-control" id="whatsappLink">
                    </div>
                </div>
                <div class="row mt-3">
                    <div class="col-md-6">
                        <label class="form-label">Instagram</label>
                        <input type="url" class="form-control" id="instagramLink">
                    </div>
                    <div class="col-md-6">
                        <label class="form-label">Twitter/X</label>
                        <input type="url" class="form-control" id="twitterLink">
                    </div>
                </div>
            </div>
        </div>

        <!-- AZIONI -->
        <div class="row mt-5 mb-5">
            <div class="col-md-12">
                <button type="submit" class="btn btn-primary btn-lg">
                    <i class="fas fa-save"></i> Salva Configurazione
                </button>
                <button type="reset" class="btn btn-secondary btn-lg ms-2">
                    <i class="fas fa-undo"></i> Annulla
                </button>
                <a href="javascript:history.back()" class="btn btn-outline-secondary btn-lg ms-2">
                    <i class="fas fa-arrow-left"></i> Indietro
                </a>
            </div>
        </div>

    </form>

</div>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
<script>

    // Carica configurazione
    async function loadConfig() {
        try {
            const response = await fetch('config.json');
            const config = await response.json();
            
            // Popola campi sito
            document.getElementById('siteTitle').value = config.siteConfig.title;
            document.getElementById('siteDescription').value = config.siteConfig.description;
            document.getElementById('siteAuthor').value = config.siteConfig.author;
            document.getElementById('ogUrl').value = config.siteConfig.ogUrl;
            document.getElementById('ogImage').value = config.siteConfig.ogImage;
            document.getElementById('analyticsId').value = config.siteConfig.analyticsId;
            document.getElementById('highlightColor').value = config.siteConfig.highlightColor;
            document.getElementById('currency').value = config.siteConfig.currency;
            document.getElementById('logoUrl').value = config.siteConfig.logo;
            
            // Popola contatti
            document.getElementById('contactPhone').value = config.contact.phone;
            document.getElementById('whatsappNumber').value = config.contact.whatsapp;
            document.getElementById('location').value = config.contact.location;
            document.getElementById('googleMapsLink').value = config.contact.googleMapsLink;
            
            // Popola categorie
            renderCategories(config.menu.categories);
            
            // Popola social
            document.getElementById('facebookLink').value = config.socialLinks.facebook;
            document.getElementById('whatsappLink').value = config.socialLinks.whatsapp;
            document.getElementById('instagramLink').value = config.socialLinks.instagram;
            document.getElementById('twitterLink').value = config.socialLinks.twitter;
            
            window.currentConfig = config;
        } catch (error) {
            showError('Errore nel caricamento della configurazione: ' + error.message);
        }
    }

    // Renderizza categorie
    function renderCategories(categories) {
        const list = document.getElementById('categoriesList');
        list.innerHTML = '';
        categories.forEach((cat, index) => {
            list.innerHTML += `
                <div class="item-card">
                    <span class="remove-btn" onclick="removeCategory(${index})"><i class="fas fa-trash"></i></span>
                    <label class="form-label">Categoria ${index + 1}</label>
                    <div class="row">
                        <div class="col-md-6">
                            <input type="text" class="form-control mb-2 cat-name" value="${cat.name}" placeholder="Nome categoria">
                        </div>
                        <div class="col-md-6">
                            <input type="url" class="form-control mb-2 cat-link" value="${cat.link}" placeholder="Link categoria">
                        </div>
                    </div>
                </div>
            `;
        });
    }

    // Aggiungi categoria
    function addCategory() {
        const list = document.getElementById('categoriesList');
        const index = document.querySelectorAll('.item-card').length;
        list.innerHTML += `
            <div class="item-card">
                <span class="remove-btn" onclick="removeCategory(${index})"><i class="fas fa-trash"></i></span>
                <label class="form-label">Categoria ${index + 1}</label>
                <div class="row">
                    <div class="col-md-6">
                        <input type="text" class="form-control mb-2 cat-name" placeholder="Nome categoria">
                    </div>
                    <div class="col-md-6">
                        <input type="url" class="form-control mb-2 cat-link" placeholder="Link categoria">
                    </div>
                </div>
            </div>
        `;
    }

    // Rimuovi categoria
    function removeCategory(index) {
        document.querySelectorAll('.item-card')[index].remove();
    }

    // Salva configurazione
    document.getElementById('configForm').addEventListener('submit', async (e) => {
        e.preventDefault();
        
        const categories = [];
        document.querySelectorAll('.item-card').forEach(card => {
            const name = card.querySelector('.cat-name').value;
            const link = card.querySelector('.cat-link').value;
            if (name && link) {
                categories.push({ name, link });
            }
        });

        const config = {
            siteConfig: {
                title: document.getElementById('siteTitle').value,
                description: document.getElementById('siteDescription').value,
                author: document.getElementById('siteAuthor').value,
                ogUrl: document.getElementById('ogUrl').value,
                ogImage: document.getElementById('ogImage').value,
                ogLocale: "it_IT",
                analyticsId: document.getElementById('analyticsId').value,
                theme: "light",
                highlightColor: document.getElementById('highlightColor').value,
                menuId: "1",
                currency: document.getElementById('currency').value,
                logo: document.getElementById('logoUrl').value
            },
            contact: {
                phone: document.getElementById('contactPhone').value,
                whatsapp: document.getElementById('whatsappNumber').value,
                location: document.getElementById('location').value,
                mapLink: "",
                googleMapsLink: document.getElementById('googleMapsLink').value
            },
            menu: {
                categories: categories
            },
            socialLinks: {
                facebook: document.getElementById('facebookLink').value,
                whatsapp: document.getElementById('whatsappLink').value,
                instagram: document.getElementById('instagramLink').value,
                twitter: document.getElementById('twitterLink').value
            }
        };

        // Salva nel localStorage per demo
        localStorage.setItem('menuConfig', JSON.stringify(config));
        
        showSuccess('Configurazione salvata! I dati sono stati aggiornati.');
    });

    // Mostra successo
    function showSuccess(msg) {
        const alert = document.getElementById('successAlert');
        alert.classList.remove('d-none');
        alert.style.display = 'block';
        setTimeout(() => {
            alert.classList.add('d-none');
        }, 3000);
    }

    // Mostra errore
    function showError(msg) {
        const alert = document.getElementById('errorAlert');
        document.getElementById('errorMsg').textContent = msg;
        alert.classList.remove('d-none');
        alert.style.display = 'block';
        setTimeout(() => {
            alert.classList.add('d-none');
        }, 5000);
    }

    // Carica all'avvio
    loadConfig();

</script>

</body>
</html>
