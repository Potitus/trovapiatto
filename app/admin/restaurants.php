<?php require_once 'auth-check.php'; ?>
<!DOCTYPE html>
<html lang="it">
<head>
    <!-- Cache Buster: 20251128-v2 -->
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Gestione Ristoranti - trovapiatto.it</title>
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link rel="preconnect" href="https://cdnjs.cloudflare.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Roboto:wght@400;500;700&display=swap" rel="stylesheet">
    <style>
        :root {
            --primary: #537b83;
            --secondary: #3d5a62;
            --accent: #EF3D26;
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

        .form-label {
            color: #333 !important;
            font-weight: 600 !important;
            text-transform: uppercase;
            letter-spacing: 0.5px;
            font-size: 0.9em;
        }

        .form-control, .form-select {
            border-radius: 8px;
            border: 2px solid #ccc !important;
            transition: all 0.3s ease;
            background-color: #ffffff !important;
            color: #333 !important;
        }

        .form-control::placeholder {
            color: #999 !important;
        }

        .form-control:focus, .form-select:focus {
            border-color: #537b83 !important;
            box-shadow: 0 0 0 0.3rem rgba(83, 123, 131, 0.15) !important;
            background-color: #ffffff !important;
            color: #333 !important;
        }

        .form-select {
            color: #333 !important;
        }

        .form-select option {
            color: #333;
            background-color: white;
        }

        @keyframes fadeIn {
            from { opacity: 0; }
            to { opacity: 1; }
        }

        .header {
            background: linear-gradient(135deg, var(--primary) 0%, var(--secondary) 100%);
            color: white;
            padding: 30px 0;
            margin-bottom: 30px;
            box-shadow: 0 4px 12px rgba(0,0,0,0.15);
            animation: slideDown 0.5s ease;
        }

        @keyframes slideDown {
            from {
                opacity: 0;
                transform: translateY(-30px);
            }
            to {
                opacity: 1;
                transform: translateY(0);
            }
        }

        .header h1 {
            margin: 0;
            font-weight: 700;
            letter-spacing: 0.5px;
        }

        .header p {
            margin: 5px 0 0 0;
            opacity: 0.9;
            font-size: 0.95em;
        }

        .card {
            border: none;
            border-radius: 12px;
            box-shadow: 0 2px 8px rgba(0,0,0,0.1);
            margin-bottom: 20px;
            transition: all 0.4s cubic-bezier(0.25, 0.46, 0.45, 0.94);
            animation: fadeInUp 0.6s ease;
        }

        @keyframes fadeInUp {
            from {
                opacity: 0;
                transform: translateY(20px);
            }
            to {
                opacity: 1;
                transform: translateY(0);
            }
        }

        .card:hover {
            transform: translateY(-4px);
            box-shadow: 0 8px 20px rgba(0,0,0,0.12);
        }

        .card-header {
            background: linear-gradient(135deg, var(--primary) 0%, var(--secondary) 100%);
            color: white;
            border: none;
            padding: 15px 20px;
            font-weight: 600;
            border-radius: 12px 12px 0 0;
            text-transform: uppercase;
            letter-spacing: 0.5px;
            font-size: 0.95em;
        }

        .btn-primary {
            background-color: var(--primary);
            border-color: var(--primary);
            transition: all 0.3s ease;
            position: relative;
            overflow: hidden;
        }

        .btn-primary:hover {
            background-color: var(--secondary);
            border-color: var(--secondary);
            transform: translateY(-2px);
            box-shadow: 0 4px 12px rgba(83, 123, 131, 0.3);
        }

        .btn-primary:active {
            transform: translateY(0);
        }

        .btn-accent {
            background-color: var(--accent);
            border-color: var(--accent);
            transition: all 0.3s ease;
            position: relative;
            overflow: hidden;
        }

        .btn-accent:hover {
            background-color: #d63018;
            border-color: #d63018;
            transform: translateY(-2px);
            box-shadow: 0 4px 12px rgba(239, 61, 38, 0.3);
        }

        .btn-accent:active {
            transform: translateY(0);
        }

        .restaurant-item {
            background: white;
            padding: 20px;
            border-radius: 10px;
            margin-bottom: 15px;
            border-left: 4px solid var(--primary);
            transition: all 0.4s cubic-bezier(0.25, 0.46, 0.45, 0.94);
            animation: slideInLeft 0.5s ease backwards;
        }

        @keyframes slideInLeft {
            from {
                opacity: 0;
                transform: translateX(-20px);
            }
            to {
                opacity: 1;
                transform: translateX(0);
            }
        }

        .restaurant-item:hover {
            box-shadow: 0 6px 16px rgba(83, 123, 131, 0.2);
            border-left-width: 6px;
            transform: translateX(4px);
        }

        .restaurant-header {
            display: flex;
            align-items: center;
            cursor: pointer;
            gap: 15px;
            margin-bottom: 0;
            transition: all 0.3s ease;
        }

        .restaurant-header:hover {
            color: var(--accent);
        }

        .restaurant-toggle {
            font-size: 1.2em;
            color: var(--primary);
            transition: transform 0.4s cubic-bezier(0.25, 0.46, 0.45, 0.94);
            width: 25px;
            text-align: center;
        }

        .restaurant-toggle.collapsed {
            transform: rotate(-90deg);
        }

        .restaurant-content {
            max-height: 1000px;
            overflow: hidden;
            transition: all 0.4s ease;
            opacity: 1;
            margin-top: 15px;
            animation: expandDown 0.4s ease;
        }

        @keyframes expandDown {
            from {
                opacity: 0;
                max-height: 0;
            }
            to {
                opacity: 1;
                max-height: 1000px;
            }
        }

        .restaurant-content.collapsed {
            max-height: 0;
            opacity: 0;
            margin-top: 0;
            animation: none;
        }

        .restaurant-name {
            font-size: 1.3em;
            font-weight: 600;
            color: var(--primary);
            margin-bottom: 10px;
            letter-spacing: 0.3px;
        }

        .restaurant-info {
            font-size: 0.95em;
            color: #666;
            margin-bottom: 10px;
            transition: color 0.3s ease;
        }

        .restaurant-info i {
            color: var(--primary);
            width: 20px;
            margin-right: 10px;
            transition: transform 0.3s ease;
        }

        .restaurant-item:hover .restaurant-info i {
            transform: translateX(3px);
        }

        .restaurant-slug {
            background: linear-gradient(135deg, #f5f5f5 0%, #ebebeb 100%);
            padding: 8px 12px;
            border-radius: 6px;
            font-family: 'Monaco', monospace;
            font-size: 0.85em;
            display: inline-block;
            margin-bottom: 10px;
            border-left: 3px solid var(--primary);
            transition: all 0.3s ease;
            color: var(--secondary);
            font-weight: 600;
        }

        .restaurant-slug:hover {
            background: linear-gradient(135deg, #efefef 0%, #e0e0e0 100%);
            transform: translateX(2px);
        }

        .restaurant-actions {
            display: flex;
            gap: 10px;
            margin-top: 15px;
            flex-wrap: wrap;
        }

        .restaurant-actions .btn {
            flex: 1;
            min-width: 120px;
            font-size: 0.9em;
            transition: all 0.3s ease;
        }

        .restaurant-actions .btn:hover {
            transform: translateY(-2px);
        }

        .stats-box {
            background: white;
            padding: 20px;
            border-radius: 10px;
            text-align: center;
            margin-bottom: 20px;
            box-shadow: 0 2px 8px rgba(0,0,0,0.1);
            transition: all 0.4s ease;
            animation: fadeInUp 0.6s ease;
            border-top: 3px solid var(--primary);
        }

        .stats-box:hover {
            transform: translateY(-4px);
            box-shadow: 0 8px 16px rgba(0,0,0,0.12);
        }

        .stats-box h6 {
            color: #666;
            margin-bottom: 10px;
            font-size: 0.9em;
            text-transform: uppercase;
            letter-spacing: 1px;
        }

        .stats-box .number {
            font-size: 2.5em;
            color: var(--primary);
            font-weight: 700;
            text-shadow: 0 2px 4px rgba(83, 123, 131, 0.1);
        }

        .empty-state {
            text-align: center;
            padding: 40px 20px;
            color: #999;
            animation: fadeIn 0.6s ease;
        }

        .empty-state i {
            font-size: 3em;
            color: #ddd;
            margin-bottom: 15px;
            transition: all 0.4s ease;
            animation: bounce 2s infinite;
        }

        @keyframes bounce {
            0%, 100% { transform: translateY(0); }
            50% { transform: translateY(-10px); }
        }

        .alert {
            border-radius: 8px;
            border: none;
            animation: slideInDown 0.4s ease;
            box-shadow: 0 2px 8px rgba(0,0,0,0.1);
        }

        @keyframes slideInDown {
            from {
                opacity: 0;
                transform: translateY(-20px);
            }
            to {
                opacity: 1;
                transform: translateY(0);
            }
        }

        .alert-success {
            background: linear-gradient(135deg, #d4edda 0%, #c3e6cb 100%);
            color: #155724;
            border-left: 4px solid #28a745;
        }

        .alert-danger {
            background: linear-gradient(135deg, #f8d7da 0%, #f5c6cb 100%);
            color: #721c24;
            border-left: 4px solid #dc3545;
        }

        .form-label {
            color: #333 !important;
            font-weight: 600 !important;
            text-transform: uppercase;
            letter-spacing: 0.5px;
            font-size: 0.9em;
        }

        .form-control, .form-select {
            border-radius: 8px;
            border: 2px solid #ccc !important;
            transition: all 0.3s ease;
            background-color: #ffffff !important;
            color: #333 !important;
        }

        .form-control::placeholder {
            color: #999 !important;
        }

        .form-control:focus, .form-select:focus {
            border-color: #537b83 !important;
            box-shadow: 0 0 0 0.3rem rgba(83, 123, 131, 0.15) !important;
            background-color: #ffffff !important;
            color: #333 !important;
        }

        .form-select {
            color: #333 !important;
        }

        .form-select option {
            color: #333;
            background-color: white;
        }

        #editDishPrice {
            font-size: 1.2em;
            font-weight: 600;
            padding: 12px;
            text-align: center;
            letter-spacing: 1px;
        }

        #editDishPrice:focus {
            background: linear-gradient(135deg, rgba(83, 123, 131, 0.05) 0%, rgba(119, 189, 200, 0.05) 100%);
        }

        .price-input-icon {
            position: relative;
        }

        .price-input-icon::before {
            content: '€';
            position: absolute;
            left: 15px;
            top: 50%;
            transform: translateY(-50%);
            font-size: 1.2em;
            color: var(--primary);
            font-weight: 600;
            pointer-events: none;
        }

        .modal-content {
            border-radius: 10px;
        }

        .modal {
            --bs-modal-backdrop-opacity: 0.5;
        }

        .modal.show .modal-dialog {
            animation: slideInUp 0.4s cubic-bezier(0.25, 0.46, 0.45, 0.94);
        }

        @keyframes slideInUp {
            from {
                opacity: 0;
                transform: translateY(30px);
            }
            to {
                opacity: 1;
                transform: translateY(0);
            }
        }

        .modal-content {
            border: none;
            box-shadow: 0 10px 40px rgba(0,0,0,0.2);
            border-radius: 12px;
            overflow: hidden;
        }

        .modal-header {
            background: linear-gradient(135deg, var(--primary) 0%, var(--secondary) 100%);
            color: white;
            border: none;
            padding: 1.5rem;
        }

        .modal-header .modal-title {
            font-weight: 600;
            letter-spacing: 0.5px;
        }

        .modal-header .btn-close {
            filter: brightness(0) invert(1);
            transition: all 0.2s ease;
        }

        .modal-header .btn-close:hover {
            opacity: 0.8;
        }

        .modal-body {
            padding: 2rem;
        }

        .modal-footer {
            background-color: #f8f9fa;
            border-top: 1px solid #e0e0e0;
            padding: 1.5rem;
        }

        .modal-footer .btn {
            border-radius: 6px;
            padding: 0.6rem 1.5rem;
            font-weight: 600;
            transition: all 0.3s ease;
        }

        .modal-footer .btn-primary:hover {
            transform: translateY(-2px);
            box-shadow: 0 4px 12px rgba(83, 123, 131, 0.3);
        }

        .modal-footer .btn-secondary {
            background-color: #6c757d;
        }

        .modal-footer .btn-secondary:hover {
            background-color: #5a6268;
            transform: translateY(-2px);
            box-shadow: 0 4px 12px rgba(108, 117, 125, 0.3);
        }

        .loading {
            display: inline-block;
            width: 20px;
            height: 20px;
            border: 3px solid rgba(83, 123, 131, 0.3);
            border-radius: 50%;
            border-top-color: var(--primary);
            animation: spin 1s ease-in-out infinite;
        }

        @keyframes spin {
            to { transform: rotate(360deg); }
        }

        .connection-status {
            position: fixed;
            bottom: 20px;
            right: 20px;
            padding: 10px 15px;
            border-radius: 20px;
            font-size: 0.9em;
            z-index: 1000;
            box-shadow: 0 2px 8px rgba(0,0,0,0.2);
            animation: slideIn 0.3s ease-out;
        }

        .connection-status.status-online {
            background: #d4edda;
            color: #155724;
            border: 1px solid #c3e6cb;
        }

        .connection-status.status-offline {
            background: #f8d7da;
            color: #721c24;
            border: 1px solid #f5c6cb;
        }

        .queue-badge {
            position: absolute;
            top: -8px;
            right: -8px;
            background: var(--accent);
            color: white;
            border-radius: 50%;
            width: 24px;
            height: 24px;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 0.8em;
            font-weight: 700;
        }

        @keyframes slideIn {
            from {
                transform: translateX(400px);
                opacity: 0;
            }
            to {
                transform: translateX(0);
                opacity: 1;
            }
        }

        .menu-section {
            background: #f9f9f9;
            border-top: 1px solid #eee;
            padding: 15px 0;
            margin-top: 15px;
        }

        .menu-title {
            font-weight: 600;
            color: var(--primary);
            margin-bottom: 10px;
            font-size: 0.95em;
        }

        .dishes-list {
            max-height: 300px;
            overflow-y: auto;
            background: white;
            border-radius: 5px;
            padding: 10px;
        }

        .dish-item {
            background: white;
            padding: 15px;
            border-left: 4px solid var(--primary);
            margin-bottom: 10px;
            border-radius: 6px;
            display: flex;
            justify-content: space-between;
            align-items: center;
            transition: all 0.3s cubic-bezier(0.25, 0.46, 0.45, 0.94);
            box-shadow: 0 2px 6px rgba(0,0,0,0.08);
            animation: slideInLeft 0.5s ease;
        }

        .dish-item:hover {
            background: linear-gradient(135deg, #f8f9fa 0%, #f0f1f2 100%);
            box-shadow: 0 6px 16px rgba(0,0,0,0.12);
            transform: translateX(6px);
            border-left-width: 5px;
        }

        .dish-info {
            flex: 1;
            transition: all 0.3s ease;
        }

        .dish-name {
            font-weight: 600;
            color: var(--secondary);
            font-size: 0.95em;
            text-transform: uppercase;
            letter-spacing: 0.3px;
            margin-bottom: 5px;
        }

        .dish-description {
            font-size: 0.85em;
            color: #888;
            margin-top: 3px;
            font-style: italic;
        }

        @keyframes slideInLeft {
            from {
                opacity: 0;
                transform: translateX(-20px);
            }
            to {
                opacity: 1;
                transform: translateX(0);
            }
        }

        .dish-price {
            font-weight: 700;
            color: var(--accent);
            min-width: 60px;
            text-align: right;
            margin-right: 10px;
        }

        .btn-delete-dish {
            padding: 2px 8px;
            font-size: 0.85em;
            color: white;
            background: #dc3545;
            border: none;
            border-radius: 3px;
            cursor: pointer;
            transition: all 0.2s;
        }

        .btn-delete-dish:hover {
            background: #c82333;
        }

        .btn-edit-dish {
            padding: 2px 8px;
            font-size: 0.85em;
            color: white;
            background: var(--primary);
            border: none;
            border-radius: 3px;
            cursor: pointer;
            transition: all 0.2s;
        }

        .btn-edit-dish:hover {
            background: var(--secondary);
        }

        .no-dishes {
            text-align: center;
            color: #999;
            padding: 20px 10px;
            font-size: 0.9em;
        }

        .menu-action-buttons {
            display: flex;
            gap: 8px;
            flex-wrap: wrap;
            margin-top: 10px;
        }

        .menu-action-buttons .btn {
            flex: 1;
            min-width: 150px;
            font-size: 0.85em;
            padding: 6px 10px;
            border-radius: 6px;
            transition: all 0.3s cubic-bezier(0.25, 0.46, 0.45, 0.94);
            font-weight: 600;
            border: none;
        }

        .btn-sm {
            border-radius: 6px;
            transition: all 0.3s cubic-bezier(0.25, 0.46, 0.45, 0.94);
            font-weight: 600;
        }

        .btn-sm:hover {
            transform: translateY(-2px);
            box-shadow: 0 4px 12px rgba(0,0,0,0.15);
        }

        .btn-sm:active {
            transform: translateY(0);
            box-shadow: 0 2px 6px rgba(0,0,0,0.1);
        }

        .btn-success:hover {
            background-color: #218838;
        }

        .btn-info:hover {
            background-color: #0c5460;
            color: white;
        }

        .btn-primary:hover {
            background-color: #0056b3;
        }

        .btn-secondary:hover {
            background-color: #5a6268;
        }

        .btn-warning:hover {
            background-color: #e0a800;
            color: white;
        }

        .btn-danger:hover {
            background-color: #c82333;
        }

        .edit-price-btn {
            margin-left: 8px;
            padding: 4px 8px;
            font-size: 0.75em;
            transition: all 0.2s ease;
        }

        .edit-price-btn:hover {
            transform: scale(1.1);
        }
    </style>
</head>
<body>

<div class="header">
    <div class="container-lg">
        <h1><i class="fas fa-building"></i> Gestione Ristoranti <span class="queue-badge" style="display:none;">0</span></h1>
        <p>Crea e gestisci nuovi menu per i tuoi ristoranti</p>
        <div class="connection-status status-online" style="font-size: 0.85em; margin-top: 10px;">
            🟢 Online - sincronizzazione attiva
        </div>
    </div>
</div>

<div class="container-lg">

    <!-- Alert -->
    <div id="successAlert" class="alert alert-success alert-dismissible fade show d-none" role="alert">
        <i class="fas fa-check-circle"></i> <span id="successMsg"></span>
        <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
    </div>

    <div id="errorAlert" class="alert alert-danger alert-dismissible fade show d-none" role="alert">
        <i class="fas fa-exclamation-circle"></i> <span id="errorMsg"></span>
        <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
    </div>

    <!-- Statistiche -->
    <div class="row mb-4">
        <div class="col-md-6">
            <div class="stats-box">
                <h6>Ristoranti Totali</h6>
                <div class="number" id="totalRestaurants">0</div>
            </div>
        </div>
        <div class="col-md-6">
            <div class="stats-box">
                <h6>Ultimo Aggiornamento</h6>
                <div id="lastUpdate" style="font-size: 0.9em; color: #999;">Caricamento...</div>
            </div>
        </div>
    </div>

    <!-- Pulsante Aggiungi Ristorante -->
    <div class="card mb-4">
        <div class="card-body">
            <button class="btn btn-accent btn-lg" data-bs-toggle="modal" data-bs-target="#addRestaurantModal">
                <i class="fas fa-plus"></i> Crea Nuovo Ristorante
            </button>
        </div>
    </div>

    <!-- Lista Ristoranti -->
    <div class="card">
        <div class="card-header">
            <i class="fas fa-list"></i> Lista Ristoranti
        </div>
        <div class="card-body">
            <div id="restaurantsList"></div>
        </div>
    </div>

</div>

<!-- Modal Crea Ristorante -->
<div class="modal fade" id="addRestaurantModal" tabindex="-1">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title"><i class="fas fa-plus-circle"></i> Crea Nuovo Ristorante</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body">
                <form id="restaurantForm">
                    <div class="mb-3">
                        <label class="form-label">Nome Ristorante *</label>
                        <input type="text" class="form-control" id="restaurantName" placeholder="Es: Al Cantuccio" required>
                        <small class="text-muted">Slug: <code id="slugPreview">al-cantuccio</code></small>
                    </div>

                    <div class="mb-3">
                        <label class="form-label">Descrizione</label>
                        <textarea class="form-control" id="restaurantDescription" rows="3" placeholder="Descrizione del ristorante"></textarea>
                    </div>

                    <div class="row">
                        <div class="col-md-6">
                            <div class="mb-3">
                                <label class="form-label">Indirizzo</label>
                                <input type="text" class="form-control" id="restaurantAddress" placeholder="Via/Piazza, città">
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="mb-3">
                                <label class="form-label">Telefono</label>
                                <input type="tel" class="form-control" id="restaurantPhone" placeholder="+39 xxx xxx xxxx">
                            </div>
                        </div>
                    </div>

                    <div class="row">
                        <div class="col-md-6">
                            <div class="mb-3">
                                <label class="form-label">Email</label>
                                <input type="email" class="form-control" id="restaurantEmail" placeholder="email@ristorante.it">
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="mb-3">
                                <label class="form-label">Logo URL</label>
                                <input type="url" class="form-control" id="restaurantLogo" placeholder="https://...">
                            </div>
                        </div>
                    </div>

                    <div class="row">
                        <div class="col-md-6">
                            <div class="mb-3">
                                <label class="form-label">Latitudine</label>
                                <input type="number" class="form-control" id="restaurantLat" placeholder="41.9028" step="0.00001">
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="mb-3">
                                <label class="form-label">Longitudine</label>
                                <input type="number" class="form-control" id="restaurantLon" placeholder="12.4964" step="0.00001">
                            </div>
                        </div>
                    </div>

                    <div class="alert alert-info">
                        <i class="fas fa-info-circle"></i> Verrà creata una pagina menu identica a index.html in:
                        <code>/menu/<span id="slugPreview2">al-cantuccio</span>/</code>
                    </div>

                    <div class="d-grid gap-2">
                        <button type="button" class="btn btn-primary" onclick="saveRestaurant()">
                            <i class="fas fa-save"></i> Crea Ristorante
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>

<!-- Modal Conferma Eliminazione -->
<div class="modal fade" id="deleteConfirmModal" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header bg-danger text-white">
                <h5 class="modal-title"><i class="fas fa-exclamation-triangle"></i> Elimina Ristorante</h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body">
                <p>Stai per eliminare il ristorante:</p>
                <p style="font-size: 1.2em; font-weight: 600; color: var(--accent);" id="deleteRestaurantName"></p>
                <p class="text-muted">Questa azione eliminerà:</p>
                <ul>
                    <li>Tutti i piatti del menu</li>
                    <li>Tutte le configurazioni</li>
                    <li>La cartella <code id="deleteRestaurantSlug"></code></li>
                </ul>
                <p class="text-danger"><strong>⚠️ Questa azione è irreversibile!</strong></p>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Annulla</button>
                <button type="button" class="btn btn-danger" onclick="confirmDelete()">
                    <i class="fas fa-trash"></i> Sì, Elimina
                </button>
            </div>
        </div>
    </div>
</div>

<!-- Modal Aggiungi Piatto -->
<div class="modal fade" id="addDishModal" tabindex="-1">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title"><i class="fas fa-plus-circle"></i> Aggiungi Piatto a <span id="dishRestaurantName"></span></h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body">
                <form id="dishForm">
                    <div class="mb-3">
                        <label class="form-label">Categoria *</label>
                        <select class="form-select" id="dishCategory" required>
                            <option value="">-- Caricamento... --</option>
                        </select>
                    </div>

                    <div class="mb-3">
                        <label class="form-label">Nome Piatto *</label>
                        <input type="text" class="form-control" id="dishName" placeholder="Es: Spaghetti al Nero di Seppia" required>
                    </div>

                    <div class="mb-3">
                        <label class="form-label">Descrizione</label>
                        <textarea class="form-control" id="dishDescription" rows="3" placeholder="Breve descrizione del piatto"></textarea>
                    </div>

                    <div class="row">
                        <div class="col-md-6">
                            <div class="mb-3">
                                <label class="form-label">Prezzo (€) *</label>
                                <input type="number" class="form-control" id="dishPrice" placeholder="15.50" step="0.01" required>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="mb-3">
                                <label class="form-label">URL Immagine</label>
                                <input type="url" class="form-control" id="dishImage" placeholder="https://...">
                            </div>
                        </div>
                    </div>

                    <div class="mb-3">
                        <div class="form-check">
                            <input class="form-check-input" type="checkbox" id="dishAvailable" checked>
                            <label class="form-check-label" for="dishAvailable">
                                Disponibile
                            </label>
                        </div>
                    </div>

                    <div class="d-grid gap-2">
                        <button type="button" class="btn btn-success" onclick="saveDish()">
                            <i class="fas fa-save"></i> Aggiungi Piatto
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>

<!-- Modal Modifica Categoria Piatto -->
<div class="modal fade" id="editCategoryModal" tabindex="-1">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title"><i class="fas fa-edit"></i> Modifica Categoria - <span id="editDishName"></span></h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body">
                <form id="editCategoryForm">
                    <div class="mb-3">
                        <label class="form-label">Categoria *</label>
                        <select class="form-select" id="editDishCategory" required>
                            <option value="">-- Caricamento categorie... --</option>
                        </select>
                    </div>

                    <div class="d-grid gap-2">
                        <button type="button" class="btn btn-primary" onclick="saveEditCategory()">
                            <i class="fas fa-save"></i> Salva Categoria
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>

<!-- Modal Modifica Foto Piatto -->
<div class="modal fade" id="editImageModal" tabindex="-1">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title"><i class="fas fa-image"></i> Modifica Foto - <span id="editImageDishName"></span></h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body">
                <form id="editImageForm">
                    <div class="mb-3">
                        <label class="form-label">Foto Attuale</label>
                        <div id="currentImagePreview" style="margin-bottom: 15px; text-align: center;">
                            <img id="currentImage" src="" alt="Foto piatto" style="max-width: 100%; max-height: 300px; border-radius: 8px;">
                        </div>
                    </div>

                    <div class="mb-3">
                        <label class="form-label">Nuova Foto o URL *</label>
                        <div style="display: flex; gap: 10px;">
                            <div style="flex: 1;">
                                <label style="display: block; margin-bottom: 8px; font-size: 0.9em; color: #666;">
                                    <strong>Opzione 1:</strong> Carica file
                                </label>
                                <input type="file" class="form-control" id="editImageFile" accept="image/jpeg,image/png,image/webp">
                            </div>
                        </div>
                    </div>

                    <div class="mb-3">
                        <label style="display: block; margin-bottom: 8px; font-size: 0.9em; color: #666;">
                            <strong>Opzione 2:</strong> Incolla URL immagine
                        </label>
                        <input type="url" class="form-control" id="editImageUrl" placeholder="https://...">
                    </div>

                    <div id="editImagePreview" style="display: none; margin-bottom: 15px; text-align: center;">
                        <label style="display: block; margin-bottom: 8px; font-size: 0.9em; color: #666;">
                            <strong>Anteprima nuova foto:</strong>
                        </label>
                        <img id="editPreviewImg" src="" alt="Anteprima" style="max-width: 100%; max-height: 300px; border-radius: 8px;">
                    </div>

                    <div class="d-grid gap-2">
                        <button type="button" class="btn btn-primary" onclick="saveEditImage()">
                            <i class="fas fa-save"></i> Salva Foto
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>

<!-- Modal Modifica Piatto (Unificato) -->
<div class="modal fade" id="editDishModal" tabindex="-1">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title"><i class="fas fa-edit"></i> Modifica Piatto - <span id="editDishModalName"></span></h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body">
                <form id="editDishCompleteForm">
                    <!-- Nome Piatto -->
                    <div class="mb-3">
                        <label class="form-label"><i class="fas fa-utensils"></i> Nome Piatto *</label>
                        <div class="input-group">
                            <input type="text" class="form-control" id="editDishName" placeholder="es. Margherita" required>
                            <button type="button" class="btn btn-info" title="Estrai dal menu con OCR" onclick="runOCRForDish()">
                                <i class="fas fa-camera"></i> OCR
                            </button>
                        </div>
                    </div>

                    <!-- Descrizione -->
                    <div class="mb-3">
                        <label class="form-label"><i class="fas fa-align-left"></i> Descrizione</label>
                        <textarea class="form-control" id="editDishDescription" rows="3" placeholder="Descrizione del piatto..."></textarea>
                    </div>

                    <!-- Categoria -->
                    <div class="mb-3">
                        <label class="form-label"><i class="fas fa-list"></i> Categoria *</label>
                        <select class="form-select" id="editDishCategorySelect" required>
                            <option value="">-- Caricamento categorie... --</option>
                        </select>
                    </div>

                    <!-- Prezzo -->
                    <div class="mb-3">
                        <label class="form-label"><i class="fas fa-euro-sign"></i> Prezzo (€) *</label>
                        <input type="number" class="form-control" id="editDishPriceInput" placeholder="15.50" step="0.01" min="0" required>
                    </div>

                    <!-- Disponibilità -->
                    <div class="mb-3">
                        <div class="form-check form-switch">
                            <input class="form-check-input" type="checkbox" id="editDishAvailable" checked>
                            <label class="form-check-label" for="editDishAvailable">
                                Piatto disponibile
                            </label>
                        </div>
                    </div>

                    <div class="d-grid gap-2">
                        <button type="button" class="btn btn-primary" onclick="saveEditDishComplete()">
                            <i class="fas fa-save"></i> Salva Modifiche
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
        <script src="./core/restaurant-manager.js?v=4"></script>
        <script src="./core/sync-manager.js?v=5"></script><script>
    let restaurantManager;
    let syncManager;
    let currentDeleteId = null;

    // Inizializza
    window.addEventListener('load', async () => {
        restaurantManager = new RestaurantManager('./api.php');
        syncManager = new SyncManager();
        
        // Inizializza sincronizzazione
        await syncManager.init();
        
        // Ascolta aggiornamenti dal sync manager
        syncManager.addListener(handleSyncEvent);
        
        await loadRestaurants();
        setupEventListeners();
    });

    /**
     * Gestisci eventi di sincronizzazione
     */
    function handleSyncEvent(event) {
        console.log('🔄 Evento sync:', event);
        
        switch(event.type) {
            case 'data-updated':
                if (event.dataType === 'restaurants') {
                    // Aggiorna visualizzazione con nuovi dati
                    displayRestaurants(event.data);
                    showInfo(`📥 Dati aggiornati: ${event.data.length} ristoranti`);
                }
                break;
                
            case 'operation-completed':
                showSuccess(`✅ ${event.operation} completato`);
                loadRestaurants();
                break;
                
            case 'operation-queued':
                showInfo(`📋 Operazione in coda (${event.queueSize})`);
                updateQueueStatus();
                break;
                
            case 'connection-status':
                updateConnectionStatus(event.status, event.message);
                break;
                
            case 'sync-error':
                showError(`❌ Errore sync: ${event.error}`);
                break;
        }
    }

    /**
     * Aggiorna stato della coda visivamente
     */
    function updateQueueStatus() {
        const status = syncManager.getQueueStatus();
        const badge = document.querySelector('.queue-badge');
        
        if (badge) {
            if (status.queueSize > 0) {
                badge.textContent = status.queueSize;
                badge.style.display = 'inline';
            } else {
                badge.style.display = 'none';
            }
        }
    }

    /**
     * Aggiorna stato della connessione
     */
    function updateConnectionStatus(status, message) {
        const statusEl = document.querySelector('.connection-status');
        
        if (statusEl) {
            statusEl.textContent = message;
            statusEl.className = 'connection-status';
            statusEl.classList.add(status === 'offline' ? 'status-offline' : 'status-online');
        }
    }

    /**
     * Carica e visualizza tutti i ristoranti
     */
    async function loadRestaurants() {
        try {
            // Carica dal syncManager (usa cache se disponibile)
            const restaurants = await restaurantManager.getRestaurants();
            displayRestaurants(restaurants);
            
            // Forza sincronizzazione dal server
            await syncManager.syncInbound();
        } catch (e) {
            console.error('Errore caricamento:', e);
            showError('Errore nel caricamento dei ristoranti');
        }
    }

    /**
     * Visualizza ristoranti nella UI
     */
    function displayRestaurants(restaurants) {
        document.getElementById('totalRestaurants').textContent = restaurants.length;
        document.getElementById('lastUpdate').textContent = new Date().toLocaleTimeString('it-IT');

        const container = document.getElementById('restaurantsList');
        
        if (restaurants.length === 0) {
            container.innerHTML = `
                <div class="empty-state">
                    <i class="fas fa-inbox"></i>
                    <h5>Nessun ristorante</h5>
                    <p>Crea il tuo primo ristorante per iniziare</p>
                </div>
            `;
            return;
        }

        container.innerHTML = restaurants.map(r => `
            <div class="restaurant-item" data-id="${r.id}" data-slug="${r.slug}">
                <div class="restaurant-header" onclick="toggleRestaurant(this)">
                    <div class="restaurant-toggle collapsed"><i class="fas fa-chevron-down"></i></div>
                    <div class="restaurant-name" style="margin: 0; flex: 1;">
                        <i class="fas fa-utensils"></i> ${r.name}
                    </div>
                </div>
                <div class="restaurant-content collapsed">
                    <div class="restaurant-slug">
                        ${r.slug}
                    </div>
                    <div class="restaurant-info">
                        ${r.address ? `<div><i class="fas fa-map-marker-alt"></i> ${r.address}</div>` : ''}
                        ${r.phone ? `<div><i class="fas fa-phone"></i> ${r.phone}</div>` : ''}
                        ${r.email ? `<div><i class="fas fa-envelope"></i> ${r.email}</div>` : ''}
                    </div>
                    <div class="restaurant-info" style="font-size: 0.85em; color: #999;">
                        Creato: ${new Date(r.created_at).toLocaleDateString('it-IT')}
                    </div>
                    <div class="menu-section">
                        <div class="menu-title"><i class="fas fa-list"></i> Menu Piatti</div>
                        <div class="dishes-list" id="dishes-${r.id}">
                            <div class="no-dishes">Caricamento...</div>
                        </div>
                        <div class="menu-action-buttons">
                            <button class="btn btn-sm btn-success" onclick="showAddDishModal('${r.id}', '${r.name}')">
                                <i class="fas fa-plus-circle"></i> Aggiungi Piatto
                            </button>
                            <button class="btn btn-sm btn-info" onclick="reloadMenu('${r.id}')">
                                <i class="fas fa-sync-alt"></i> Ricarica
                            </button>
                        </div>
                    </div>
                    <div class="restaurant-actions">
                        <button class="btn btn-sm btn-primary" onclick="openRestaurantPage('${r.slug}')">
                            <i class="fas fa-external-link-alt"></i> Apri Menu
                        </button>
                        <button class="btn btn-sm btn-secondary" onclick="editRestaurant('${r.id}')">
                            <i class="fas fa-edit"></i> Modifica
                        </button>
                        <button class="btn btn-sm btn-warning" onclick="exportRestaurant('${r.id}')">
                            <i class="fas fa-download"></i> Esporta
                        </button>
                        <button class="btn btn-sm btn-danger" onclick="showDeleteConfirm('${r.id}', '${r.name}', '${r.slug}')">
                            <i class="fas fa-trash"></i> Elimina
                        </button>
                    </div>
                </div>
            </div>
        `).join('');

        // Carica i piatti per ogni ristorante
        restaurants.forEach(r => loadMenuForRestaurant(r.id));
    }

    /**
     * Setup event listener per il form
     */
    function setupEventListeners() {
        const nameInput = document.getElementById('restaurantName');
        nameInput.addEventListener('input', (e) => {
            const slug = restaurantManager.generateSlug(e.target.value);
            document.getElementById('slugPreview').textContent = slug;
            document.getElementById('slugPreview2').textContent = slug;
        });
    }

    /**
     * Salva un nuovo ristorante
     */
    async function saveRestaurant() {
        const name = document.getElementById('restaurantName').value;
        const description = document.getElementById('restaurantDescription').value;
        const address = document.getElementById('restaurantAddress').value;
        const phone = document.getElementById('restaurantPhone').value;
        const email = document.getElementById('restaurantEmail').value;
        const logo = document.getElementById('restaurantLogo').value;
        const lat = document.getElementById('restaurantLat').value;
        const lon = document.getElementById('restaurantLon').value;

        if (!name.trim()) {
            showError('Nome ristorante obbligatorio');
            return;
        }

        const data = {
            name,
            description,
            address,
            phone,
            email,
            logo_url: logo,
            latitude: lat ? parseFloat(lat) : null,
            longitude: lon ? parseFloat(lon) : null
        };

        // Accoda l'operazione nel sync manager
        syncManager.queueOperation('create-restaurant', data);
        
        // Mostra messaggio
        showInfo('🔄 Ristorante in corso di creazione...');
        
        // Chiudi modal e resetta form
        bootstrap.Modal.getInstance(document.getElementById('addRestaurantModal')).hide();
        document.getElementById('restaurantForm').reset();
        
        // Aggiorna UI dopo breve delay
        setTimeout(() => loadRestaurants(), 1000);
    }

    /**
     * Mostra conferma eliminazione
     */
    function showDeleteConfirm(id, name, slug) {
        currentDeleteId = id;
        document.getElementById('deleteRestaurantName').textContent = name;
        document.getElementById('deleteRestaurantSlug').textContent = slug;
        const modal = new bootstrap.Modal(document.getElementById('deleteConfirmModal'));
        modal.show();
    }

    /**
     * Conferma e elimina
     */
    async function confirmDelete() {
        if (!currentDeleteId) return;

        // Accoda l'operazione
        syncManager.queueOperation('delete-restaurant', {}, currentDeleteId);
        
        showInfo('🔄 Eliminazione in corso...');
        bootstrap.Modal.getInstance(document.getElementById('deleteConfirmModal')).hide();
        
        // Aggiorna UI dopo breve delay
        setTimeout(() => loadRestaurants(), 1000);
        
        currentDeleteId = null;
    }

    /**
     * Apri pagina menu
     */
    function openRestaurantPage(slug) {
        const url = restaurantManager.getRestaurantPageUrl(slug);
        window.open(url, '_blank');
    }

    /**
     * Modifica ristorante
     */
    function editRestaurant(id) {
        window.location.href = `./edit-restaurant.html?id=${id}`;
    }

    /**
     * Esporta ristorante
     */
    async function exportRestaurant(id) {
        const success = await restaurantManager.exportRestaurant(id);
        if (success) {
            showSuccess('Menu esportato');
        } else {
            showError('Errore nell\'export');
        }
    }

    /**
     * Toggle collapsible restaurant card
     */
    function toggleRestaurant(header) {
        const item = header.closest('.restaurant-item');
        const content = item.querySelector('.restaurant-content');
        const toggle = header.querySelector('.restaurant-toggle');
        
        content.classList.toggle('collapsed');
        toggle.classList.toggle('collapsed');
    }

    /**
     * Mostra messaggio di successo
     */
    function showSuccess(msg) {
        const alert = document.getElementById('successAlert');
        document.getElementById('successMsg').textContent = msg;
        alert.classList.remove('d-none');
        setTimeout(() => alert.classList.add('d-none'), 4000);
    }

    /**
     * Mostra messaggio di errore
     */
    function showError(msg) {
        const alert = document.getElementById('errorAlert');
        document.getElementById('errorMsg').textContent = msg;
        alert.classList.remove('d-none');
        setTimeout(() => alert.classList.add('d-none'), 4000);
    }

    /**
     * Mostra messaggio informativo
     */
    function showInfo(msg) {
        // Riusa l'alert di successo per messaggi info
        const alert = document.querySelector('[id$="Alert"]') || document.getElementById('successAlert');
        if (alert) {
            const msgEl = alert.querySelector('[id$="Msg"]') || document.getElementById('successMsg');
            if (msgEl) msgEl.textContent = msg;
            alert.classList.remove('d-none');
        }
    }

    /**
     * Carica il menu (piatti) di un ristorante
     */
    async function loadMenuForRestaurant(restaurantId) {
        console.log('🔍 Caricamento menu per:', restaurantId);
        const dishes = await restaurantManager.getMenu(restaurantId);
        console.log('📋 Piatti caricati:', dishes);
        displayMenuDishes(restaurantId, dishes);
    }

    /**
     * Visualizza piatti nel menu
     */
    function displayMenuDishes(restaurantId, dishes) {
        const container = document.getElementById(`dishes-${restaurantId}`);
        console.log('🎯 Container trovato:', !!container, 'ID:', `dishes-${restaurantId}`);
        console.log('📊 Piatti da visualizzare:', dishes);
        
        if (!container) return;
        
        if (dishes.length === 0) {
            container.innerHTML = '<div class="no-dishes">Nessun piatto nel menu</div>';
            return;
        }

        // Raggruppa per categoria
        const grouped = restaurantManager.groupDishesByCategory(dishes);
        let html = '';
        
        for (const [category, items] of Object.entries(grouped)) {
            html += `<div style="margin-bottom: 15px;">
                    <div style="font-weight: 600; color: var(--primary); margin-bottom: 8px; font-size: 0.9em;">
                        ${category}
                    </div>`;
            
            items.forEach(dish => {
                // Parse allergeni se è una stringa JSON
                let allergens = [];
                if (dish.allergens) {
                    try {
                        allergens = typeof dish.allergens === 'string' ? JSON.parse(dish.allergens) : dish.allergens;
                    } catch(e) {
                        allergens = [];
                    }
                }
                
                const allergensHtml = allergens.length > 0 
                    ? `<div style="margin-top: 4px; font-size: 0.8em; color: #d32f2f;">
                        ⚠️ ${allergens.join(', ')}
                       </div>`
                    : '';
                
                html += `<div class="dish-item">
                        <div class="dish-info">
                            <div class="dish-name">${dish.name}</div>
                            ${dish.description ? `<div class="dish-description">${dish.description}</div>` : ''}
                            ${allergensHtml}
                        </div>
                        <div class="dish-price">€${parseFloat(dish.price).toFixed(2)}</div>
                        <div style="display: flex; gap: 8px; flex-wrap: wrap;">
                            <button class="btn-edit-dish" title="Modifica piatto completo" onclick="openEditDishModal('${dish.id}', '${dish.name}', '${dish.description || ''}', '${dish.category_id || ''}', '${dish.price}', ${dish.available || 1}, '${restaurantId}')">
                                <i class="fas fa-edit"></i>
                            </button>
                            <button class="btn-edit-dish" title="Modifica foto" onclick="showEditImageModal('${dish.id}', '${dish.name}', '${dish.image_url || ''}')">
                                <i class="fas fa-image"></i>
                            </button>
                            <button class="btn-delete-dish" title="Elimina piatto" onclick="deleteDishConfirm('${dish.id}', '${dish.name}')">
                                <i class="fas fa-trash"></i>
                            </button>
                        </div>
                    </div>`;
            });
            
            html += '</div>';
        }
        
        container.innerHTML = html;
    }

    /**
     * Mostra modal per aggiungere piatto
     */
    let currentRestaurantIdForDish = null;
    
    async function showAddDishModal(restaurantId, restaurantName) {
        currentRestaurantIdForDish = restaurantId;
        document.getElementById('dishRestaurantName').textContent = restaurantName;
        document.getElementById('dishForm').reset();
        
        // Carica categorie dal database
        const categorySelect = document.getElementById('dishCategory');
        categorySelect.innerHTML = '<option value="">-- Caricamento categorie... --</option>';
        
        try {
            const categories = await restaurantManager.getCategories(restaurantId);
            
            if (categories && categories.length > 0) {
                categorySelect.innerHTML = '<option value="">-- Seleziona categoria --</option>';
                
                categories.forEach(cat => {
                    const option = document.createElement('option');
                    option.value = cat.id;  // Usa l'ID della categoria dal database
                    option.textContent = cat.name;
                    option.dataset.categoryId = cat.id;
                    categorySelect.appendChild(option);
                });
                
                // Aggiungi opzione per creare nuova categoria se necessario
                const divider = document.createElement('option');
                divider.disabled = true;
                categorySelect.appendChild(divider);
                
                const newCat = document.createElement('option');
                newCat.value = 'NEW';
                newCat.textContent = '➕ Crea nuova categoria';
                categorySelect.appendChild(newCat);
            } else {
                categorySelect.innerHTML = '<option value="">Nessuna categoria disponibile</option>';
            }
        } catch (error) {
            console.error('Errore caricamento categorie:', error);
            categorySelect.innerHTML = '<option value="">Errore caricamento categorie</option>';
        }
        
        const modal = new bootstrap.Modal(document.getElementById('addDishModal'));
        modal.show();
    }

    /**
     * Salva un nuovo piatto
     */
    async function saveDish() {
        console.log('🔍 saveDish() chiamato');
        console.log('📋 currentRestaurantIdForDish:', currentRestaurantIdForDish);
        
        if (!currentRestaurantIdForDish) {
            console.error('❌ Errore: ristorante non selezionato');
            showError('Errore: ristorante non selezionato');
            return;
        }

        const categoryValue = document.getElementById('dishCategory').value;
        const name = document.getElementById('dishName').value;
        const description = document.getElementById('dishDescription').value;
        const price = document.getElementById('dishPrice').value;
        const image = document.getElementById('dishImage').value;
        const available = document.getElementById('dishAvailable').checked ? 1 : 0;

        // Se l'utente sceglie "Crea nuova categoria"
        if (categoryValue === 'NEW') {
            const newCategoryName = prompt('Inserisci il nome della nuova categoria:');
            if (!newCategoryName) {
                showError('Creazione categoria annullata');
                return;
            }
            
            try {
                const result = await restaurantManager.addCategory(currentRestaurantIdForDish, {
                    name: newCategoryName,
                    display_order: 0
                });
                
                if (result && result.id) {
                    // Ricaricare le categorie nel dropdown
                    await showAddDishModal(currentRestaurantIdForDish, document.getElementById('dishRestaurantName').textContent);
                    showSuccess(`Categoria '${newCategoryName}' creata`);
                    return;
                } else {
                    showError('Errore nella creazione della categoria');
                    return;
                }
            } catch (error) {
                console.error('Errore creazione categoria:', error);
                showError('Errore nella creazione della categoria');
                return;
            }
        }

        console.log('📝 Dati form:', {categoryValue, name, price, description, image});

        if (!categoryValue || !name || !price) {
            console.error('❌ Errore: campi obbligatori mancanti');
            showError('Compilare i campi obbligatori');
            return;
        }

        const dishData = {
            category_id: categoryValue,  // Usa l'ID della categoria dal database
            name,
            description,
            price: parseFloat(price),
            image_url: image,
            available
        };

        console.log('📤 Invio dati al server:', {restaurantId: currentRestaurantIdForDish, ...dishData});

        const result = await restaurantManager.addDish(currentRestaurantIdForDish, dishData);
        
        console.log('📥 Risposta server:', result);
        
        if (result && result.success) {
            console.log('✅ Piatto aggiunto con successo');
            showSuccess(`✅ Piatto '${name}' aggiunto con successo`);
            bootstrap.Modal.getInstance(document.getElementById('addDishModal')).hide();
            await loadMenuForRestaurant(currentRestaurantIdForDish);
        } else {
            console.error('❌ Errore aggiunta piatto:', result);
            showError('Errore nell\'aggiunta del piatto');
        }
    }

    /**
     * Conferma eliminazione piatto
     */
    function deleteDishConfirm(dishId, dishName) {
        if (confirm(`Vuoi eliminare il piatto "${dishName}"?`)) {
            deleteDish(dishId);
        }
    }

    /**
     * Elimina un piatto
     */
    async function deleteDish(dishId) {
        const success = await restaurantManager.deleteDish(dishId);
        
        if (success) {
            showSuccess('Piatto eliminato');
            // Ricarica menu corrente
            if (currentRestaurantIdForDish) {
                await loadMenuForRestaurant(currentRestaurantIdForDish);
            }
        } else {
            showError('Errore nell\'eliminazione del piatto');
        }
    }

    /**
     * Mostra modal per modificare categoria del piatto
     */
    let currentEditDishId = null;
    let currentEditRestaurantId = null;
    
    async function showEditCategoryModal(dishId, dishName, restaurantId, currentCategoryId) {
        currentEditDishId = dishId;
        currentEditRestaurantId = restaurantId;
        
        document.getElementById('editDishName').textContent = dishName;
        
        // Carica categorie dal database
        const categorySelect = document.getElementById('editDishCategory');
        categorySelect.innerHTML = '<option value="">-- Caricamento categorie... --</option>';
        
        try {
            const categories = await restaurantManager.getCategories(restaurantId);
            
            if (categories && categories.length > 0) {
                categorySelect.innerHTML = '<option value="">-- Seleziona categoria --</option>';
                
                categories.forEach(cat => {
                    const option = document.createElement('option');
                    option.value = cat.id;
                    option.textContent = cat.name;
                    if (cat.id === currentCategoryId) {
                        option.selected = true;
                    }
                    categorySelect.appendChild(option);
                });
            } else {
                categorySelect.innerHTML = '<option value="">Nessuna categoria disponibile</option>';
            }
        } catch (error) {
            console.error('Errore caricamento categorie:', error);
            categorySelect.innerHTML = '<option value="">Errore caricamento categorie</option>';
        }
        
        const modal = new bootstrap.Modal(document.getElementById('editCategoryModal'));
        modal.show();
    }

    /**
     * Salva la categoria modificata del piatto
     */
    async function saveEditCategory() {
        if (!currentEditDishId) {
            showError('Errore: piatto non selezionato');
            return;
        }

        const categoryId = document.getElementById('editDishCategory').value;

        if (!categoryId) {
            showError('Seleziona una categoria');
            return;
        }

        try {
            // Chiama l'endpoint update-dish per aggiornare la categoria
            const response = await fetch('./api.php?action=update-dish', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json'
                },
                body: JSON.stringify({
                    id: currentEditDishId,
                    category_id: categoryId
                })
            });

            const result = await response.json();

            if (result.success) {
                showSuccess('✅ Categoria aggiornata con successo');
                bootstrap.Modal.getInstance(document.getElementById('editCategoryModal')).hide();
                
                // Ricarica menu
                if (currentEditRestaurantId) {
                    await loadMenuForRestaurant(currentEditRestaurantId);
                }
            } else {
                showError('Errore nell\'aggiornamento della categoria: ' + (result.error || 'Errore sconosciuto'));
            }
        } catch (error) {
            console.error('Errore:', error);
            showError('Errore nella comunicazione con il server');
        }
    }

    /**
     * Mostra modal per modificare foto del piatto
     */
    let currentEditImageDishId = null;
    
    async function showEditImageModal(dishId, dishName, currentImageUrl) {
        currentEditImageDishId = dishId;
        
        document.getElementById('editImageDishName').textContent = dishName;
        document.getElementById('editImageFile').value = '';
        document.getElementById('editImageUrl').value = '';
        document.getElementById('editImagePreview').style.display = 'none';
        
        // Mostra foto attuale se disponibile
        if (currentImageUrl) {
            document.getElementById('currentImage').src = currentImageUrl;
            document.getElementById('currentImagePreview').style.display = 'block';
        } else {
            document.getElementById('currentImagePreview').style.display = 'none';
        }
        
        const modal = new bootstrap.Modal(document.getElementById('editImageModal'));
        modal.show();
    }

    /**
     * Anteprima foto da file
     */
    document.addEventListener('DOMContentLoaded', function() {
        const fileInput = document.getElementById('editImageFile');
        if (fileInput) {
            fileInput.addEventListener('change', function(e) {
                const file = e.target.files[0];
                if (file && file.type.startsWith('image/')) {
                    const reader = new FileReader();
                    reader.onload = function(event) {
                        document.getElementById('editPreviewImg').src = event.target.result;
                        document.getElementById('editImagePreview').style.display = 'block';
                        // Pulisci URL se è stato cambiato il file
                        document.getElementById('editImageUrl').value = '';
                    };
                    reader.readAsDataURL(file);
                } else {
                    alert('❌ Seleziona una immagine valida');
                }
            });
        }
        
        // Preview da URL
        const urlInput = document.getElementById('editImageUrl');
        if (urlInput) {
            urlInput.addEventListener('change', function(e) {
                const url = e.target.value.trim();
                if (url) {
                    document.getElementById('editPreviewImg').src = url;
                    document.getElementById('editImagePreview').style.display = 'block';
                    // Pulisci file se è stato cambiato l'URL
                    document.getElementById('editImageFile').value = '';
                }
            });
        }
    });

    /**
     * Salva la foto modificata del piatto
     */
    async function saveEditImage() {
        if (!currentEditImageDishId) {
            showError('Errore: piatto non selezionato');
            return;
        }

        const fileInput = document.getElementById('editImageFile');
        const urlInput = document.getElementById('editImageUrl');
        
        // Verifica che almeno uno dei due sia compilato
        if ((!fileInput.files || fileInput.files.length === 0) && !urlInput.value.trim()) {
            showError('Carica una foto o incolla un URL');
            return;
        }

        let newImageUrl = null;

        try {
            // Se è stato selezionato un file, caricalo
            if (fileInput.files && fileInput.files.length > 0) {
                const file = fileInput.files[0];
                
                // Validazione file
                if (!file.type.startsWith('image/')) {
                    showError('Il file deve essere una immagine');
                    return;
                }
                
                if (file.size > 5 * 1024 * 1024) {
                    showError('La foto non deve superare 5MB');
                    return;
                }

                // Carica il file
                const formData = new FormData();
                formData.append('file', file);

                const uploadResponse = await fetch('./api.php?action=upload-image', {
                    method: 'POST',
                    body: formData
                });

                const uploadData = await uploadResponse.json();

                if (!uploadData.success) {
                    showError('Errore nel caricamento della foto: ' + (uploadData.error || 'Errore sconosciuto'));
                    return;
                }

                newImageUrl = uploadData.url;
            } else {
                // Usa l'URL fornito
                newImageUrl = urlInput.value.trim();
                if (!newImageUrl.startsWith('http')) {
                    showError('URL non valido');
                    return;
                }
            }

            // Aggiorna il piatto con la nuova foto
            const response = await fetch('./api.php?action=update-dish', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json'
                },
                body: JSON.stringify({
                    id: currentEditImageDishId,
                    image_url: newImageUrl
                })
            });

            const result = await response.json();

            if (result.success) {
                showSuccess('✅ Foto aggiornata con successo');
                bootstrap.Modal.getInstance(document.getElementById('editImageModal')).hide();
                
                // Ricarica il menu corrente per mostrare la nuova foto
                if (currentRestaurantIdForDish) {
                    await loadMenuForRestaurant(currentRestaurantIdForDish);
                }
            } else {
                showError('Errore nell\'aggiornamento della foto: ' + (result.error || 'Errore sconosciuto'));
            }
        } catch (error) {
            console.error('Errore:', error);
            showError('Errore nella comunicazione con il server');
        }
    }

    /**
     * Ricarica menu di un ristorante
     */
    async function reloadMenu(restaurantId) {
        showInfo('🔄 Ricaricamento menu...');
        await loadMenuForRestaurant(restaurantId);
        showSuccess('Menu ricaricato');
    }

    /**
     * Ricarica menu di un ristorante
     */
    async function reloadMenu(restaurantId) {
        showInfo('🔄 Ricaricamento menu...');
        await loadMenuForRestaurant(restaurantId);
        showSuccess('Menu ricaricato');
    }

    /**
     * Inserisce il menu di esempio per Gennaro
     */
    async function insertExampleMenuGennaro() {
        if (!confirm('Vuoi caricare il menu di esempio per il ristorante Gennaro?\n\nVerranno aggiunti 8 piatti di esempio.')) {
            return;
        }

        showInfo('🔄 Caricamento menu Gennaro in corso...');
        
        const result = await restaurantManager.insertExampleMenuGennaro();
        
        if (result && result.success) {
            showSuccess(`✅ Menu inserito: ${result.dishes_inserted} piatti aggiunti a Gennaro`);
            // Ricarica lista ristoranti
            setTimeout(() => loadRestaurants(), 1000);
        } else {
            showError(`❌ Errore: ${result?.error || 'Errore sconosciuto'}`);
        }
    }

    /**
     * Esegue OCR sul menu corrente del ristorante
     */
    async function runOCRForDish() {
        const restaurantId = currentEditDishData.restaurantId;
        if (!restaurantId) {
            showError('Errore: ristorante non selezionato');
            return;
        }

        // Mostra dialog per caricare immagine
        const fileInput = document.createElement('input');
        fileInput.type = 'file';
        fileInput.accept = 'image/*';
        fileInput.onchange = async (e) => {
            const file = e.target.files[0];
            if (!file) return;

            // Validazione file
            if (!file.type.startsWith('image/')) {
                showError('Carica un file immagine valido');
                return;
            }

            if (file.size > 10 * 1024 * 1024) {
                showError('Immagine troppo grande (max 10MB)');
                return;
            }

            // Mostra loading
            showInfo('📸 Elaborazione immagine con OCR...');

            try {
                // Leggi il file come base64
                const reader = new FileReader();
                reader.onload = async (e) => {
                    const imageData = e.target.result;
                    
                    // Invia al server per OCR
                    const response = await fetch('./api.php?action=run-ocr', {
                        method: 'POST',
                        headers: {
                            'Content-Type': 'application/json'
                        },
                        body: JSON.stringify({
                            image: imageData,
                            restaurant_id: restaurantId
                        })
                    });

                    const result = await response.json();

                    if (result.success && result.extracted_text) {
                        // Estrai nome e descrizione dal testo OCR
                        const text = result.extracted_text;
                        
                        // Semplice parsing: prima riga = nome, resto = descrizione
                        const lines = text.split('\n').filter(l => l.trim());
                        
                        if (lines.length > 0) {
                            document.getElementById('editDishName').value = lines[0].trim();
                            
                            if (lines.length > 1) {
                                document.getElementById('editDishDescription').value = lines.slice(1).join(' ').trim();
                            }
                            
                            showSuccess('✅ OCR completato! Completa il modulo.');
                        } else {
                            showError('❌ Nessun testo trovato nell\'immagine');
                        }
                    } else {
                        showError('❌ Errore OCR: ' + (result.error || 'Errore sconosciuto'));
                    }
                };
                
                reader.readAsDataURL(file);
            } catch (error) {
                console.error('Errore OCR:', error);
                showError('Errore nella comunicazione con il server');
            }
        };

        fileInput.click();
    }

    /**
     * Apre il modal unificato per modificare un piatto
     */
    let currentEditDishData = {
        dishId: null,
        restaurantId: null
    };

    async function openEditDishModal(dishId, dishName, dishDescription, categoryId, price, available, restaurantId) {
        currentEditDishData.dishId = dishId;
        currentEditDishData.restaurantId = restaurantId;
        
        // Popola campi
        document.getElementById('editDishModalName').textContent = dishName;
        document.getElementById('editDishName').value = dishName;
        document.getElementById('editDishDescription').value = dishDescription;
        document.getElementById('editDishPriceInput').value = price;
        document.getElementById('editDishAvailable').checked = available == 1 || available == true;
        
        // Carica categorie
        const categorySelect = document.getElementById('editDishCategorySelect');
        categorySelect.innerHTML = '<option value="">-- Caricamento categorie... --</option>';
        
        try {
            const categories = await restaurantManager.getCategories(restaurantId);
            
            if (categories && categories.length > 0) {
                categorySelect.innerHTML = '<option value="">-- Seleziona categoria --</option>';
                
                categories.forEach(cat => {
                    const option = document.createElement('option');
                    option.value = cat.id;
                    option.textContent = cat.name;
                    if (cat.id == categoryId) option.selected = true;
                    categorySelect.appendChild(option);
                });
            } else {
                categorySelect.innerHTML = '<option value="">Nessuna categoria disponibile</option>';
            }
        } catch (error) {
            console.error('Errore caricamento categorie:', error);
            categorySelect.innerHTML = '<option value="">Errore caricamento categorie</option>';
        }
        
        const modal = new bootstrap.Modal(document.getElementById('editDishModal'));
        modal.show();
    }

    /**
     * Salva tutte le modifiche al piatto
     */
    async function saveEditDishComplete() {
        const dishId = currentEditDishData.dishId;
        const restaurantId = currentEditDishData.restaurantId;
        
        if (!dishId || !restaurantId) {
            showError('Errore: piatto non selezionato');
            return;
        }

        const name = document.getElementById('editDishName').value.trim();
        const description = document.getElementById('editDishDescription').value.trim();
        const categoryId = document.getElementById('editDishCategorySelect').value;
        const price = document.getElementById('editDishPriceInput').value;
        const available = document.getElementById('editDishAvailable').checked ? 1 : 0;

        // Validazioni
        if (!name) {
            showError('Nome piatto obbligatorio');
            return;
        }

        if (!categoryId) {
            showError('Seleziona una categoria');
            return;
        }

        if (!price || parseFloat(price) < 0) {
            showError('Prezzo non valido');
            return;
        }

        try {
            const response = await fetch('./api.php?action=update-dish', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json'
                },
                body: JSON.stringify({
                    id: dishId,
                    name: name,
                    description: description,
                    category_id: categoryId,
                    price: parseFloat(price),
                    available: available
                })
            });

            const result = await response.json();

            if (result.success) {
                showSuccess('✅ Piatto modificato con successo');
                bootstrap.Modal.getInstance(document.getElementById('editDishModal')).hide();
                
                // Ricarica menu
                if (restaurantId) {
                    await loadMenuForRestaurant(restaurantId);
                }
            } else {
                showError('Errore: ' + (result.error || 'Errore sconosciuto'));
            }
        } catch (error) {
            console.error('Errore:', error);
            showError('Errore nella comunicazione con il server');
        }
    }

    /**
     * Attiva/Disattiva la sincronizzazione online
     */
    function toggleSync() {
        if (!syncManager) return;
        
        const isSyncActive = syncManager.isSyncActive === false ? true : false;
        syncManager.isSyncActive = !syncManager.isSyncActive;
        
        const btn = document.getElementById('syncToggleBtn');
        const status = document.getElementById('syncStatus');
        
        if (syncManager.isSyncActive) {
            btn.classList.remove('btn-danger');
            btn.classList.add('btn-success');
            status.textContent = 'ATTIVA';
            console.log('✅ Sincronizzazione ATTIVATA');
            // Avvia sincronizzazione
            if (syncManager.startAutoSync) {
                syncManager.startAutoSync();
            }
            showSuccess('✅ Sincronizzazione attivata');
        } else {
            btn.classList.remove('btn-success');
            btn.classList.add('btn-danger');
            status.textContent = 'DISATTIVATA';
            console.log('❌ Sincronizzazione DISATTIVATA');
            // Ferma sincronizzazione
            if (syncManager.stopAutoSync) {
                syncManager.stopAutoSync();
            }
            showInfo('ℹ️ Sincronizzazione disattivata');
        }
    }
</script>

</body>
</html>
