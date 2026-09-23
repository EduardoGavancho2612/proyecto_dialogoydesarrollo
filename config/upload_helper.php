<?php
function handle_upload(string $fieldName, string $subdir, array $allowedExt, int $maxBytes, ?string $current = null): ?string
{
    if (empty($_FILES[$fieldName]) || $_FILES[$fieldName]['error'] === UPLOAD_ERR_NO_FILE) {
        return $current;
    }

    $file = $_FILES[$fieldName];

    if ($file['error'] !== UPLOAD_ERR_OK) {
        throw new RuntimeException('Error al subir el archivo (codigo ' . $file['error'] . ').');
    }

    if ($file['size'] > $maxBytes) {
        throw new RuntimeException('El archivo supera el tamano maximo permitido (' . round($maxBytes / 1024 / 1024, 1) . ' MB).');
    }

    $ext = strtolower(pathinfo($file['name'], PATHINFO_EXTENSION));
    if (!in_array($ext, $allowedExt, true)) {
        throw new RuntimeException('Extension no permitida (.' . $ext . '). Permitidas: ' . implode(', ', $allowedExt));
    }

    $destDir = __DIR__ . '/../uploads/' . $subdir . '/';
    if (!is_dir($destDir)) {
        mkdir($destDir, 0755, true);
    }

    $filename = uniqid($subdir . '_', true) . '.' . $ext;

    if (!move_uploaded_file($file['tmp_name'], $destDir . $filename)) {
        throw new RuntimeException('No se pudo guardar el archivo en el servidor.');
    }

    return 'uploads/' . $subdir . '/' . $filename;
}

/** Guarda un mensaje flash en sesion para mostrarlo despues de un redirect. */
function set_flash(string $type, string $message): void
{
    $_SESSION['flash'] = ['type' => $type, 'message' => $message];
}
