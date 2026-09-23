<?php
session_start();
require __DIR__ . '/../config/conexion.php';
require __DIR__ . '/../config/permisos.php';

if (empty($_SESSION['usuario_id'])) {
    header('Location: login.php');
    exit;
}
exigir('reportajes', 'alternar');

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $id = (int) ($_POST['id'] ?? 0);
    // No toca los borradores: alternar solo tiene sentido entre publicado y oculto.
    $stmt = $pdo->prepare("UPDATE reportajes SET estado = CASE estado WHEN 'publicado' THEN 'oculto' WHEN 'oculto' THEN 'publicado' ELSE estado END WHERE id = ?");
    $stmt->execute([$id]);
    set_flash('success', 'Estado del reportaje actualizado.');
}

header('Location: reportajes.php');
exit;
