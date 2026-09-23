<?php
/** Sitemap XML dinamico: paginas estaticas + cada reportaje publicado. */
require __DIR__ . '/inc/bootstrap.php';

$scheme = (isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off') ? 'https://' : 'http://';
$base = $scheme . ($_SERVER['HTTP_HOST'] ?? 'localhost') . rtrim(dirname($_SERVER['SCRIPT_NAME']), '/') . '/';

header('Content-Type: application/xml; charset=utf-8');

$urls = [
    ['loc' => $base . 'index.php', 'prioridad' => '1.0'],
    ['loc' => $base . 'reportajes.php', 'prioridad' => '0.9'],
    ['loc' => $base . 'boletines.php', 'prioridad' => '0.7'],
    ['loc' => $base . 'podcasts.php', 'prioridad' => '0.6'],
    ['loc' => $base . 'alianzas.php', 'prioridad' => '0.4'],
    ['loc' => $base . 'sobre.php', 'prioridad' => '0.4'],
];

$reportajes = $pdo->query("SELECT id, fecha_publicacion, updated_at FROM reportajes WHERE estado = 'publicado' ORDER BY fecha_publicacion DESC")->fetchAll();
foreach ($reportajes as $r) {
    $urls[] = [
        'loc' => $base . 'reportaje.php?id=' . (int) $r['id'],
        'lastmod' => date('Y-m-d', strtotime($r['updated_at'] ?: $r['fecha_publicacion'])),
        'prioridad' => '0.8',
    ];
}

echo '<?xml version="1.0" encoding="UTF-8"?>' . "\n";
echo '<urlset xmlns="http://www.sitemaps.org/schemas/sitemap/0.9">' . "\n";
foreach ($urls as $u) {
    echo "  <url>\n";
    echo '    <loc>' . htmlspecialchars($u['loc'], ENT_XML1) . "</loc>\n";
    if (!empty($u['lastmod'])) {
        echo '    <lastmod>' . $u['lastmod'] . "</lastmod>\n";
    }
    echo '    <priority>' . $u['prioridad'] . "</priority>\n";
    echo "  </url>\n";
}
echo '</urlset>';
