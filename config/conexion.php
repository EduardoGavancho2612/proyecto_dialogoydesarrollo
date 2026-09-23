<?php

require_once __DIR__ . '/env.php';

// En local (XAMPP) sin .env, se mantiene el acceso root sin contraseña de siempre.
// En el hosting, estos valores vienen del archivo .env (ver .env.example).
$host = env('DB_HOST', 'localhost');
$db   = env('DB_NAME', 'revista_digital');
$user = env('DB_USER', 'root');
$pass = env('DB_PASS', '');
$charset = env('DB_CHARSET', 'utf8mb4');

define('APP_ENV', env('APP_ENV', 'local'));

if (APP_ENV === 'production') {
    // No mostrar rutas de archivos ni detalles internos en pantalla ante un error PHP.
    ini_set('display_errors', '0');
    error_reporting(E_ALL & ~E_DEPRECATED & ~E_NOTICE);
}

$dsn = "mysql:host=$host;dbname=$db;charset=$charset";
$options = [
    PDO::ATTR_ERRMODE            => PDO::ERRMODE_EXCEPTION,
    PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
    PDO::ATTR_EMULATE_PREPARES   => false,
];

try {
    $pdo = new PDO($dsn, $user, $pass, $options);
} catch (PDOException $e) {
    // En produccion no se expone el detalle del error (puede revelar host/usuario/BD).
    die(APP_ENV === 'production'
        ? 'El sitio no esta disponible en este momento. Intenta mas tarde.'
        : 'Error de conexion a la base de datos: ' . $e->getMessage());
}

/*
 * Si la sesion trae un usuario_id que ya no existe en esta base de datos
 * (por ejemplo, una sesion abierta contra la base anterior), se limpia para
 * forzar un nuevo login y evitar violaciones de clave foranea al guardar.
 * Ademas, se refresca el rol y el nombre desde la BD en cada peticion: si un
 * admin cambia el rol de alguien (o lo desactiva) el cambio aplica al toque,
 * sin esperar a que esa persona cierre y vuelva a abrir sesion.
 */
if (session_status() === PHP_SESSION_ACTIVE && !empty($_SESSION['usuario_id'])) {
    $chk = $pdo->prepare("SELECT rol, CONCAT_WS(' ', nombres, ap_paterno, ap_materno) AS nombre FROM usuarios WHERE id = ?");
    $chk->execute([$_SESSION['usuario_id']]);
    $u = $chk->fetch();
    if (!$u) {
        unset($_SESSION['usuario_id'], $_SESSION['usuario_nombre'], $_SESSION['usuario_rol']);
    } else {
        $_SESSION['usuario_rol'] = $u['rol'];
        $_SESSION['usuario_nombre'] = $u['nombre'];
    }
}
