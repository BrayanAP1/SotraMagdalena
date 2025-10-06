<?php
//Configuración de la cookie de sesión
session_set_cookie_params([
    'lifetime' => 0,          
    'path' => '/',
    'domain' => '',          
    'secure' => true,        
    'httponly' => true,       
    'samesite' => 'Strict'  
]);

// Iniciar la sesión
session_start();

//Verificar si hay sesión activa
if (!isset($_SESSION['id'])) {
    header("Location: /Sotramagdalena/index.php");
    exit();
}

//Expiración por inactividad
$tiempo_inactividad = 1800; // 30 minutos
if (isset($_SESSION['LAST_ACTIVITY']) && (time() - $_SESSION['LAST_ACTIVITY'] > $tiempo_inactividad)) {
    session_unset();
    session_destroy();
    header("Location: /Sotramagdalena/index.php?timeout=1");
    exit();
}
$_SESSION['LAST_ACTIVITY'] = time(); // renovar tiempo de actividad

//Seguridad adicional: validar navegador
if (!isset($_SESSION['USER_AGENT'])) {
    $_SESSION['USER_AGENT'] = $_SERVER['HTTP_USER_AGENT'];
} elseif ($_SESSION['USER_AGENT'] !== $_SERVER['HTTP_USER_AGENT']) {
    session_unset();
    session_destroy();
    header("Location: /Sotramagdalena/index.php");
    exit();
}
?>
