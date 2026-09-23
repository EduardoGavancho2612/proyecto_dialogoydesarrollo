<?php
require __DIR__ . '/inc/bootstrap.php';

$boletines = $pdo->query("
    SELECT numero_boletin, resumen, foto_portada, archivo_pdf, fecha_publicacion
    FROM boletines
    WHERE estado = 'publicado'
    ORDER BY fecha_publicacion DESC, id DESC
")->fetchAll();

$page_title = 'Boletines NTEP - Diálogo y Desarrollo Perú';
$page_description = 'Boletín NTEP: resúmenes periódicos de noticias sobre territorio, economía y proyectos de inversión en el Perú.';
$active_nav = 'boletin';
require __DIR__ . '/inc/header.php';

breadcrumb('Boletines NTEP', ['Inicio' => 'index.php', 'Boletines' => null]);
?>

<div class="grids-block-5 py-5">
    <section class="py-lg-4 py-md-3">
        <div class="container">
            <div class="row">
                <?php if (empty($boletines)): ?>
                    <div class="col-12"><p class="text-center">Aún no hay boletines publicados.</p></div>
                <?php endif; ?>
                <?php foreach ($boletines as $i => $b): $pdf = e(media_url($b['archivo_pdf'])); ?>
                <div class="col-lg-4 col-md-6 grids5-info <?php echo $i >= 3 ? 'mt-5' : 'mt-md-0 mt-5'; ?>">
                    <a target="_blank" rel="noopener" href="<?php echo $pdf; ?>" class="d-block">
                        <img src="<?php echo e(media_url($b['foto_portada'], 'assets/site/boletin-ntep-45.png')); ?>" alt="Boletín NTEP N° <?php echo e($b['numero_boletin']); ?>" class="img-fluid">
                    </a>
                    <div class="blog-info">
                        <h5><?php echo e(fecha_es($b['fecha_publicacion'])); ?></h5>
                        <h4><a target="_blank" rel="noopener" href="<?php echo $pdf; ?>" class="d-block">Boletín NTEP N° <?php echo e($b['numero_boletin']); ?></a></h4>
                        <?php if (!empty($b['resumen'])): ?>
                            <p><?php echo e(resumen($b['resumen'], 140)); ?></p>
                        <?php endif; ?>
                        <a target="_blank" rel="noopener" href="<?php echo $pdf; ?>" class="btn mt-4 p-0">Ver boletín <span class="fa fa-arrow-right"></span></a>
                    </div>
                </div>
                <?php endforeach; ?>
            </div>
        </div>
    </section>
</div>

<?php require __DIR__ . '/inc/footer.php'; ?>
