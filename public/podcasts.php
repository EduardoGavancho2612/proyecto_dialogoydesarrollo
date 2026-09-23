<?php
require __DIR__ . '/inc/bootstrap.php';

$podcasts = $pdo->query("
    SELECT id, titulo, audio_url, enlace_externo, fecha_publicacion
    FROM podcasts
    WHERE estado = 'publicado'
    ORDER BY fecha_publicacion DESC, id DESC
")->fetchAll();

$page_title = 'Podcast - Diálogo y Desarrollo Perú';
$page_description = 'Episodios de podcast de Diálogo y Desarrollo Perú sobre periodismo de investigación.';
$active_nav = 'podcast';
require __DIR__ . '/inc/header.php';

breadcrumb('Podcast', ['Inicio' => 'index.php', 'Podcast' => null]);
?>

<div class="grids-block-5 py-5">
    <section class="py-lg-4 py-md-3">
        <div class="container">
            <div class="row">
                <?php if (empty($podcasts)): ?>
                    <div class="col-12"><p class="text-center">Aún no hay podcasts publicados.</p></div>
                <?php endif; ?>
                <?php foreach ($podcasts as $i => $p): $play = podcast_playback($p['audio_url']); ?>
                <div class="col-lg-4 col-md-6 grids5-info <?php echo $i >= 3 ? 'mt-5' : 'mt-md-0 mt-5'; ?>">
                    <?php if ($play['tipo'] === 'file'): ?>
                        <a href="#" class="d-block ddp-podcast-thumb"
                           data-podcast-src="<?php echo e(media_url($play['src'])); ?>"
                           data-podcast-title="<?php echo e($p['titulo']); ?>"
                           data-podcast-share="<?php echo e($p['enlace_externo'] ?: media_url($play['src'])); ?>">
                            <img src="assets/site/podcast.png" alt="<?php echo e($p['titulo']); ?>" class="img-fluid">
                            <span class="ddp-play-overlay"><span class="fa fa-play"></span></span>
                        </a>
                    <?php elseif ($play['tipo'] === 'spotify'): ?>
                        <iframe src="<?php echo e($play['src']); ?>" width="100%" height="232" frameborder="0" scrolling="no"
                                allow="autoplay; clipboard-write; encrypted-media; fullscreen; picture-in-picture" loading="lazy"
                                title="<?php echo e($p['titulo']); ?>"></iframe>
                    <?php else: ?>
                        <img src="assets/site/podcast.png" alt="<?php echo e($p['titulo']); ?>" class="img-fluid">
                    <?php endif; ?>
                    <div class="blog-info">
                        <h5><?php echo e(fecha_es($p['fecha_publicacion'])); ?></h5>
                        <h4><?php echo e($p['titulo']); ?></h4>
                        <?php if (!empty($p['enlace_externo'])): ?>
                            <a target="_blank" rel="noopener" href="<?php echo e($p['enlace_externo']); ?>" class="btn mt-4 p-0">Ver en la plataforma <span class="fa fa-arrow-right"></span></a>
                        <?php endif; ?>
                    </div>
                </div>
                <?php endforeach; ?>
            </div>
        </div>
    </section>
</div>

<?php require __DIR__ . '/inc/footer.php'; ?>
