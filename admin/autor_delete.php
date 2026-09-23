<?php
session_start();
require __DIR__ . '/../config/conexion.php';
require __DIR__ . '/../config/upload_helper.php';
require __DIR__ . '/../config/permisos.php';

if (empty($_SESSION['usuario_id'])) {
    header('Location: login.php');
    exit;
}
exigir('autores', 'editar');

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $id = (int) ($_POST['id'] ?? 0);

    $stmt = $pdo->prepare('SELECT COUNT(*) FROM reportajes WHERE autor_id = ?');
    $stmt->execute([$id]);

    if ($stmt->fetchColumn() > 0) {
        set_flash('error', 'No se puede eliminar: hay reportajes asociados a este autor.');
    } else {
        $stmt = $pdo->prepare('DELETE FROM autores WHERE id = ?');
        $stmt->execute([$id]);
        set_flash('success', 'Autor eliminado correctamente.');
    }
}

header('Location: autores.php');
exit;
