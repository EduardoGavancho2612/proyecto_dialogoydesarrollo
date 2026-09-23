<?php
require __DIR__ . '/inc/bootstrap.php';

$page_title = 'Sobre D&D - Diálogo y Desarrollo Perú';
$page_description = 'Conoce a Diálogo y Desarrollo Perú: periodismo independiente sobre minería, canon y desarrollo territorial.';
$active_nav = 'sobre';
require __DIR__ . '/inc/header.php';

breadcrumb('Sobre D&D', ['Inicio' => 'index.php', 'Sobre D&D' => null]);
?>

<section class="w3l-homeblock3 py-5">
    <div class="container py-lg-3">
        <div class="row">
            <div class="col-lg-9 mx-auto">
                <h5 class="title-small mb-2">DDP Noticias</h5>
                <h3 class="title-big mb-4">Diálogo y Desarrollo Perú</h3>
                <p>Somos un espacio de periodismo independiente que busca visibilizar las acciones de diálogo en el país desde una mirada constructiva. Producimos reportajes, boletines, podcasts y videos sobre minería, canon, desarrollo territorial y gestión pública.</p>

                <h4 class="mt-5 mb-3">Qué hacemos</h4>
                <ul>
                    <li><strong>Reportajes de investigación</strong> sobre el uso de recursos públicos y el impacto de la actividad minera formal e informal.</li>
                    <li><strong>Boletín NTEP</strong>, un resumen periódico de noticias sobre territorio, economía y proyectos de inversión.</li>
                    <li><strong>Podcast y videos</strong> con análisis de especialistas y testimonios de las comunidades.</li>
                    <li><strong>Cobertura de actualidad</strong> con enlaces a fuentes verificadas.</li>
                </ul>

                <h4 class="mt-5 mb-3">Nuestro enfoque</h4>
                <p>Creemos que el periodismo puede aportar al diálogo entre el Estado, las empresas y la ciudadanía. Por eso priorizamos la data verificable, las voces locales y el seguimiento de los compromisos asumidos en los espacios de concertación.</p>

                <h4 class="mt-5 mb-3" id="footer">Contacto</h4>
                <p>
                    Correo: <a href="mailto:info@dialogoydesarrollo.com.pe">info@dialogoydesarrollo.com.pe</a><br>
                    Redes:
                    <a target="_blank" rel="noopener" href="https://www.facebook.com/DialogoyDesarrolloPeru">Facebook</a>,
                    <a target="_blank" rel="noopener" href="https://www.instagram.com/dialogo.y.desarrollo/">Instagram</a>,
                    <a target="_blank" rel="noopener" href="https://www.tiktok.com/@dialogo.y.desarrollo">TikTok</a>.
                </p>
            </div>
        </div>
    </div>
</section>

<?php require __DIR__ . '/inc/footer.php'; ?>
