<?php require_once 'auth-check.php'; ?>
<!DOCTYPE html>
<html lang="it">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Importa Menu da Foto | trovapiatto Admin</title>
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/css/bootstrap.min.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link rel="preconnect" href="https://cdnjs.cloudflare.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Roboto:wght@400;500;700&display=swap" rel="stylesheet">
    <style>
        body { background: linear-gradient(135deg, #f5f7fa 0%, #c3cfe2 100%) !important; font-family: 'Roboto', sans-serif !important; color: #333 !important; min-height: 100vh !important; }
        .form-control, .form-select { background-color: #ffffff !important; color: #333 !important; border-color: #ccc !important; }
        .form-control:focus, .form-select:focus { background-color: #ffffff !important; color: #333 !important; border-color: #537b83 !important; }
        .navbar { background: linear-gradient(135deg, #EF3D26 0%, #537B83 100%); }
        .card { border: none; box-shadow: 0 2px 8px rgba(0,0,0,0.1); border-radius: 10px; }
        .btn-primary { background-color: #EF3D26; border: none; }
        .btn-primary:hover { background-color: #d63219; }
        .upload-area { border: 2px dashed #EF3D26; border-radius: 10px; padding: 40px; text-align: center; cursor: pointer; transition: all 0.3s ease; }
        .upload-area:hover { background-color: rgba(239, 61, 38, 0.05); }
        .upload-area.dragover { background-color: rgba(239, 61, 38, 0.1); border-color: #d63219; }
        .file-item { background-color: #f8f9fa; padding: 10px; border-radius: 5px; margin-bottom: 10px; display: flex; justify-content: space-between; align-items: center; }
        .progress-section { display: none; }
        .csv-preview { max-height: 400px; overflow-y: auto; }
        .badge-category { display: inline-block; background-color: #537B83; color: white; padding: 4px 8px; border-radius: 4px; font-size: 0.85em; margin: 2px; }
        .alert-info { background-color: #e3f2fd; border-left: 4px solid #2196F3; }
        .step-indicator { display: flex; justify-content: space-between; margin-bottom: 30px; }
        .step { flex: 1; text-align: center; padding: 15px; background-color: #f0f0f0; border-radius: 8px; margin: 0 5px; }
        .step.active { background-color: #EF3D26; color: white; }
        .step.completed { background-color: #4CAF50; color: white; }
        .spinner { display: inline-block; width: 20px; height: 20px; border: 3px solid #f3f3f3; border-top: 3px solid #EF3D26; border-radius: 50%; animation: spin 1s linear infinite; }
        @keyframes spin { 0% { transform: rotate(0deg); } 100% { transform: rotate(360deg); } }
    </style>
</head>
<body>
    <nav class="navbar navbar-dark mb-4">
        <div class="container-fluid">
            <span class="navbar-brand mb-0 h1">
                <i class="fas fa-utensils"></i> trovapiatto Admin - Importa Menu da Foto
            </span>
        </div>
    </nav>

    <div class="container">
        <!-- Step Indicator -->
        <div class="step-indicator mb-4">
            <div class="step active" id="step-1">
                <i class="fas fa-upload"></i>
                <h6>1. Carica Foto</h6>
            </div>
            <div class="step" id="step-2">
                <i class="fas fa-spinner"></i>
                <h6>2. Elabora OCR</h6>
            </div>
            <div class="step" id="step-3">
                <i class="fas fa-check"></i>
                <h6>3. Verifica</h6>
            </div>
            <div class="step" id="step-4">
                <i class="fas fa-database"></i>
                <h6>4. Importa</h6>
            </div>
        </div>

        <!-- Main Content -->
        <div class="row">
            <div class="col-md-8">
                <!-- Step 1: Upload -->
                <div class="card mb-4" id="section-upload">
                    <div class="card-header bg-primary text-white">
                        <h5 class="mb-0"><i class="fas fa-camera"></i> 1. Carica Foto Menu</h5>
                    </div>
                    <div class="card-body">
                        <div class="mb-3">
                            <label class="form-label">Seleziona Ristorante:</label>
                            <select class="form-select" id="restaurant-select">
                                <option value="">-- Carica ristoranti --</option>
                            </select>
                        </div>

                        <div class="mb-3">
                            <label class="form-label">Categoria Piatti:</label>
                            <input type="text" class="form-control" id="category-input" placeholder="es. Piatti Principali" value="Piatti">
                        </div>

                        <div class="upload-area" id="upload-area" ondrop="handleDrop(event)" ondragover="handleDragOver(event)" ondragleave="handleDragLeave(event)" onclick="document.getElementById('file-input').click()">
                            <i class="fas fa-cloud-upload-alt" style="font-size: 3em; color: #EF3D26; margin-bottom: 10px;"></i>
                            <h5>Trascina foto qui o clicca per selezionare</h5>
                            <p class="text-muted">Supportati: JPG, PNG, WebP (max 10MB per file)</p>
                        </div>
                        <input type="file" id="file-input" style="display: none;" multiple accept="image/*" onchange="handleFileSelect(event)">

                        <div class="mt-3" id="files-list"></div>

                        <button class="btn btn-primary btn-lg w-100 mt-3" id="btn-upload" disabled>
                            <i class="fas fa-upload"></i> Carica e Elabora
                        </button>
                    </div>
                </div>

                <!-- Step 2: Processing -->
                <div class="card mb-4" id="section-processing" style="display: none;">
                    <div class="card-header bg-info text-white">
                        <h5 class="mb-0"><i class="spinner"></i> 2. Elaborazione OCR</h5>
                    </div>
                    <div class="card-body">
                        <div class="progress mb-3">
                            <div class="progress-bar progress-bar-animated" id="progress-bar" style="width: 0%"></div>
                        </div>
                        <p id="progress-text" class="text-center">Inizializzazione...</p>
                        <div id="progress-details" class="alert alert-info"></div>
                    </div>
                </div>

                <!-- Step 3: Preview CSV -->
                <div class="card mb-4" id="section-preview" style="display: none;">
                    <div class="card-header bg-success text-white">
                        <h5 class="mb-0"><i class="fas fa-check-circle"></i> 3. Verifica Piatti Estratti</h5>
                    </div>
                    <div class="card-body">
                        <div class="alert alert-info">
                            <i class="fas fa-info-circle"></i> Verifica i dati estratti. Deseleziona i piatti che non vuoi importare.
                        </div>

                        <div class="mb-3">
                            <input type="text" class="form-control" id="search-dishes" placeholder="Cerca piatto...">
                        </div>

                        <div class="csv-preview" id="csv-preview"></div>

                        <div class="mt-3">
                            <button class="btn btn-warning" id="btn-edit-csv">
                                <i class="fas fa-edit"></i> Scarica CSV per editare
                            </button>
                            <button class="btn btn-primary" id="btn-import" disabled>
                                <i class="fas fa-database"></i> Importa nel Database
                            </button>
                        </div>
                    </div>
                </div>

                <!-- Step 4: Results -->
                <div class="card mb-4" id="section-results" style="display: none;">
                    <div class="card-header bg-success text-white">
                        <h5 class="mb-0"><i class="fas fa-check"></i> 4. Importazione Completata</h5>
                    </div>
                    <div class="card-body">
                        <div id="results-content"></div>
                        <button class="btn btn-primary mt-3" onclick="location.reload()">
                            <i class="fas fa-redo"></i> Importa altri piatti
                        </button>
                    </div>
                </div>
            </div>

            <!-- Sidebar -->
            <div class="col-md-4">
                <div class="card mb-4">
                    <div class="card-header bg-secondary text-white">
                        <h6 class="mb-0"><i class="fas fa-info-circle"></i> Informazioni</h6>
                    </div>
                    <div class="card-body">
                        <p><strong>Formato riconosciuto:</strong></p>
                        <code>Piatto | Prezzo | Descrizione | Allergeni</code>

                        <p class="mt-3"><strong>Esempio:</strong></p>
                        <code>Pizza Margherita | 8.00 | Pomodoro, mozzarella | glutine, lattosio</code>

                        <p class="mt-3"><strong>Allergeni riconosciuti:</strong></p>
                        <ul class="small">
                            <li>glutine</li>
                            <li>lattosio</li>
                            <li>uova</li>
                            <li>pesce</li>
                            <li>crostacei</li>
                            <li>molluschi</li>
                            <li>noci</li>
                            <li>arachidi</li>
                            <li>sesamo</li>
                            <li>soia</li>
                            <li>sedano</li>
                            <li>solfiti</li>
                        </ul>
                    </div>
                </div>

                <div class="card">
                    <div class="card-header bg-secondary text-white">
                        <h6 class="mb-0"><i class="fas fa-lightbulb"></i> Consigli</h6>
                    </div>
                    <div class="card-body">
                        <ul class="small">
                            <li>Carica foto nitide e ben illuminate</li>
                            <li>Preferisci formato verticale</li>
                            <li>Una sezione menu per foto</li>
                            <li>Verifica sempre prima di importare</li>
                            <li>I piatti duplicati non saranno importati</li>
                        </ul>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/js/bootstrap.bundle.min.js"></script>
    
    <script>
        const API_URL = '/app/admin/ocr-api.php';
        let uploadedFiles = [];
        let csvData = [];
        let currentRestaurant = '';

        // Carica lista ristoranti
        async function loadRestaurants() {
            try {
                const response = await fetch('/app/admin/api.php?action=get-restaurants');
                const result = await response.json();
                
                if (result.success && result.data) {
                    const select = document.getElementById('restaurant-select');
                    result.data.forEach(r => {
                        const option = document.createElement('option');
                        option.value = r.slug;
                        option.textContent = r.name;
                        select.appendChild(option);
                    });
                }
            } catch (error) {
                console.error('Errore caricamento ristoranti:', error);
            }
        }

        // Drag & Drop
        function handleDragOver(e) {
            e.preventDefault();
            document.getElementById('upload-area').classList.add('dragover');
        }

        function handleDragLeave(e) {
            document.getElementById('upload-area').classList.remove('dragover');
        }

        function handleDrop(e) {
            e.preventDefault();
            document.getElementById('upload-area').classList.remove('dragover');
            handleFileSelect({ target: { files: e.dataTransfer.files } });
        }

        // File Selection
        function handleFileSelect(e) {
            uploadedFiles = Array.from(e.target.files).filter(f => f.type.startsWith('image/'));
            
            if (uploadedFiles.length === 0) {
                alert('Seleziona almeno una foto');
                return;
            }

            displayFiles();
            document.getElementById('btn-upload').disabled = false;
        }

        function displayFiles() {
            const list = document.getElementById('files-list');
            list.innerHTML = '';

            uploadedFiles.forEach((file, index) => {
                const item = document.createElement('div');
                item.className = 'file-item';
                item.innerHTML = `
                    <span>
                        <i class="fas fa-image"></i> ${file.name}
                        <small class="text-muted">(${(file.size / 1024 / 1024).toFixed(2)} MB)</small>
                    </span>
                    <button class="btn btn-sm btn-danger" onclick="removeFile(${index})">
                        <i class="fas fa-trash"></i>
                    </button>
                `;
                list.appendChild(item);
            });
        }

        function removeFile(index) {
            uploadedFiles.splice(index, 1);
            displayFiles();
            if (uploadedFiles.length === 0) {
                document.getElementById('btn-upload').disabled = true;
            }
        }

        // Upload & OCR
        document.getElementById('btn-upload').addEventListener('click', async () => {
            const restaurant = document.getElementById('restaurant-select').value;
            const category = document.getElementById('category-input').value;

            if (!restaurant) {
                alert('Seleziona un ristorante');
                return;
            }

            currentRestaurant = restaurant;
            
            // Mostra sezione processing
            document.getElementById('section-upload').style.display = 'none';
            document.getElementById('section-processing').style.display = 'block';
            updateStepIndicator(2);

            const formData = new FormData();
            uploadedFiles.forEach(f => formData.append('files', f));
            formData.append('restaurant_slug', restaurant);
            formData.append('category', category);

            try {
                const response = await fetch(API_URL + '?action=upload-menu-ocr', {
                    method: 'POST',
                    body: formData
                });

                const result = await response.json();

                if (result.success) {
                    csvData = result.data.preview || [];
                    document.getElementById('progress-details').innerHTML = `
                        <strong>✅ Elaborazione completata!</strong><br>
                        ${result.data.dishes_count} piatti estratti<br>
                        ${result.data.errors.length > 0 ? '<br><strong>Errori:</strong> ' + result.data.errors.join('<br>') : ''}
                    `;
                    
                    setTimeout(() => {
                        showPreview(result.data.preview || []);
                    }, 1000);
                } else {
                    alert('Errore: ' + result.error);
                    location.reload();
                }
            } catch (error) {
                alert('Errore upload: ' + error.message);
                location.reload();
            }
        });

        function updateStepIndicator(step) {
            for (let i = 1; i <= 4; i++) {
                const el = document.getElementById(`step-${i}`);
                if (i < step) {
                    el.classList.remove('active');
                    el.classList.add('completed');
                } else if (i === step) {
                    el.classList.add('active');
                    el.classList.remove('completed');
                } else {
                    el.classList.remove('active', 'completed');
                }
            }
        }

        function showPreview(dishes) {
            document.getElementById('section-processing').style.display = 'none';
            document.getElementById('section-preview').style.display = 'block';
            updateStepIndicator(3);

            const preview = document.getElementById('csv-preview');
            preview.innerHTML = '';

            dishes.forEach((dish, index) => {
                const row = document.createElement('div');
                row.className = 'card mb-2';
                row.innerHTML = `
                    <div class="card-body p-2">
                        <div class="form-check">
                            <input type="checkbox" class="form-check-input dish-check" data-index="${index}" checked>
                            <label class="form-check-label ms-2">
                                <strong>${dish.name}</strong> - €${dish.price.toFixed(2)}<br>
                                <small class="text-muted">${dish.description || 'Nessuna descrizione'}</small><br>
                                ${dish.allergens.length > 0 ? `<small>${dish.allergens.map(a => `<span class="badge-category">${a}</span>`).join('')}</small>` : ''}
                                <br>
                                <small class="text-secondary">${dish.category}</small>
                            </label>
                        </div>
                    </div>
                `;
                preview.appendChild(row);
            });

            document.getElementById('btn-import').disabled = false;
        }

        // Import
        document.getElementById('btn-import').addEventListener('click', async () => {
            const checked = Array.from(document.querySelectorAll('.dish-check:checked'))
                .map(cb => parseInt(cb.dataset.index));

            const dishesToImport = csvData.filter((_, i) => checked.includes(i));

            if (dishesToImport.length === 0) {
                alert('Seleziona almeno un piatto');
                return;
            }

            updateStepIndicator(4);
            document.getElementById('section-preview').style.display = 'none';
            document.getElementById('section-results').style.display = 'block';
            document.getElementById('results-content').innerHTML = `
                <div class="spinner-border mb-3" role="status"></div>
                <p>Importazione in corso...</p>
            `;

            try {
                // Import via API
                let importedCount = 0;
                let failedCount = 0;
                const results = [];

                for (const dish of dishesToImport) {
                    try {
                        const response = await fetch('/app/admin/api.php', {
                            method: 'POST',
                            headers: { 'Content-Type': 'application/json' },
                            body: JSON.stringify({
                                action: 'create-dish',
                                restaurant_slug: currentRestaurant,
                                name: dish.name,
                                description: dish.description,
                                price: dish.price.toFixed(2),
                                category: dish.category,
                                allergens: dish.allergens
                            })
                        });

                        const result = await response.json();

                        if (result.success) {
                            importedCount++;
                            results.push(`✅ ${dish.name}`);
                        } else {
                            failedCount++;
                            results.push(`❌ ${dish.name}: ${result.error || result.message}`);
                        }
                    } catch (error) {
                        failedCount++;
                        results.push(`❌ ${dish.name}: ${error.message}`);
                    }
                }

                document.getElementById('results-content').innerHTML = `
                    <div class="alert alert-success">
                        <h5><i class="fas fa-check-circle"></i> Importazione Completata!</h5>
                        <p class="mb-0"><strong>${importedCount}</strong> piatti importati nel database</p>
                        ${failedCount > 0 ? `<p class="mb-0 text-warning"><strong>${failedCount}</strong> piatti non importati</p>` : ''}
                    </div>
                    <div style="max-height: 300px; overflow-y: auto; font-size: 0.9em;">
                        ${results.map(r => `<div>${r}</div>`).join('')}
                    </div>
                `;
            } catch (error) {
                alert('Errore import: ' + error.message);
            }
        });

        // Init
        loadRestaurants();
    </script>
</body>
</html>
