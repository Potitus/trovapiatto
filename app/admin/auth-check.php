<?php
/**
 * Middleware di autenticazione per Admin
 * Includi questo file all'inizio di ogni file PHP nella cartella admin
 * che vuoi proteggere
 */

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
        $v = preg_replace('/^["\']|["\']$/', '', $v);
        $env[$k] = $v;
    }
}

// Configurazione (fallback)
$ADMIN_USERNAME = isset($env['ADMIN_USERNAME']) ? $env['ADMIN_USERNAME'] : 'admin';
$ADMIN_PASSWORD = isset($env['ADMIN_PASSWORD']) ? $env['ADMIN_PASSWORD'] : 'trovapiatto2024';
$SESSION_TIMEOUT = isset($env['SESSION_TIMEOUT']) ? intval($env['SESSION_TIMEOUT']) : 3600; // seconds

// Lista di file che NON richiedono autenticazione
$public_files = [
    'get-restaurants.php',
    'api.php',
    'api-clean.php',
    'api-debug.php',
    'auth-check.php',
    'logout.php'
];

// Ottieni il nome del file corrente
$current_file = basename($_SERVER['PHP_SELF']);

// Se il file è pubblico, non fare nulla
if (in_array($current_file, $public_files)) {
    return;
}

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

// Gestisci il login
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['login_action'])) {
    $username = isset($_POST['username']) ? trim($_POST['username']) : '';
    $password = isset($_POST['password']) ? $_POST['password'] : '';
    
    if ($username === $ADMIN_USERNAME && $password === $ADMIN_PASSWORD) {
        $_SESSION['admin_logged_in'] = true;
        $_SESSION['login_time'] = time();
        $_SESSION['username'] = $username;
        header('Location: ' . $_SERVER['REQUEST_URI']);
        exit();
    } else {
        $login_error = 'Username o password non corretti';
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
    <title>Admin Login - trovapiatto.it</title>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@400;500;600;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.2/css/all.min.css">
    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }
        
        body {
            font-family: 'Poppins', sans-serif;
            background: linear-gradient(135deg, #0f0a15 0%, #1a0f25 100%);
            min-height: 100vh;
            display: flex;
            align-items: center;
            justify-content: center;
            padding: 20px;
            position: relative;
            overflow: hidden;
        }
        
        body::before {
            content: '';
            position: absolute;
            top: -50%;
            right: -10%;
            width: 600px;
            height: 600px;
            background: radial-gradient(circle, rgba(231, 58, 58, 0.1), transparent);
            border-radius: 50%;
            animation: float 6s ease-in-out infinite;
        }
        
        @keyframes float {
            0%, 100% { transform: translateY(0); }
            50% { transform: translateY(30px); }
        }
        
        .login-container {
            width: 100%;
            max-width: 420px;
            background: rgba(26, 18, 37, 0.9);
            border: 1px solid rgba(231, 58, 58, 0.2);
            border-radius: 16px;
            padding: 3rem 2rem;
            backdrop-filter: blur(10px);
            box-shadow: 0 25px 50px rgba(0, 0, 0, 0.4);
            position: relative;
            z-index: 10;
            animation: slideUp 0.6s ease-out;
        }
        
        @keyframes slideUp {
            from {
                opacity: 0;
                transform: translateY(30px);
            }
            to {
                opacity: 1;
                transform: translateY(0);
            }
        }
        
        .login-header {
            text-align: center;
            margin-bottom: 2.5rem;
        }
        
        .logo-icon {
            font-size: 3rem;
            margin-bottom: 1rem;
            display: inline-block;
        }
        
        .login-header h1 {
            font-size: 2rem;
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
            margin-bottom: 0.7rem;
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
        
        input[type="text"]::placeholder,
        input[type="password"]::placeholder {
            color: rgba(255, 255, 255, 0.4);
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
            border-left: 4px solid #e73a3a;
            color: #ff8888;
            padding: 12px 16px;
            border-radius: 8px;
            margin-bottom: 1.5rem;
            font-size: 0.9rem;
            display: flex;
            align-items: center;
            gap: 0.8rem;
        }
        
        .error-message i {
            font-size: 1.1rem;
        }
        
        .login-btn {
            width: 100%;
            padding: 12px 24px;
            background: linear-gradient(135deg, #e73a3a, #ff6b6b);
            color: white;
            border: none;
            border-radius: 8px;
            font-weight: 600;
            font-size: 1rem;
            cursor: pointer;
            transition: all 0.3s ease;
            box-shadow: 0 8px 20px rgba(231, 58, 58, 0.3);
            display: flex;
            align-items: center;
            justify-content: center;
            gap: 0.8rem;
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
            padding: 1.2rem;
            background: rgba(255, 255, 255, 0.02);
            border: 1px solid rgba(231, 58, 58, 0.15);
            border-radius: 8px;
            font-size: 0.85rem;
            color: #a0a0a0;
            text-align: center;
            line-height: 1.6;
        }
        
        .security-notice i {
            color: #e73a3a;
            margin-right: 0.5rem;
        }
        
        .forgot-password {
            text-align: center;
            margin-top: 1rem;
        }
        
        .forgot-password a {
            color: #e73a3a;
            text-decoration: none;
            font-size: 0.9rem;
            transition: all 0.3s ease;
        }
        
        .forgot-password a:hover {
            color: #ff6b6b;
        }
    </style>
</head>
<body>
    <div class="login-container">
        <div class="login-header">
            <div class="logo-icon">🔐</div>
            <h1>Admin Panel</h1>
            <p>trovapiatto.it - Area Riservata</p>
        </div>
        
        <?php if (isset($login_error)): ?>
            <div class="error-message">
                <i class="fa-solid fa-circle-exclamation"></i>
                <span><?php echo htmlspecialchars($login_error); ?></span>
            </div>
        <?php endif; ?>
        
        <form method="POST">
            <input type="hidden" name="login_action" value="1">
            
            <div class="form-group">
                <label for="username">
                    <i class="fa-solid fa-user" style="color: #e73a3a; margin-right: 0.5rem;"></i>Username
                </label>
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
                <label for="password">
                    <i class="fa-solid fa-lock" style="color: #e73a3a; margin-right: 0.5rem;"></i>Password
                </label>
                <input 
                    type="password" 
                    id="password" 
                    name="password" 
                    placeholder="Inserisci password"
                    required
                >
            </div>
            
            <button type="submit" class="login-btn">
                <i class="fa-solid fa-sign-in-alt"></i> Accedi
            </button>
        </form>
        
        <div class="security-notice">
            <i class="fa-solid fa-shield"></i>
            Questa area è protetta. Solo amministratori autorizzati possono accedere.
        </div>
        
        <div class="forgot-password">
            <a href="mailto:admin@trovapiatto.it">Password dimenticata?</a>
        </div>
    </div>
</body>
</html>
    <?php
    exit();
}

// Se arriviamo qui, l'utente è loggato
// Continua con il resto dello script
?>
