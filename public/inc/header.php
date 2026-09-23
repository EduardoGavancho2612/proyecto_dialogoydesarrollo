<?php
/**
 * Cabecera comun del front-end publico.
 * Variables opcionales antes del include:
 *   $page_title       (string) Titulo de la pestana / SEO.
 *   $page_description (string) Meta description / og:description (se recorta a 160 car.).
 *   $page_image       (string) Imagen para og:image (ruta guardada en BD o absoluta).
 *   $active_nav       (string) Clave del item de menu activo:
 *                     inicio | actualidad | reportajes | podcast | boletin | alianzas | sobre
 */
$page_title = $page_title ?? 'DDP Noticias - Diálogo y Desarrollo Perú';
$page_description = $page_description ?? 'Periodismo independiente sobre minería, canon y desarrollo territorial en el Perú.';
$page_description = mb_strimwidth(trim(preg_replace('/\s+/', ' ', $page_description)), 0, 160, '...');
$active_nav = $active_nav ?? '';

$scheme = (isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off') ? 'https://' : 'http://';
$host = $_SERVER['HTTP_HOST'] ?? 'localhost';
// El query string (?id=, ?mes=, ?pagina=) identifica la pagina real: se conserva en la canonica.
$canonical = $scheme . $host . ($_SERVER['REQUEST_URI'] ?? '/');
$publicBaseUrl = $scheme . $host . rtrim(dirname($_SERVER['SCRIPT_NAME'] ?? '/'), '/') . '/';
$themeBaseUrl = $scheme . $host . rtrim(dirname(rtrim(dirname($_SERVER['SCRIPT_NAME'] ?? '/'), '/')), '/') . '/';

if (!empty($page_image)) {
    // media_url() ya sabe si el archivo es local (relativo a /theme/) o una URL externa.
    $raw = trim((string) $page_image);
    if (preg_match('#^https?://#i', $raw)) {
        $ogImage = $raw;
    } else {
        $ogImage = $themeBaseUrl . ltrim($raw, '/');
    }
} else {
    $ogImage = $publicBaseUrl . 'assets/site/logo.png';
}

$nav_items = [
    'inicio'     => ['Inicio',       'index.php'],
    'actualidad' => ['Actualidad',   'index.php#actualidad'],
    'reportajes' => ['Reportajes',   'reportajes.php'],
    'podcast'    => ['Podcast',      'podcasts.php'],
    'boletin'    => ['Boletín NTEP', 'boletines.php'],
    'alianzas'   => ['Alianzas',     'alianzas.php'],
    'sobre'      => ['Sobre D&amp;D', 'sobre.php'],
];
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta http-equiv="Content-Type" content="text/html; charset=UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1, shrink-to-fit=no">
    <title><?php echo e($page_title); ?></title>
    <meta name="description" content="<?php echo e($page_description); ?>">
    <link rel="canonical" href="<?php echo e($canonical); ?>">
    <meta property="og:type" content="website">
    <meta property="og:site_name" content="Diálogo y Desarrollo Perú">
    <meta property="og:title" content="<?php echo e($page_title); ?>">
    <meta property="og:description" content="<?php echo e($page_description); ?>">
    <meta property="og:url" content="<?php echo e($canonical); ?>">
    <meta property="og:image" content="<?php echo e($ogImage); ?>">
    <meta name="twitter:card" content="summary_large_image">
    <meta name="twitter:title" content="<?php echo e($page_title); ?>">
    <meta name="twitter:description" content="<?php echo e($page_description); ?>">
    <meta name="twitter:image" content="<?php echo e($ogImage); ?>">
    <link href="assets/site/fonts.css" rel="stylesheet">
    <link rel="stylesheet" href="assets/site/style-starter.css">
    <link rel="stylesheet" href="assets/site/ddp-extras.css">
</head>
<body>
<!-- header -->
<header id="site-header" class="fixed-top">
  <div class="container">
      <nav class="navbar navbar-expand-lg stroke">
      <a class="navbar-brand" href="index.php">
          <img src="assets/site/logo.png" alt="Diálogo y Desarrollo Perú" title="Diálogo y Desarrollo Perú" style="height:75px;">
      </a>
          <button class="navbar-toggler collapsed bg-gradient" type="button" data-toggle="collapse" data-target="#navbarTogglerDemo02" aria-controls="navbarTogglerDemo02" aria-expanded="false" aria-label="Toggle navigation">
              <span class="navbar-toggler-icon fa icon-expand fa-bars"></span>
              <span class="navbar-toggler-icon fa icon-close fa-times"></span>
          </button>

          <div class="collapse navbar-collapse" id="navbarTogglerDemo02">
              <ul class="navbar-nav ml-auto">
                  <?php foreach ($nav_items as $key => [$label, $href]): ?>
                  <li class="nav-item<?php echo $active_nav === $key ? ' active' : ''; ?>">
                      <a class="nav-link" href="<?php echo $href; ?>"><?php echo $label; ?><?php echo $active_nav === $key ? ' <span class="sr-only">(current)</span>' : ''; ?></a>
                  </li>
                  <?php endforeach; ?>
                  <li class="ml-2">
                      <a href="sobre.php#footer" class="btn btn-style btn-outline-secondary">Contacto</a>
                  </li>
              </ul>
          </div>
      </nav>
  </div>
</header>
<!-- //header -->
