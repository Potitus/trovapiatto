<?php
session_start();

// Distruggi la sessione admin
$_SESSION = array();
if (ini_get('session.use_cookies')) {
    $params = session_get_cookie_params();
    setcookie(session_name(), '', time() - 42000,
        $params['path'], $params['domain'],
        $params['secure'], $params['httponly']
    );
}
session_destroy();

// Redirect alla pagina di login (auth-check gestirà il form)
header('Location: /app/admin/');
exit();
