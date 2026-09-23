<?php
session_start();
require __DIR__ . '/../config/conexion.php';
require __DIR__ . '/../config/upload_helper.php';
require __DIR__ . '/../config/permisos.php';

if (empty($_SESSION['usuario_id'])) {
    header('Location: login.php');
    exit;
}
exigir('usuarios', 'editar');

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $id = (int) ($_POST['id'] ?? 0);

    if ($id === (int) $_SESSION['usuario_id']) {
        set_flash('error', 'No puedes eliminar tu propio usuario.');
    } else {
        try {
            $stmt = $pdo->prepare('DELETE FROM usuarios WHERE id = ?');
            $stmt->execute([$id]);
            set_flash('success', 'Usuario eliminado correctamente.');
        } catch (PDOException $e) {
            set_flash('error', 'No se puede eliminar: este usuario tiene contenido publicado (reportajes, boletines, etc.).');
        }
    }
}

header('Location: usuarios.php');
exit;
