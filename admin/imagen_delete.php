<?php
session_start();
require __DIR__ . '/../config/conexion.php';
require __DIR__ . '/../config/upload_helper.php';
require __DIR__ . '/../config/permisos.php';

if (empty($_SESSION['usuario_id'])) {
    header('Location: login.php');
    exit;
}
exigir('reportajes', 'editar');

$reportajeId = 0;
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $id = (int) ($_POST['id'] ?? 0);
    $stmt = $pdo->prepare('SELECT reportaje_id FROM reportajes_fotos WHERE id = ?');
    $stmt->execute([$id]);
    $reportajeId = (int) $stmt->fetchColumn();
    $stmt = $pdo->prepare('DELETE FROM reportajes_fotos WHERE id = ?');
    $stmt->execute([$id]);
    set_flash('success', 'Imagen eliminada correctamente.');
}

header('Location: reportaje_form.php' . ($reportajeId ? '?id=' . $reportajeId : ''));
exit;
