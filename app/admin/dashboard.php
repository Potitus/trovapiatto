<?php require_once 'auth-check.php'; ?>
<!DOCTYPE html>
<html lang="it">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Dashboard - trovapiatto.it</title>
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link rel="preconnect" href="https://cdnjs.cloudflare.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Roboto:wght@400;500;700&display=swap" rel="stylesheet">
    <style>
        :root {
            --primary-color: #537b83;
            --secondary-color: #3d5a62;
            --success-color: #28a745;
            --danger-color: #dc3545;
        }
        body {
            background: linear-gradient(135deg, #f5f7fa 0%, #c3cfe2 100%);
            min-height: 100vh;
            font-family: 'Roboto', sans-serif !important;
            padding: 20px 0;
            color: #333 !important;
        }
        
        * {
            font-family: 'Roboto', sans-serif !important;
        }
        .navbar-custom {
            background: linear-gradient(135deg, var(--primary-color) 0%, var(--secondary-color) 100%);
            box-shadow: 0 4px 12px rgba(0,0,0,0.15);
            padding: 15px 0;
        }
        .navbar-custom h1 {
            color: white;
            margin-bottom: 0;
            font-weight: 700;
        }
        .dashboard-container {
            max-width: 1200px;
            margin: 30px auto;
        }
        .stat-card {
            background: white;
            border-radius: 12px;
            padding: 25px;
            margin-bottom: 20px;
            box-shadow: 0 2px 12px rgba(0,0,0,0.08);
            border-left: 4px solid var(--primary-color);
            transition: transform 0.3s ease, box-shadow 0.3s ease;
        }
        .stat-card:hover {
            transform: translateY(-5px);
            box-shadow: 0 8px 20px rgba(0,0,0,0.12);
        }
        .stat-icon {
            font-size: 2.5em;
            margin-bottom: 15px;
            color: var(--primary-color);
        }
        .stat-title {
            color: #666;
            font-size: 0.95em;
            margin-bottom: 8px;
            text-transform: uppercase;
            font-weight: 600;
        }
        .stat-value {
            font-size: 1.8em;
            color: var(--primary-color);
            font-weight: 700;
            margin-bottom: 15px;
        }
        .quick-action {
            display: inline-block;
            padding: 8px 16px;
            background: var(--primary-color);
            color: white;
            border-radius: 5px;
            text-decoration: none;
            font-size: 0.9em;
            transition: all 0.3s ease;
            margin-right: 10px;
            margin-top: 10px;
        }
        .quick-action:hover {
            background: var(--secondary-color);
            text-decoration: none;
            color: white;
            transform: translateX(3px);
        }
        .quick-action.secondary {
            background: #6c757d;
        }
        .quick-action.secondary:hover {
            background: #5a6268;
        }
        .quick-action.danger {
            background: var(--danger-color);
        }
        .quick-action.danger:hover {
            background: #c82333;
        }
        .section-title {
            color: var(--primary-color);
            font-size: 1.5em;
            font-weight: 700;
            margin-bottom: 25px;
            padding-bottom: 15px;
            border-bottom: 3px solid var(--primary-color);
        }
        .menu-list {
            background: white;
            border-radius: 12px;
            padding: 0;
            box-shadow: 0 2px 12px rgba(0,0,0,0.08);
            overflow: hidden;
        }
        .menu-item {
            padding: 20px;
            border-bottom: 1px solid #eee;
            display: flex;
            align-items: center;
            justify-content: space-between;
            transition: background 0.3s ease;
        }
        .menu-item:last-child {
            border-bottom: none;
        }
        .menu-item:hover {
            background: #f8f9fa;
        }
        .menu-item-name {
            font-weight: 600;
            color: #333;
        }
        .menu-item-desc {
            color: #999;
            font-size: 0.9em;
            margin-top: 5px;
        }
        .menu-item-status {
            display: inline-block;
            padding: 5px 12px;
            background: var(--success-color);
            color: white;
            border-radius: 20px;
            font-size: 0.85em;
            font-weight: 600;
        }
        .alert-custom {
            background: white;
            border-left: 4px solid var(--primary-color);
            border-radius: 8px;
            padding: 20px;
            margin-bottom: 20px;
            box-shadow: 0 2px 8px rgba(0,0,0,0.06);
        }
        .alert-custom.info {
            border-left-color: #0d6efd;
        }
        .alert-custom.warning {
            border-left-color: #ffc107;
        }
        .alert-custom.success {
            border-left-color: var(--success-color);
        }
        .footer-nav {
            background: white;
            border-radius: 12px;
            padding: 20px;
            margin-top: 40px;
            text-align: center;
            box-shadow: 0 2px 12px rgba(0,0,0,0.08);
        }
        .nav-link-footer {
            display: inline-block;
            margin: 10px;
            padding: 12px 20px;
            background: #f8f9fa;
            border-radius: 5px;
            text-decoration: none;
            color: var(--primary-color);
            font-weight: 600;
            transition: all 0.3s ease;
        }
        .nav-link-footer:hover {
            background: var(--primary-color);
            color: white;
        }
    </style>
</head>
<body>

<div class="navbar-custom">
    <div class="container-lg">
        <h1><i class="fas fa-tachometer-alt"></i> Dashboard Admin trovapiatto.it</h1>
    </div>
</div>

<div class="dashboard-container">
    
    <!-- Alert di Benvenuto -->
    <div class="alert-custom info">
        <i class="fas fa-info-circle"></i>
        <strong>Benvenuto!</strong> Questo è il pannello di controllo per gestire il tuo menu digitale. Accedi all'editor per modificare le configurazioni.
    </div>

    <!-- ROW 1: Statistiche Rapide -->
    <div class="row">
        <div class="col-md-6 col-lg-3">
            <div class="stat-card">
                <div class="stat-icon">
                    <i class="fas fa-cog"></i>
                </div>
                <div class="stat-title">Sistema</div>
                <div class="stat-value" id="systemStatus">Attivo</div>
                <a href="diagnostics.html" class="quick-action secondary">
                    <i class="fas fa-stethoscope"></i> Diagnostica
                </a>
            </div>
        </div>

        <div class="col-md-6 col-lg-3">
            <div class="stat-card">
                <div class="stat-icon">
                    <i class="fas fa-database"></i>
                </div>
                <div class="stat-title">Configurazioni</div>
                <div class="stat-value" id="configStatus">Pronte</div>
                <a href="editor.html" class="quick-action">
                    <i class="fas fa-edit"></i> Modifica
                </a>
            </div>
        </div>

        <div class="col-md-6 col-lg-3">
            <div class="stat-card">
                <div class="stat-icon">
                    <i class="fas fa-list"></i>
                </div>
                <div class="stat-title">Categorie</div>
                <div class="stat-value" id="categoryCount">0</div>
                <a href="editor.html#categorie" class="quick-action">
                    <i class="fas fa-list"></i> Gestisci
                </a>
            </div>
        </div>

        <div class="col-md-6 col-lg-3">
            <div class="stat-card">
                <div class="stat-icon">
                    <i class="fas fa-save"></i>
                </div>
                <div class="stat-title">Storage</div>
                <div class="stat-value" id="storageStatus">OK</div>
                <a href="diagnostics.html" class="quick-action secondary">
                    <i class="fas fa-database"></i> Verifica
                </a>
            </div>
        </div>
    </div>

    <!-- ROW 2: Menu Principali -->
    <div class="row mt-5">
        <div class="col-12">
            <h2 class="section-title">Gestione Menu</h2>
            <div class="menu-list">
                <div class="menu-item">
                    <div>
                        <div class="menu-item-name"><i class="fas fa-pencil-alt"></i> Editor Configurazione</div>
                        <div class="menu-item-desc">Modifica tutti i parametri del menu digitale</div>
                    </div>
                    <a href="editor.html" class="quick-action">Apri Editor</a>
                </div>
                <div class="menu-item">
                    <div>
                        <div class="menu-item-name"><i class="fas fa-guide"></i> Guida Rapida</div>
                        <div class="menu-item-desc">Istruzioni passo per passo su come usare l'editor</div>
                    </div>
                    <a href="index.html" class="quick-action">Leggi Guida</a>
                </div>
                <div class="menu-item">
                    <div>
                        <div class="menu-item-name"><i class="fas fa-stethoscope"></i> Diagnostica Sistema</div>
                        <div class="menu-item-desc">Verifica lo stato del sistema e risolvi eventuali problemi</div>
                    </div>
                    <a href="diagnostics.html" class="quick-action">Esegui Diagnostica</a>
                </div>
                <div class="menu-item">
                    <div>
                        <div class="menu-item-name"><i class="fas fa-home"></i> Torna al Menu</div>
                        <div class="menu-item-desc">Visualizza il menu digitale di trovapiatto.it</div>
                    </div>
                    <a href="../index.html" class="quick-action">Vai al Menu</a>
                </div>
            </div>
        </div>
    </div>

    <!-- ROW 3: Informazioni Configurazione -->
    <div class="row mt-5">
        <div class="col-12">
            <h2 class="section-title">Informazioni Configurazione</h2>
            <div id="configInfo" class="row">
                <div class="col-md-6">
                    <div class="stat-card">
                        <div class="stat-title"><i class="fas fa-globe"></i> Sito</div>
                        <div id="siteInfo" class="text-muted">Caricamento...</div>
                    </div>
                </div>
                <div class="col-md-6">
                    <div class="stat-card">
                        <div class="stat-title"><i class="fas fa-phone"></i> Contatti</div>
                        <div id="contactInfo" class="text-muted">Caricamento...</div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- ROW 4: Azioni Utili -->
    <div class="row mt-5">
        <div class="col-12">
            <h2 class="section-title">Azioni Utili</h2>
            <div class="alert-custom warning">
                <strong><i class="fas fa-exclamation-triangle"></i> Attenzione:</strong>
                Le azioni qui sotto potrebbero influire sulla configurazione. Usa con cautela.
            </div>
            <div class="stat-card">
                <div>
                    <h6>Gestione Storage</h6>
                    <p class="text-muted">Cancella la configurazione salvata e ripristina le impostazioni di default.</p>
                </div>
                <button class="quick-action danger" onclick="clearAllStorage()">
                    <i class="fas fa-trash"></i> Cancella Storage
                </button>
                <button class="quick-action secondary" onclick="exportConfig()">
                    <i class="fas fa-download"></i> Esporta Config
                </button>
                <button class="quick-action secondary" onclick="viewRawConfig()">
                    <i class="fas fa-code"></i> Visualizza JSON
                </button>
            </div>
        </div>
    </div>

    <!-- Footer Navigation -->
    <div class="footer-nav">
        <a href="index.html" class="nav-link-footer">
            <i class="fas fa-guide"></i> Guida
        </a>
        <a href="editor.html" class="nav-link-footer">
            <i class="fas fa-edit"></i> Editor
        </a>
        <a href="diagnostics.html" class="nav-link-footer">
            <i class="fas fa-stethoscope"></i> Diagnostica
        </a>
        <a href="../index.html" class="nav-link-footer">
            <i class="fas fa-home"></i> Menu
        </a>
    </div>

</div>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>

<script>

    // Carica informazioni configurazione
    async function loadDashboardInfo() {
        try {
            const config = JSON.parse(localStorage.getItem('menuConfig'));
            
            if (config) {
                // Categorie
                document.getElementById('categoryCount').textContent = config.menu.categories.length || 0;
                
                // Informazioni Sito
                document.getElementById('siteInfo').innerHTML = `
                    <strong>${config.siteConfig.title}</strong><br>
                    <small>${config.siteConfig.description}</small><br>
                    <small class="text-success">Colore: ${config.siteConfig.highlightColor}</small>
                `;
                
                // Informazioni Contatti
                document.getElementById('contactInfo').innerHTML = `
                    <strong>${config.contact.location}</strong><br>
                    <small>Tel: ${config.contact.phone || 'Non configurato'}</small><br>
                    <small>${config.contact.whatsapp || 'WhatsApp non configurato'}</small>
                `;
            } else {
                document.getElementById('categoryCount').textContent = '8 (default)';
                document.getElementById('siteInfo').innerHTML = '<small class="text-warning">Usando configurazione di default</small>';
                document.getElementById('contactInfo').innerHTML = '<small class="text-warning">Usando configurazione di default</small>';
            }
        } catch (error) {
            console.error('Errore nel caricamento:', error);
            document.getElementById('categoryCount').textContent = 'Errore';
        }
    }

    // Cancella storage
    function clearAllStorage() {
        if (confirm('Sei sicuro? Questa azione cancellerà tutte le configurazioni salvate e ripristinerà i default.')) {
            localStorage.removeItem('menuConfig');
            alert('Storage cancellato. Pagina in ricaricamento...');
            window.location.reload();
        }
    }

    // Esporta configurazione
    function exportConfig() {
        const config = localStorage.getItem('menuConfig');
        if (config) {
            const element = document.createElement('a');
            element.setAttribute('href', 'data:text/plain;charset=utf-8,' + encodeURIComponent(config));
            element.setAttribute('download', 'trovapiatto-config.json');
            element.style.display = 'none';
            document.body.appendChild(element);
            element.click();
            document.body.removeChild(element);
        } else {
            alert('Nessuna configurazione da esportare');
        }
    }

    // Visualizza raw config
    function viewRawConfig() {
        const config = localStorage.getItem('menuConfig');
        if (config) {
            const win = window.open();
            win.document.write('<pre>' + JSON.stringify(JSON.parse(config), null, 2) + '</pre>');
            win.document.title = 'Configurazione JSON';
        } else {
            alert('Nessuna configurazione trovata');
        }
    }

    // Carica al avvio
    window.addEventListener('load', loadDashboardInfo);

</script>

</body>
</html>
