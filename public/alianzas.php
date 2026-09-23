<?php
require __DIR__ . '/inc/bootstrap.php';

$page_title = 'Alianzas - Diálogo y Desarrollo Perú';
$page_description = 'Alianzas de Diálogo y Desarrollo Perú con instituciones, medios y organizaciones de la sociedad civil.';
$active_nav = 'alianzas';
require __DIR__ . '/inc/header.php';

breadcrumb('Alianzas', ['Inicio' => 'index.php', 'Alianzas' => null]);

$alianzas = [
    ['Cooperación para el Desarrollo Territorial', 'Trabajamos junto a organizaciones que impulsan proyectos de agua potable, saneamiento e infraestructura en zonas rurales del país.'],
    ['Red de Periodismo Independiente', 'Compartimos investigaciones y coberturas con medios y colectivos que promueven la transparencia y el acceso a la información pública.'],
    ['Academia e Investigación', 'Colaboramos con universidades y centros de estudio para analizar el impacto del canon, las regalías y la actividad minera en el desarrollo local.'],
    ['Organizaciones de la Sociedad Civil', 'Sumamos esfuerzos con asociaciones comunitarias, mesas de diálogo y frentes de defensa ambiental de distintas regiones.'],
];
?>

<section class="w3l-homeblock3 py-5">
    <div class="container py-lg-3">
        <div class="row">
            <div class="col-lg-9 mx-auto text-center">
                <h5 class="title-small mb-2">DyD Perú</h5>
                <h3 class="title-big mb-4">Alianzas estratégicas</h3>
                <p>Diálogo y Desarrollo Perú construye alianzas con instituciones públicas y privadas, medios de comunicación, universidades y organizaciones de la sociedad civil. Estas alianzas nos permiten ampliar la cobertura de nuestras investigaciones, fortalecer los espacios de diálogo y acercar información útil a las comunidades donde trabajamos.</p>
            </div>
        </div>

        <div class="row mt-lg-5 mt-4">
            <?php foreach ($alianzas as $i => [$titulo, $texto]): ?>
            <div class="col-lg-6 grids5-info <?php echo $i >= 2 ? 'mt-lg-5 mt-4' : 'mt-4 mt-lg-0'; ?>">
                <div class="area-box">
                    <h4 class="mb-2"><?php echo e($titulo); ?></h4>
                    <p><?php echo e($texto); ?></p>
                </div>
            </div>
            <?php endforeach; ?>
        </div>

        <div class="text-center mt-lg-5 mt-4">
            <p>¿Tu organización quiere sumarse? Escríbenos a
                <a href="mailto:info@dialogoydesarrollo.com.pe">info@dialogoydesarrollo.com.pe</a>.
            </p>
        </div>
    </div>
</section>

<?php require __DIR__ . '/inc/footer.php'; ?>
