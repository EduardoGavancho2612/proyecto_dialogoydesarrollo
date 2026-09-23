<?php
session_start();
require __DIR__ . '/../config/conexion.php';
require __DIR__ . '/../config/permisos.php';

if (empty($_SESSION['usuario_id'])) {
    header('Location: login.php');
    exit;
}
exigir('videos', 'alternar');

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $id = (int) ($_POST['id'] ?? 0);
    $stmt = $pdo->prepare("UPDATE videos SET estado = IF(estado = 'publicado', 'oculto', 'publicado') WHERE id = ?");
    $stmt->execute([$id]);
    set_flash('success', 'Estado del video actualizado.');
}

header('Location: videos.php');
exit;
