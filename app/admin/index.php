<?php
// Punto d'accesso alla cartella admin.
// Include il middleware di autenticazione; se l'utente è loggato, lo reindirizza
// all'interfaccia amministrativa principale (index.html).
require __DIR__ . '/auth-check.php';

// Se siamo arrivati qui, auth-check ha permesso l'accesso (o ha eseguito il login)
// Reindirizza alla UI amministrativa HTML presente nella cartella
header('Location: index.html');
exit();
?>
<?php require_once 'auth-check.php'; ?>
<!DOCTYPE html>
<html lang="it">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Dashboard Admin - trovapiatto.it</title>
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link rel="preconnect" href="https://cdnjs.cloudflare.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Roboto:wght@400;500;700&display=swap" rel="stylesheet">
    <style>
        body {
            background: linear-gradient(135deg, #537b83 0%, #3d5a62 100%);
            min-height: 100vh;
            display: flex;
            align-items: center;
            justify-content: center;
            font-family: 'Roboto', sans-serif;
        }
        .dashboard-container {
            background: white;
            border-radius: 12px;
            box-shadow: 0 10px 40px rgba(0,0,0,0.2);
            padding: 40px;
            max-width: 900px;
            width: 100%;
            animation: fadeIn 0.6s ease;
        }
        @keyframes fadeIn {
            from { opacity: 0; transform: translateY(20px); }
            to { opacity: 1; transform: translateY(0); }
        }
        .dashboard-title {
            color: #537b83;
            font-weight: 700;
            margin-bottom: 30px;
            font-size: 2em;
            text-align: center;
        }
        .menu-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(250px, 1fr));
            gap: 20px;
            margin-bottom: 30px;
        }
        .menu-card {
            background: white;
            border: 2px solid #537b83;
            border-radius: 10px;
            padding: 20px;
            text-align: center;
            transition: all 0.3s ease;
            text-decoration: none;
            color: #333;
        }
        .menu-card:hover {
            background: linear-gradient(135deg, #537b83 0%, #3d5a62 100%);
            color: white;
            transform: translateY(-4px);
            box-shadow: 0 8px 20px rgba(83, 123, 131, 0.3);
        }
        .menu-card i {
            font-size: 2em;
            margin-bottom: 10px;
            display: block;
        }
        .menu-card-title {
            font-weight: 600;
            font-size: 1.1em;
            margin-bottom: 8px;
        }
        .menu-card-desc {
            font-size: 0.9em;
            opacity: 0.8;
        }
        .logout-btn {
            background-color: #EF3D26;
            border: none;
            color: white;
            padding: 10px 20px;
            border-radius: 6px;
            font-weight: 600;
            text-decoration: none;
            display: inline-block;
            transition: all 0.3s ease;
        }
        .logout-btn:hover {
            background-color: #d63018;
            transform: translateY(-2px);
            box-shadow: 0 4px 12px rgba(239, 61, 38, 0.3);
            color: white;
        }
        .header-section {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 30px;
            padding-bottom: 20px;
            border-bottom: 2px solid #537b83;
        }
        .user-info {
            font-size: 0.95em;
            color: #666;
        }
        .user-info strong {
            color: #537b83;
        }
    </style>
</head>
<body>

<div class="dashboard-container">
    <div class="header-section">
        <div>
            <h1 class="dashboard-title" style="margin: 0; font-size: 1.8em;">
                <i class="fas fa-building"></i> trovapiatto Admin
            </h1>
        </div>
        <div class="user-info">
            Autenticato come <strong>admin</strong>
            <br>
            <a href="logout.php" class="logout-btn"><i class="fas fa-sign-out-alt"></i> Logout</a>
        </div>
    </div>

    <div class="menu-grid">
        <a href="restaurants.php" class="menu-card">
            <i class="fas fa-utensils"></i>
            <div class="menu-card-title">Gestione Ristoranti</div>
            <div class="menu-card-desc">Crea e modifica ristoranti</div>
        </a>

        <a href="dashboard.php" class="menu-card">
            <i class="fas fa-chart-bar"></i>
            <div class="menu-card-title">Dashboard</div>
            <div class="menu-card-desc">Statistiche e metriche</div>
        </a>

        <a href="menu-manager.php" class="menu-card">
            <i class="fas fa-list"></i>
            <div class="menu-card-title">Gestione Menu</div>
            <div class="menu-card-desc">Modifica menu e piatti</div>
        </a>

        <a href="editor.php" class="menu-card">
            <i class="fas fa-edit"></i>
            <div class="menu-card-title">Editor Configurazione</div>
            <div class="menu-card-desc">Personalizza layout</div>
        </a>

        <a href="crea-admin.php" class="menu-card">
            <i class="fas fa-user-shield"></i>
            <div class="menu-card-title">Crea/Modifica Admin</div>
            <div class="menu-card-desc">Aggiungi o aggiorna credenziali amministratore</div>
        </a>

        <a href="import-menu-ocr.php" class="menu-card">
            <i class="fas fa-camera"></i>
            <div class="menu-card-title">Importa da Foto</div>
            <div class="menu-card-desc">Estrai menu via OCR</div>
        </a>

        <a href="menu-guide.php" class="menu-card">
            <i class="fas fa-question-circle"></i>
            <div class="menu-card-title">Guida Rapida</div>
            <div class="menu-card-desc">Come usare il sistema</div>
        </a>
    </div>

    <div style="text-align: center; color: #999; font-size: 0.9em;">
        <p><i class="fas fa-lock"></i> Quest'area è protetta da autenticazione</p>
    </div>
</div>

</body>
</html>
