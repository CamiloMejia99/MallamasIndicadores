<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

define('TIEMPO_INACTIVIDAD', 1800); // 30 minutos
//define('TIEMPO_INACTIVIDAD', 10); // 30 minutos

// Si no hay usuario logueado, no hacer nada
if (!isset($_SESSION['usuario'])) {
    return;
}

// Validar tiempo de inactividad
if (isset($_SESSION['ultima_actividad'])) {
    $tiempoInactivo = time() - $_SESSION['ultima_actividad'];

    if ($tiempoInactivo > TIEMPO_INACTIVIDAD) {

        // Destruir sesión por inactividad
        session_unset();
        session_destroy();

        echo "<script>
            alert('Tu sesión ha expirado por inactividad.');
            window.location.href='../../index.php';
        </script>";
        exit;
    }
}

// Actualizar última actividad
$_SESSION['ultima_actividad'] = time();
