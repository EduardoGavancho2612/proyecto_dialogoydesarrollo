<?php
require __DIR__ . '/inc/bootstrap.php';

/* ------------------------------------------------------------------ *
 *  Consultas: contenido real de la base de datos revista_digital
 * ------------------------------------------------------------------ */

// Reportaje destacado (el marcado como destacado o, si no hay, el mas reciente).
$destacado = $pdo->query("
    SELECT r.*,
           CASE WHEN a.es_nickname = 1 AND a.nickname IS NOT NULL AND a.nickname <> ''
                THEN a.nickname
                ELSE CONCAT_WS(' ', a.nombres, a.ap_paterno, a.ap_materno)
           END AS autor
    FROM reportajes r
    LEFT JOIN autores a ON a.id = r.autor_id
    WHERE r.estado = 'publicado'
    ORDER BY r.es_destacado DESC, r.fecha_publicacion DESC, r.id DESC
    LIMIT 1
")->fetch();

$destacadoId = $destacado['id'] ?? 0;

// Grilla de reportajes recientes (excluye el destacado).
$stmt = $pdo->prepare("
    SELECT id, titulo, resumen_corto, foto_principal, fecha_publicacion
    FROM reportajes
    WHERE id <> ? AND estado = 'publicado'
    ORDER BY fecha_publicacion DESC, id DESC
    LIMIT 3
");
$stmt->execute([$destacadoId]);
$reportajes = $stmt->fetchAll();

// Noticias recientes.
$noticias = $pdo->query("
    SELECT titulo, foto, link_externo, fecha_publicacion
    FROM noticias
    WHERE estado = 'publicado'
    ORDER BY fecha_publicacion DESC, id DESC
    LIMIT 3
")->fetchAll();

// Ultimo boletin NTEP.
$boletin = $pdo->query("
    SELECT numero_boletin, resumen, foto_portada, archivo_pdf, fecha_publicacion
    FROM boletines
    WHERE estado = 'publicado'
    ORDER BY fecha_publicacion DESC, id DESC
    LIMIT 1
")->fetch();

// Podcasts.
$podcasts = $pdo->query("
    SELECT id, titulo, audio_url, enlace_externo, fecha_publicacion
    FROM podcasts
    WHERE estado = 'publicado'
    ORDER BY fecha_publicacion DESC, id DESC
    LIMIT 4
")->fetchAll();

// Videos (seccion "Especiales").
$videos = $pdo->query("
    SELECT titulo, url_embed, fecha_publicacion
    FROM videos
    WHERE estado = 'publicado'
    ORDER BY fecha_publicacion DESC, id DESC
    LIMIT 8
")->fetchAll();

$page_title = 'DDP Noticias - Diálogo y Desarrollo Perú';
$page_description = $destacado['resumen_corto'] ?? 'Periodismo independiente sobre minería, canon y desarrollo territorial en el Perú.';
$page_image = $destacado['foto_principal'] ?? null;
$active_nav = 'inicio';
require __DIR__ . '/inc/header.php';
?>

<?php breadcrumb('Reportajes'); ?>

<?php if ($destacado): ?>
<section class="w3l-video w3l-homeblock3" id="video">
    <div class="container-fluid">
        <div class="video-grids-info row">
            <div class="video-gd-right col-lg-6 p-0">
                <div class="position-relative">
                    <a href="reportaje.php?id=<?php echo (int) $destacado['id']; ?>" class="ddp-corner-red">
                        <img src="<?php echo e(media_url($destacado['foto_principal'])); ?>" alt="<?php echo e($destacado['titulo']); ?>" class="img-fluid">
                    </a>
                </div>
            </div>
            <div class="video-gd-left col-lg-6 p-lg-5 p-4 align-self">
                <div class="p-xl-4 p-0 video-wrap">
                    <h5><?php echo e(fecha_es($destacado['fecha_publicacion'])); ?></h5>
                    <h3 class="title-big text-left mb-4">
                        <a href="reportaje.php?id=<?php echo (int) $destacado['id']; ?>"><?php echo e($destacado['titulo']); ?></a>
                    </h3>
                    <p><?php echo e(resumen($destacado['resumen_corto'] ?: $destacado['desarrollo'], 320)); ?></p>
                    <a href="reportaje.php?id=<?php echo (int) $destacado['id']; ?>" class="btn mt-4 p-0">Leer <span class="fa fa-arrow-right"></span></a>
                </div>
            </div>
        </div>
    </div>
</section>
<?php endif; ?>

<div class="grids-block-5 py-1">
    <section class="py-lg-4 py-md-3">
        <div class="container">
            <div class="row">
                <?php if (empty($reportajes)): ?>
                    <div class="col-12"><p class="text-center">Aún no hay reportajes publicados.</p></div>
                <?php endif; ?>
                <?php foreach ($reportajes as $r): ?>
                <div class="col-lg-4 col-md-6 grids5-info mt-5">
                    <a href="reportaje.php?id=<?php echo (int) $r['id']; ?>" class="d-block ddp-corner-red">
                        <img src="<?php echo e(media_url($r['foto_principal'])); ?>" alt="<?php echo e($r['titulo']); ?>" class="img-fluid">
                    </a>
                    <div class="blog-info">
                        <h5><?php echo e(fecha_es($r['fecha_publicacion'])); ?></h5>
                        <h4><a href="reportaje.php?id=<?php echo (int) $r['id']; ?>" class="d-block"><?php echo e($r['titulo']); ?></a></h4>
                        <a href="reportaje.php?id=<?php echo (int) $r['id']; ?>" class="btn mt-4 p-0">Leer <span class="fa fa-arrow-right"></span></a>
                    </div>
                </div>
                <?php endforeach; ?>
            </div>
            <div class="pagination">
                <ul>
                    <li><a href="reportajes.php">Ver todos</a></li>
                </ul>
            </div>
        </div>
    </section>
</div>

<section class="breadcrumb-area py-sm-5 py-1">
    <div class="container">
        <div class="row">
            <div class="col-md-12">
                <div class="breadcrumb-contents">
                    <h2 class="title-big">Noticias Recientes</h2><a class="anchor" id="actualidad"></a>
                </div>
            </div>
        </div>
    </div>
</section>

<div class="grids-block-5 py-5" id="actualidad-grid">
    <section class="py-lg-4 py-md-3">
        <div class="container">
            <div class="row">
                <?php if (empty($noticias)): ?>
                    <div class="col-12"><p class="text-center">Aún no hay noticias registradas.</p></div>
                <?php endif; ?>
                <?php foreach ($noticias as $n): ?>
                <div class="col-lg-4 col-md-6 grids5-info mt-lg-0 mt-5">
                    <a target="_blank" rel="noopener" href="<?php echo e($n['link_externo']); ?>" class="d-block">
                        <img src="<?php echo e(media_url($n['foto'])); ?>" alt="<?php echo e($n['titulo']); ?>" class="img-fluid">
                    </a>
                    <div class="blog-info">
                        <h5><?php echo e(fecha_es($n['fecha_publicacion'])); ?></h5>
                        <h4><a target="_blank" rel="noopener" href="<?php echo e($n['link_externo']); ?>" class="d-block"><?php echo e($n['titulo']); ?></a></h4>
                        <a target="_blank" rel="noopener" href="<?php echo e($n['link_externo']); ?>" class="btn mt-4 p-0">Leer <span class="fa fa-arrow-right"></span></a>
                    </div>
                </div>
                <?php endforeach; ?>
            </div>
            <div class="pagination">
                <ul>
                    <li><a target="_blank" rel="noopener" href="https://www.facebook.com/DialogoyDesarrolloPeru">Ver todos</a></li>
                </ul>
            </div>
        </div>
    </section>
</div>

<?php if ($boletin): ?>
<section class="w3l-homeblock5 py-0" id="boletin">
    <div class="container py-lg-5 py-4">
        <div class="row">
            <div class="col-lg-8 align-self">
                <h3 class="title-big mb-4">Boletín NTEP</h3>
                <?php foreach (preg_split('/\r\n|\r|\n/', (string) $boletin['resumen'], -1, PREG_SPLIT_NO_EMPTY) as $linea): ?>
                    <p class=""><?php echo e($linea); ?></p>
                <?php endforeach; ?>
                <div class="row mt-sm-4 mt-2 px-3">
                    <div class="col-6 p-0">
                        <span>Nº <?php echo e($boletin['numero_boletin']); ?></span>
                        <h4><?php echo e(fecha_es($boletin['fecha_publicacion'])); ?></h4>
                    </div>
                    <div class="col-6 p-0">
                        <span>
                            <a target="_blank" rel="noopener" href="<?php echo e(media_url($boletin['archivo_pdf'])); ?>" class="facebook"><span class="fa fa-download"></span></a>
                        </span>
                        <h4>Ver Boletín</h4>
                    </div>
                    <center><a href="boletines.php" class="btn btn-style btn-primary mt-md-5 mt-4">Ver todos</a></center>
                </div>
            </div>
            <div class="col-lg-4 mt-lg-0 mt-4">
                <img src="<?php echo e(media_url($boletin['foto_portada'], 'assets/site/boletin-ntep-45.png')); ?>" class="img-fluid radius-image" alt="Boletín NTEP">
            </div>
        </div>
    </div>
</section>
<?php endif; ?>

<section class="w3l-homeblock3 py-5" id="podcast">
    <div class="container py-lg-5 py-md-4">
        <h3 class="title-big mb-5 text-center">Podcast</h3>
        <?php if (empty($podcasts)): ?>
            <p class="text-center">Aún no hay podcasts publicados.</p>
        <?php else: ?>
        <div id="podcast-carousel" class="owl-carousel owl-theme text-center" data-count="<?php echo count($podcasts); ?>">
            <?php foreach ($podcasts as $p): $play = podcast_playback($p['audio_url']); ?>
            <div class="item">
                <div class="area-box">
                    <?php if ($play['tipo'] === 'file'): ?>
                        <a href="#" class="ddp-podcast-thumb"
                           data-podcast-src="<?php echo e(media_url($play['src'])); ?>"
                           data-podcast-title="<?php echo e($p['titulo']); ?>"
                           data-podcast-share="<?php echo e($p['enlace_externo'] ?: media_url($play['src'])); ?>">
                            <img src="assets/site/podcast.png" alt="Podcast">
                            <span class="ddp-play-overlay"><span class="fa fa-play"></span></span>
                        </a>
                    <?php elseif ($play['tipo'] === 'spotify'): ?>
                        <iframe src="<?php echo e($play['src']); ?>" width="100%" height="232" frameborder="0" scrolling="no"
                                allow="autoplay; clipboard-write; encrypted-media; fullscreen; picture-in-picture" loading="lazy"
                                title="<?php echo e($p['titulo']); ?>"></iframe>
                    <?php elseif (!empty($p['enlace_externo'])): ?>
                        <a target="_blank" rel="noopener" href="<?php echo e($p['enlace_externo']); ?>">
                            <img src="assets/site/podcast.png" alt="Podcast">
                        </a>
                    <?php else: ?>
                        <img src="assets/site/podcast.png" alt="Podcast">
                    <?php endif; ?>
                    <p><?php echo e($p['titulo']); ?></p>
                </div>
            </div>
            <?php endforeach; ?>
        </div>
        <?php endif; ?>
        <center><a href="podcasts.php" class="btn btn-style btn-primary mt-md-5 mt-4">Ver todos</a></center>
    </div>
</section>

<?php if (!empty($videos)): ?>
<section class="w3l-team" id="especiales">
    <div class="teams1 py-5 mb-3">
        <div class="container py-lg-3 pb-lg-5 pb-4">
            <div class="teams1-content">
                <h3 class="title-big text-center mb-5">Especiales</h3>
                <div id="especiales-carousel" class="owl-carousel owl-theme text-center" data-count="<?php echo count($videos); ?>">
                    <?php foreach ($videos as $i => $v): ?>
                    <div class="item">
                        <div class="d-grid team-info">
                            <div class="column position-relative">
                                <a href="#especial-video-<?php echo $i; ?>" class="popup-with-zoom-anim ddp-podcast-thumb">
                                    <img src="<?php echo e(youtube_thumb($v['url_embed']) ?: 'assets/site/video.jpg'); ?>" alt="<?php echo e($v['titulo']); ?>" class="img-fluid rounded team-image">
                                    <span class="ddp-play-overlay"><span class="fa fa-play"></span></span>
                                </a>
                            </div>
                            <div class="column">
                                <p><?php echo e($v['titulo']); ?></p>
                            </div>
                        </div>
                    </div>
                    <?php endforeach; ?>
                </div>
                <?php foreach ($videos as $i => $v): ?>
                <div id="especial-video-<?php echo $i; ?>" class="zoom-anim-dialog mfp-hide">
                    <div class="embed-responsive embed-responsive-16by9">
                        <iframe class="embed-responsive-item" src="<?php echo e(embed_url($v['url_embed'])); ?>" allow="autoplay; fullscreen" allowfullscreen title="<?php echo e($v['titulo']); ?>"></iframe>
                    </div>
                </div>
                <?php endforeach; ?>
            </div>
        </div>
    </div>
</section>
<?php endif; ?>

<section class="w3l-banner py-0" id="work">
    <div class="midd-w3 py-lg-4 py-md-3">
        <div class="container">
            <div class="row">
                <div class="col-lg-6 mt-lg-0 mt-lg-5 about-right-faq align-self">
                    <h5 class="title-small mb-2">DDP Noticias</h5>
                    <h3 class="title-banner">Diálogo y Desarrollo Perú</h3>
                    <p class="mt-4">Somos un espacio de periodismo independiente que busca visibilizar las acciones de diálogo en el país desde una mirada constructiva.</p>
                    <a href="sobre.php" class="btn btn-style btn-primary mt-md-5 mt-4">Nosotros</a>
                </div>
                <div class="col-md-6 left-wthree-img mt-lg-0 mt-4">
                    <div class="position-relative">
                        <img src="assets/site/bannerimg.jpg" alt="" class="img-fluid">
                    </div>
                </div>
            </div>
        </div>
    </div>
</section>

<?php require __DIR__ . '/inc/footer.php'; ?>
