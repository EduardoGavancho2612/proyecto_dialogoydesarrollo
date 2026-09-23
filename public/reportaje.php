<?php
require __DIR__ . '/inc/bootstrap.php';

$id = isset($_GET['id']) ? (int) $_GET['id'] : 0;

$stmt = $pdo->prepare("
    SELECT r.*,
           CASE WHEN a.es_nickname = 1 AND a.nickname IS NOT NULL AND a.nickname <> ''
                THEN a.nickname
                ELSE CONCAT_WS(' ', a.nombres, a.ap_paterno, a.ap_materno)
           END AS autor
    FROM reportajes r
    LEFT JOIN autores a ON a.id = r.autor_id
    WHERE r.id = ? AND r.estado = 'publicado'
");
$stmt->execute([$id]);
$reportaje = $stmt->fetch();

if (!$reportaje) {
    http_response_code(404);
    $page_title = 'Reportaje no encontrado';
    $active_nav = 'reportajes';
    require __DIR__ . '/inc/header.php';
    breadcrumb('Reportaje no encontrado', ['Inicio' => 'index.php', 'Reportajes' => 'reportajes.php']);
    echo '<section class="w3l-blog mt-lg-5 pb-5"><div class="container text-center py-5">'
       . '<p><a href="reportajes.php" class="btn btn-style btn-primary">Ver todos los reportajes</a></p>'
       . '</div></section>';
    require __DIR__ . '/inc/footer.php';
    exit;
}

$fotosStmt = $pdo->prepare('SELECT url_foto, descripcion FROM reportajes_fotos WHERE reportaje_id = ? ORDER BY orden, id');
$fotosStmt->execute([$id]);
$fotos = $fotosStmt->fetchAll();

// --- Sidebar: "Ultimas noticias" (reportajes recientes, sin el actual) ---
$recientesStmt = $pdo->prepare("SELECT id, titulo, fecha_publicacion FROM reportajes WHERE id <> ? AND estado = 'publicado' ORDER BY fecha_publicacion DESC, id DESC LIMIT 3");
$recientesStmt->execute([$id]);
$recientes = $recientesStmt->fetchAll();

// --- Sidebar: "Archivos" (meses con reportajes publicados) ---
$meses = $pdo->query("SELECT DISTINCT DATE_FORMAT(fecha_publicacion, '%Y-%m') AS ym FROM reportajes WHERE estado = 'publicado' ORDER BY ym DESC LIMIT 12")->fetchAll(PDO::FETCH_COLUMN);

$page_title = $reportaje['titulo'] . ' - Diálogo y Desarrollo Perú';
$page_description = $reportaje['resumen_corto'] ?: $reportaje['desarrollo'];
$page_image = $reportaje['foto_principal'] ?? null;
$active_nav = 'reportajes';
require __DIR__ . '/inc/header.php';

breadcrumb($reportaje['titulo'], ['Inicio' => 'index.php', 'Reportajes' => 'reportajes.php', 'Reportaje' => null]);
?>

<section class="w3l-blog mt-lg-5">
    <div class="text-element-9 py-5 mt-lg-5">
        <div class="container py-lg-3">
            <div class="row grid-text-9">
                <div class="col-lg-8">
                    <div class="blog-single-post">
                        <div class="post-content">
                            <h2 class="title-single mb-3"><?php echo e($reportaje['titulo']); ?></h2>
                        </div>

                        <?php if (!empty($reportaje['foto_principal'])): ?>
                        <div class="single-post-image mb-4 text-center ddp-corner-red">
                            <img src="<?php echo e(media_url($reportaje['foto_principal'])); ?>" class="img-fluid w-100 radius-image" alt="<?php echo e($reportaje['titulo']); ?>">
                        </div>
                        <?php endif; ?>

                        <div class="single-post-content">
                            <?php if (!empty($reportaje['resumen_corto'])): ?>
                            <blockquote class="blockquote my-5">
                                <q class="mb-3 d-block"><?php echo e($reportaje['resumen_corto']); ?></q>
                            </blockquote>
                            <?php endif; ?>

                            <p class="mb-2">
                                <span class="fa fa-calendar mr-1"></span> <?php echo e(fecha_es($reportaje['fecha_publicacion'])); ?>
                                <?php if (!empty($reportaje['autor'])): ?>
                                    &nbsp;&nbsp;<span class="fa fa-user mr-1"></span> <?php echo e($reportaje['autor']); ?>
                                <?php endif; ?>
                            </p>

                            <?php echo render_desarrollo($reportaje['desarrollo']); ?>

                            <?php if (!empty($reportaje['pdf_adjunto'])): ?>
                                <p align="center" class="mb-4">
                                    <a target="_blank" rel="noopener" href="<?php echo e(media_url($reportaje['pdf_adjunto'])); ?>" class="btn btn-style btn-primary">
                                        <span class="fa fa-download"></span> Descargar PDF
                                    </a>
                                </p>
                            <?php endif; ?>

                            <?php if ($fotos): ?>
                                <div class="row mt-4">
                                    <?php foreach ($fotos as $f): ?>
                                    <div class="col-md-4 col-6 mb-4">
                                        <img src="<?php echo e(media_url($f['url_foto'])); ?>" alt="<?php echo e($f['descripcion']); ?>" class="img-fluid radius-image">
                                        <?php if (!empty($f['descripcion'])): ?>
                                            <p class="text-muted mt-2 mb-0"><small><?php echo e($f['descripcion']); ?></small></p>
                                        <?php endif; ?>
                                    </div>
                                    <?php endforeach; ?>
                                </div>
                            <?php endif; ?>
                        </div>

                        <nav class="post-navigation row mb-5 py-4">
                            <div class="post-prev col-md-6 pr-sm-5">
                                <span class="nav-title"><span class="fa fa-arrow-left mr-2"></span> <a href="reportajes.php">Reportajes</a></span>
                            </div>
                        </nav>
                    </div>
                </div>

                <div class="col-lg-4 left-text-9 mt-lg-0 mt-5 pl-lg-4">
                    <?php if ($recientes): ?>
                    <div class="left-top-9 mt-5 pt-sm-3">
                        <h6 class="heading-small-text-9 mb-3">Últimos reportajes</h6>
                        <?php foreach ($recientes as $r): ?>
                        <a href="reportaje.php?id=<?php echo (int) $r['id']; ?>" class="p-post d-block py-2">
                            <h6 class="text-left-inner-9"><?php echo e($r['titulo']); ?></h6>
                            <span class="sub-inner-text-9"><?php echo e(fecha_es($r['fecha_publicacion'])); ?></span>
                        </a>
                        <?php endforeach; ?>
                    </div>
                    <?php endif; ?>

                    <?php if ($meses): ?>
                    <div class="categories mt-5 pt-sm-3">
                        <h6 class="heading-small-text-9">Archivos</h6>
                        <ul>
                            <?php foreach ($meses as $ym): ?>
                            <li><a href="reportajes.php?mes=<?php echo e($ym); ?>"><?php echo e(mes_es($ym)); ?></a></li>
                            <?php endforeach; ?>
                        </ul>
                    </div>
                    <?php endif; ?>
                </div>
            </div>
        </div>
    </div>
</section>

<?php require __DIR__ . '/inc/footer.php'; ?>
