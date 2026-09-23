<?php
require __DIR__ . '/inc/bootstrap.php';

$mes = $_GET['mes'] ?? '';
$mes = preg_match('/^\d{4}-\d{2}$/', $mes) ? $mes : '';

$porPagina = 9;
$pagina = max(1, (int) ($_GET['pagina'] ?? 1));

if ($mes) {
    $total = $pdo->prepare("SELECT COUNT(*) FROM reportajes WHERE estado = 'publicado' AND DATE_FORMAT(fecha_publicacion, '%Y-%m') = ?");
    $total->execute([$mes]);
} else {
    $total = $pdo->query("SELECT COUNT(*) FROM reportajes WHERE estado = 'publicado'");
}
$totalReportajes = (int) $total->fetchColumn();
$totalPaginas = max(1, (int) ceil($totalReportajes / $porPagina));
$pagina = min($pagina, $totalPaginas);
$offset = ($pagina - 1) * $porPagina;

if ($mes) {
    $stmt = $pdo->prepare("
        SELECT id, titulo, resumen_corto, foto_principal, fecha_publicacion, es_destacado
        FROM reportajes
        WHERE estado = 'publicado' AND DATE_FORMAT(fecha_publicacion, '%Y-%m') = ?
        ORDER BY fecha_publicacion DESC, id DESC
        LIMIT $porPagina OFFSET $offset
    ");
    $stmt->execute([$mes]);
    $reportajes = $stmt->fetchAll();
} else {
    $stmt = $pdo->query("
        SELECT id, titulo, resumen_corto, foto_principal, fecha_publicacion, es_destacado
        FROM reportajes
        WHERE estado = 'publicado'
        ORDER BY fecha_publicacion DESC, id DESC
        LIMIT $porPagina OFFSET $offset
    ");
    $reportajes = $stmt->fetchAll();
}

$page_title = 'Reportajes - Diálogo y Desarrollo Perú';
$page_description = 'Reportajes de investigación sobre minería, canon y desarrollo territorial en el Perú.';
$active_nav = 'reportajes';
require __DIR__ . '/inc/header.php';

breadcrumb('Reportajes', ['Inicio' => 'index.php', 'Reportajes' => null]);
?>

<?php if ($mes): ?>
<div class="container">
    <p class="mb-0">Mostrando reportajes de <strong><?php echo e(mes_es($mes)); ?></strong> —
        <a href="reportajes.php">ver todos</a>
    </p>
</div>
<?php endif; ?>

<div class="grids-block-5 py-5">
    <section class="py-lg-4 py-md-3">
        <div class="container">
            <div class="row">
                <?php if (empty($reportajes)): ?>
                    <div class="col-12"><p class="text-center">Aún no hay reportajes publicados.</p></div>
                <?php endif; ?>
                <?php foreach ($reportajes as $i => $r): ?>
                <div class="col-lg-4 col-md-6 grids5-info <?php echo $i >= 3 ? 'mt-5' : ''; ?>">
                    <a href="reportaje.php?id=<?php echo (int) $r['id']; ?>" class="d-block ddp-corner-red">
                        <img src="<?php echo e(media_url($r['foto_principal'])); ?>" alt="<?php echo e($r['titulo']); ?>" class="img-fluid">
                    </a>
                    <div class="blog-info">
                        <h5><?php echo e(fecha_es($r['fecha_publicacion'])); ?><?php echo $r['es_destacado'] ? ' &middot; Destacado' : ''; ?></h5>
                        <h4><a href="reportaje.php?id=<?php echo (int) $r['id']; ?>" class="d-block"><?php echo e($r['titulo']); ?></a></h4>
                        <a href="reportaje.php?id=<?php echo (int) $r['id']; ?>" class="btn mt-4 p-0">Leer <span class="fa fa-arrow-right"></span></a>
                    </div>
                </div>
                <?php endforeach; ?>
            </div>

            <?php pagination_nav('reportajes.php', $pagina, $totalPaginas, ['mes' => $mes]); ?>
        </div>
    </section>
</div>

<?php require __DIR__ . '/inc/footer.php'; ?>
