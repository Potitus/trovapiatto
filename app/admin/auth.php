<?php
session_start();

// Carica variabili da .env (se presente)
$env = [];
$envPaths = [dirname(__DIR__, 2) . '/.env', __DIR__ . '/.env'];
foreach ($envPaths as $envPath) {
if (!is_file($envPath)) continue;
    $lines = file($envPath, FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES);
    foreach ($lines as $line) {
        $line = trim($line);
        if ($line === '' || strpos($line, '#') === 0) continue;
        if (strpos($line, '=') === false) continue;
        list($k, $v) = explode('=', $line, 2);
        $k = trim($k);
        $v = trim($v);
        // remove optional surrounding quotes
        $v = preg_replace('/^["\']|["\']$/', '', $v);
        $env[$k] = $v;
    }
}

// Configurazione (fallback se .env mancante)
$ADMIN_USERNAME = isset($env['ADMIN_USERNAME']) ? $env['ADMIN_USERNAME'] : 'admin';
$ADMIN_PASSWORD = isset($env['ADMIN_PASSWORD']) ? $env['ADMIN_PASSWORD'] : 'trovapiatto2024';
$SESSION_TIMEOUT = isset($env['SESSION_TIMEOUT']) ? intval($env['SESSION_TIMEOUT']) : 3600; // seconds

// Verifica se l'utente è loggato
function isLoggedIn() {
    global $SESSION_TIMEOUT;
    if (!isset($_SESSION['admin_logged_in'])) {
        return false;
    }
    
    // Verifica timeout sessione
    if (time() - $_SESSION['login_time'] > $SESSION_TIMEOUT) {
        session_destroy();
        return false;
    }
    
    // Aggiorna l'ultima attività
    $_SESSION['login_time'] = time();
    return true;
}

// Throttle tentativi (condiviso con login JWT)
require_once __DIR__ . '/config.php';
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (!tp_throttle('login', 10, 300, false, 'login-session-bruteforce')) {
        http_response_code(429);
        $error = 'Troppi tentativi. Riprova tra qualche minuto.';
    }
    $username = isset($_POST['username']) ? trim($_POST['username']) : '';
    $password = isset($_POST['password']) ? $_POST['password'] : '';
    if (!isset($error) && $username === $ADMIN_USERNAME && $password === $ADMIN_PASSWORD) {
        $_SESSION['admin_logged_in'] = true;
        $_SESSION['login_time'] = time();
        header('Location: ' . $_SERVER['REQUEST_URI']);
        exit();
    } elseif (!isset($error)) {
        $error = 'Username o password non corretti';
    }
}

// Se non loggato, mostra il form di login
if (!isLoggedIn()) {
    ?>
<!DOCTYPE html>
<html lang="it">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Admin - trovapiatto.it</title>
    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }
        
        body {
            font-family: 'Poppins', -apple-system, BlinkMacSystemFont, 'Segoe UI', sans-serif;
            background: linear-gradient(135deg, #0f0a15 0%, #1a0f25 100%);
            min-height: 100vh;
            display: flex;
            align-items: center;
            justify-content: center;
            padding: 20px;
        }
        
        .login-container {
            width: 100%;
            max-width: 420px;
            background: rgba(26, 18, 37, 0.8);
            border: 1px solid rgba(231, 58, 58, 0.2);
            border-radius: 16px;
            padding: 2.5rem;
            backdrop-filter: blur(10px);
            box-shadow: 0 20px 60px rgba(0, 0, 0, 0.3);
        }
        
        .login-header {
            text-align: center;
            margin-bottom: 2rem;
        }
        
        .logo {
            font-size: 2.5rem;
            margin-bottom: 1rem;
        }
        
        .login-header h1 {
            font-size: 1.8rem;
            font-weight: 700;
            background: linear-gradient(135deg, #e73a3a, #ff6b6b);
            -webkit-background-clip: text;
            -webkit-text-fill-color: transparent;
            background-clip: text;
            margin-bottom: 0.5rem;
        }
        
        .login-header p {
            color: #b0b0b0;
            font-size: 0.95rem;
        }
        
        .form-group {
            margin-bottom: 1.5rem;
        }
        
        label {
            display: block;
            margin-bottom: 0.5rem;
            color: #e0e0e0;
            font-weight: 500;
            font-size: 0.95rem;
        }
        
        input[type="text"],
        input[type="password"] {
            width: 100%;
            padding: 12px 16px;
            border: 1px solid rgba(231, 58, 58, 0.2);
            border-radius: 8px;
            background: rgba(255, 255, 255, 0.05);
            color: #fff;
            font-family: inherit;
            font-size: 1rem;
            transition: all 0.3s ease;
        }
        
        input[type="text"]:focus,
        input[type="password"]:focus {
            outline: none;
            border-color: #e73a3a;
            background: rgba(255, 255, 255, 0.08);
            box-shadow: 0 0 0 3px rgba(231, 58, 58, 0.1);
        }
        
        .error-message {
            background: rgba(231, 58, 58, 0.1);
            border: 1px solid #e73a3a;
            color: #ff6b6b;
            padding: 12px 16px;
            border-radius: 8px;
            margin-bottom: 1.5rem;
            font-size: 0.9rem;
        }
        
        .login-btn {
            width: 100%;
            padding: 12px 24px;
            background: linear-gradient(135deg, #e73a3a, #ff6b6b);
            color: white;
            border: none;
            border-radius: 8px;
            font-weight: 600;
            font-size: 0.95rem;
            cursor: pointer;
            transition: all 0.3s ease;
            box-shadow: 0 8px 20px rgba(231, 58, 58, 0.3);
        }
        
        .login-btn:hover {
            transform: translateY(-2px);
            box-shadow: 0 12px 30px rgba(231, 58, 58, 0.5);
        }
        
        .login-btn:active {
            transform: translateY(0);
        }
        
        .security-notice {
            margin-top: 2rem;
            padding: 1rem;
            background: rgba(255, 255, 255, 0.02);
            border: 1px solid rgba(231, 58, 58, 0.1);
            border-radius: 8px;
            font-size: 0.85rem;
            color: #909090;
            text-align: center;
        }
        
        .security-notice i {
            margin-right: 0.5rem;
        }
    </style>
</head>
<body>
    <div class="login-container">
        <div class="login-header">
            <div class="logo">🔐</div>
            <h1>Admin Panel</h1>
            <p>trovapiatto.it</p>
        </div>
        
        <?php if (isset($error)): ?>
            <div class="error-message">
                ⚠️ <?php echo htmlspecialchars($error); ?>
            </div>
        <?php endif; ?>
        
        <form method="POST">
            <div class="form-group">
                <label for="username">Username</label>
                <input 
                    type="text" 
                    id="username" 
                    name="username" 
                    placeholder="Inserisci username"
                    required
                    autofocus
                >
            </div>
            
            <div class="form-group">
                <label for="password">Password</label>
                <input 
                    type="password" 
                    id="password" 
                    name="password" 
                    placeholder="Inserisci password"
                    required
                >
            </div>
            
            <button type="submit" class="login-btn">Accedi</button>
        </form>
        
        <div class="security-notice">
            🔒 Questa area è protetta. Solo amministratori autorizzati possono accedere.
        </div>
    </div>
</body>
</html>
    <?php
    exit();
}

// Se arriviamo qui, l'utente è loggato
// Includi il resto del pannello admin
?>
