<?php
/**
 * Arranque comun del front-end publico.
 * Reutiliza la conexion PDO del panel de administracion (config/conexion.php).
 */

require __DIR__ . '/../../config/conexion.php';

/** Escapa texto para HTML. */
function e(?string $s): string
{
    return htmlspecialchars((string) $s, ENT_QUOTES, 'UTF-8');
}

/**
 * Convierte una ruta guardada en la base de datos (relativa a /theme/, o una URL
 * absoluta) en una URL utilizable desde /theme/public/.
 */
function media_url(?string $path, string $fallback = 'assets/site/video.jpg'): string
{
    $path = trim((string) $path);
    if ($path === '') {
        return $fallback;
    }
    if (preg_match('#^(https?:)?//#i', $path) || str_starts_with($path, 'data:')) {
        return $path;
    }
    // Rutas del panel (uploads/..., images/...) viven un nivel arriba de /public/.
    return '../' . ltrim($path, '/');
}

/** Formatea 2026-08-28 -> "Ago 28, 2026". */
function fecha_es(?string $fecha): string
{
    if (empty($fecha)) {
        return '';
    }
    $ts = strtotime($fecha);
    if ($ts === false) {
        return (string) $fecha;
    }
    $meses = [1 => 'Ene', 'Feb', 'Mar', 'Abr', 'May', 'Jun', 'Jul', 'Ago', 'Set', 'Oct', 'Nov', 'Dic'];
    return $meses[(int) date('n', $ts)] . ' ' . date('j', $ts) . ', ' . date('Y', $ts);
}

/** Recorta un texto a $max caracteres agregando puntos suspensivos. */
function resumen(?string $texto, int $max = 220): string
{
    $texto = trim(preg_replace('/\s+/', ' ', (string) $texto));
    if (mb_strlen($texto) <= $max) {
        return $texto;
    }
    return mb_substr($texto, 0, $max) . '...';
}

/**
 * Normaliza una URL de video/podcast a una forma embebible en un <iframe>.
 * Acepta enlaces normales de YouTube y devuelve la version /embed/.
 */
function embed_url(?string $url): string
{
    $url = trim((string) $url);
    if ($url === '') {
        return '';
    }
    if (preg_match('#youtube\.com/watch\?v=([\w-]+)#i', $url, $m)) {
        return 'https://www.youtube.com/embed/' . $m[1];
    }
    if (preg_match('#youtu\.be/([\w-]+)#i', $url, $m)) {
        return 'https://www.youtube.com/embed/' . $m[1];
    }
    return $url;
}

/**
 * Renderiza la seccion de encabezado con titulo grande y migas de pan.
 * $trail: pares [etiqueta => url|null]. El ultimo item se marca como activo.
 */
function breadcrumb(string $titulo, array $trail = []): void
{
    ?>
    <section class="breadcrumb-area py-sm-5 py-4">
        <div class="container">
            <div class="row">
                <div class="col-md-12">
                    <div class="breadcrumb-contents">
                        <h2 class="title-big"><?php echo e($titulo); ?></h2>
                        <?php if ($trail): ?>
                        <div class="breadcrumb">
                            <ul>
                                <?php $i = 0; $n = count($trail); foreach ($trail as $label => $url): $i++; ?>
                                    <li class="<?php echo $i === $n ? 'active' : ''; ?>">
                                        <?php if ($url && $i !== $n): ?>
                                            <a href="<?php echo e($url); ?>"><?php echo e($label); ?></a>
                                        <?php else: ?>
                                            <?php echo e($label); ?>
                                        <?php endif; ?>
                                    </li>
                                <?php endforeach; ?>
                            </ul>
                        </div>
                        <?php endif; ?>
                    </div>
                </div>
            </div>
        </div>
    </section>
    <?php
}

/**
 * Renderiza el HTML guardado en "desarrollo" (parrafos, imagenes intercaladas,
 * subtitulos) reescribiendo las rutas de imagen para que funcionen desde /public/.
 */
function render_desarrollo(?string $html): string
{
    $html = (string) $html;
    if ($html === '') {
        return '';
    }
    return preg_replace_callback('/\ssrc="([^"]+)"/', function ($m) {
        return ' src="' . e(media_url($m[1])) . '"';
    }, $html);
}

/**
 * Renderiza la navegacion de paginas (Ant / 1 2 3 ... / Sig), igual que el sitio original.
 * $params: parametros extra a conservar en los enlaces (ej. ['mes' => '2026-08']).
 */
function pagination_nav(string $baseUrl, int $actual, int $totalPaginas, array $params = []): void
{
    if ($totalPaginas <= 1) {
        return;
    }
    $params = array_filter($params, fn($v) => $v !== '' && $v !== null);
    $link = function (int $p) use ($baseUrl, $params) {
        return $baseUrl . '?' . http_build_query($params + ['pagina' => $p]);
    };
    ?>
    <div class="pagination">
        <ul>
            <?php if ($actual > 1): ?>
                <li class="prev"><a href="<?php echo e($link($actual - 1)); ?>"> Ant</a></li>
            <?php endif; ?>
            <?php for ($p = 1; $p <= $totalPaginas; $p++): ?>
                <li><a href="<?php echo e($link($p)); ?>" class="<?php echo $p === $actual ? 'active' : ''; ?>"><?php echo $p; ?></a></li>
            <?php endfor; ?>
            <?php if ($actual < $totalPaginas): ?>
                <li class="next"><a href="<?php echo e($link($actual + 1)); ?>"> Sig </a></li>
            <?php endif; ?>
        </ul>
    </div>
    <?php
}

/** Convierte "2026-08" en "Agosto 2026". */
function mes_es(string $ym): string
{
    $meses = [1 => 'Enero', 'Febrero', 'Marzo', 'Abril', 'Mayo', 'Junio', 'Julio', 'Agosto', 'Septiembre', 'Octubre', 'Noviembre', 'Diciembre'];
    [$anio, $mes] = array_pad(explode('-', $ym), 2, '');
    $mes = (int) $mes;
    return ($meses[$mes] ?? $ym) . ' ' . $anio;
}

/**
 * Decide como reproducir el audio de un podcast segun la URL/archivo guardado:
 *  - 'file'    -> archivo de audio directo (subido o URL .mp3/.wav/...): usa el
 *                 reproductor propio del sitio (barra inferior derecha).
 *  - 'spotify' -> Spotify no entrega un archivo de audio directo para incrustar
 *                 en un <audio>, asi que se usa su propio widget embebido oficial.
 *  - null      -> no se puede reproducir en la pagina (solo queda el enlace externo).
 */
function podcast_playback(?string $audioUrl): array
{
    $audioUrl = trim((string) $audioUrl);
    if ($audioUrl === '') {
        return ['tipo' => null, 'src' => null];
    }

    // Ruta local (subida desde el panel) o URL que termina en una extension de audio.
    if (!preg_match('#^https?://#i', $audioUrl) || preg_match('/\.(mp3|wav|m4a|ogg|aac)(\?.*)?$/i', $audioUrl)) {
        return ['tipo' => 'file', 'src' => $audioUrl];
    }

    // Spotify: solo funciona con su reproductor embebido (no expone un mp3 directo).
    if (preg_match('#open\.spotify\.com/(?:embed/)?(episode|show)/([A-Za-z0-9]+)#i', $audioUrl, $m)) {
        return ['tipo' => 'spotify', 'src' => 'https://open.spotify.com/embed/' . $m[1] . '/' . $m[2] . '?utm_source=generator'];
    }

    // Cualquier otra URL: se intenta como archivo de audio directo (mejor esfuerzo).
    return ['tipo' => 'file', 'src' => $audioUrl];
}

/** Miniatura oficial de YouTube para una URL de embed (o null si no es de YouTube). */
function youtube_thumb(?string $embedUrl): ?string
{
    if ($embedUrl && preg_match('#youtube\.com/embed/([\w-]+)#i', $embedUrl, $m)) {
        return 'https://img.youtube.com/vi/' . $m[1] . '/hqdefault.jpg';
    }
    return null;
}

/** Nombre visible de un autor (nickname o nombre completo). */
function nombre_autor(array $a): string
{
    if (!empty($a['es_nickname']) && !empty($a['nickname'])) {
        return $a['nickname'];
    }
    return trim(($a['nombres'] ?? '') . ' ' . ($a['ap_paterno'] ?? '') . ' ' . ($a['ap_materno'] ?? ''));
}
