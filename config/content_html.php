<?php
/**
 * Helpers para el cuerpo (desarrollo) de reportajes.
 * A partir de ahora "desarrollo" guarda HTML (no texto plano): permite parrafos,
 * imagenes intercaladas con leyenda y subtitulos en negrita, igual que el sitio original.
 */

/** Etiquetas permitidas dentro de "desarrollo". */
const DESARROLLO_TAGS_PERMITIDAS = '<p><br><strong><b><em><i><img><blockquote><ul><ol><li><a><h3><h4>';

/**
 * Convierte texto plano (parrafos separados por linea en blanco) en HTML seguro.
 * Es lo que se usa cuando el admin escribe el desarrollo como texto normal.
 */
function desarrollo_from_plain(string $texto): string
{
    $texto = trim($texto);
    if ($texto === '') {
        return '';
    }
    $bloques = preg_split('/\r\n\r\n|\n\n|\r\r/', $texto, -1, PREG_SPLIT_NO_EMPTY);
    if (!$bloques) {
        $bloques = [$texto];
    }
    $html = '';
    foreach ($bloques as $bloque) {
        $bloque = trim($bloque);
        if ($bloque === '') continue;
        $html .= '<p class="mb-4">' . nl2br(htmlspecialchars($bloque, ENT_QUOTES, 'UTF-8')) . "</p>\n";
    }
    return trim($html);
}

/**
 * Sanea HTML ya escrito (por un admin o por una importacion) dejando solo las
 * etiquetas permitidas. No valida atributos (contenido de administradores de confianza).
 */
function desarrollo_sanitize_html(string $html): string
{
    $html = trim($html);
    if ($html === '') {
        return '';
    }
    return trim(strip_tags($html, DESARROLLO_TAGS_PERMITIDAS));
}

/**
 * Normaliza el "desarrollo" recibido del formulario: si el admin escribio texto
 * plano se auto-formatea en parrafos; si ya trae HTML (p, img, etc.) se conserva
 * saneado.
 */
function desarrollo_normalizar(string $raw): string
{
    $raw = trim($raw);
    if ($raw === '') {
        return '';
    }
    return (strip_tags($raw) === $raw)
        ? desarrollo_from_plain($raw)
        : desarrollo_sanitize_html($raw);
}
