<?php
/**
 * Permisos por rol. admin siempre tiene acceso total (no hace falta listarlo).
 * Acciones: ver (listar), crear, editar, alternar (ocultar/mostrar).
 * Nada de "eliminar": el contenido publicable no se borra, solo se oculta.
 */
require_once __DIR__ . '/upload_helper.php';

$GLOBALS['PERMISOS'] = [
    'reportajes' => [
        'editor'    => ['ver', 'crear', 'editar', 'alternar'],
        'redactor'  => ['ver', 'crear', 'editar'],
    ],
    'boletines' => [
        'editor' => ['ver', 'crear', 'editar', 'alternar'],
    ],
    'noticias' => [
        'editor' => ['ver', 'crear', 'editar', 'alternar'],
    ],
    'podcasts' => [
        'editor' => ['ver', 'crear', 'editar', 'alternar'],
    ],
    'videos' => [
        'editor' => ['ver', 'crear', 'editar', 'alternar'],
    ],
    'archivos' => [
        'editor'   => ['ver'],
        'redactor' => ['ver'],
    ],
    // usuarios y autores: solo admin (no se listan roles adicionales aqui).
];

/** ¿El usuario en sesion puede hacer $accion sobre $recurso? */
function puede(string $recurso, string $accion): bool
{
    $rol = $_SESSION['usuario_rol'] ?? '';
    if ($rol === 'admin') {
        return true;
    }
    return in_array($accion, $GLOBALS['PERMISOS'][$recurso][$rol] ?? [], true);
}

/** Corta la ejecucion con un mensaje si el usuario no tiene el permiso. */
function exigir(string $recurso, string $accion): void
{
    if (!puede($recurso, $accion)) {
        set_flash('error', 'No tienes permiso para realizar esa accion.');
        header('Location: index.php');
        exit;
    }
}
