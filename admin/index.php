<?php
session_start();
require __DIR__ . '/../config/conexion.php';

if (empty($_SESSION['usuario_id'])) {
    header('Location: login.php');
    exit;
}

$page_title = 'Dashboard';
$active_menu = 'dashboard';

// --- Conteos por seccion ---
$stats = $pdo->query('
    SELECT
        (SELECT COUNT(*) FROM reportajes) AS reportajes,
        (SELECT COUNT(*) FROM boletines) AS boletines,
        (SELECT COUNT(*) FROM noticias) AS noticias,
        (SELECT COUNT(*) FROM podcasts) AS podcasts,
        (SELECT COUNT(*) FROM videos) AS videos,
        (SELECT COUNT(*) FROM autores) AS autores,
        (SELECT COUNT(*) FROM usuarios) AS usuarios,
        (SELECT COUNT(*) FROM reportajes WHERE es_destacado = 1) AS destacados
')->fetch();

// --- Ranking de autores por cantidad de reportajes ---
$rankingAutores = $pdo->query("
    SELECT CASE WHEN a.es_nickname = 1 AND a.nickname IS NOT NULL AND a.nickname <> ''
                THEN a.nickname
                ELSE CONCAT_WS(' ', a.nombres, a.ap_paterno, a.ap_materno)
           END AS nombre,
           COUNT(r.id) AS total
    FROM autores a
    LEFT JOIN reportajes r ON r.autor_id = a.id
    GROUP BY a.id, a.nombres, a.ap_paterno, a.ap_materno, a.nickname, a.es_nickname
    ORDER BY total DESC, nombre ASC
")->fetchAll();
$topAutor = $rankingAutores[0] ?? null;

// --- Contenido reciente (union de las 5 secciones de contenido) ---
$reciente = $pdo->query('
    SELECT "Reportaje" AS tipo, titulo, fecha_publicacion FROM reportajes
    UNION ALL
    SELECT "Boletin" AS tipo, CONCAT("Boletin #", numero_boletin) AS titulo, fecha_publicacion FROM boletines
    UNION ALL
    SELECT "Noticia" AS tipo, titulo, fecha_publicacion FROM noticias
    UNION ALL
    SELECT "Podcast" AS tipo, titulo, fecha_publicacion FROM podcasts
    UNION ALL
    SELECT "Video" AS tipo, titulo, fecha_publicacion FROM videos
    ORDER BY fecha_publicacion DESC
    LIMIT 10
')->fetchAll();

$tipoBadge = [
    'Reportaje' => 'badge-primary',
    'Boletin' => 'badge-info',
    'Noticia' => 'badge-warning',
    'Podcast' => 'badge-success',
    'Video' => 'badge-danger',
];

$distribucionLabels = ['Reportajes', 'Boletines', 'Noticias', 'Podcasts', 'Videos'];
$distribucionData = [
    (int) $stats['reportajes'], (int) $stats['boletines'], (int) $stats['noticias'],
    (int) $stats['podcasts'], (int) $stats['videos'],
];

$autoresLabels = array_column($rankingAutores, 'nombre');
$autoresData = array_map('intval', array_column($rankingAutores, 'total'));

require __DIR__ . '/../config/layout_top.php';
?>
                <div class="row">
                    <div class="col-xl-3 col-lg-6 col-md-6 mb-4">
                        <div class="card h-100 mb-0">
                            <div class="card-body d-flex align-items-center">
                                <i class="fa fa-newspaper-o fa-2x text-primary mr-3"></i>
                                <div>
                                    <h3 class="mb-0"><?php echo (int) $stats['reportajes']; ?></h3>
                                    <p class="mb-0 text-muted">Reportajes</p>
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="col-xl-3 col-lg-6 col-md-6 mb-4">
                        <div class="card h-100 mb-0">
                            <div class="card-body d-flex align-items-center">
                                <i class="fa fa-bullhorn fa-2x text-info mr-3"></i>
                                <div>
                                    <h3 class="mb-0"><?php echo (int) $stats['boletines']; ?></h3>
                                    <p class="mb-0 text-muted">Boletines</p>
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="col-xl-3 col-lg-6 col-md-6 mb-4">
                        <div class="card h-100 mb-0">
                            <div class="card-body d-flex align-items-center">
                                <i class="fa fa-globe fa-2x text-warning mr-3"></i>
                                <div>
                                    <h3 class="mb-0"><?php echo (int) $stats['noticias']; ?></h3>
                                    <p class="mb-0 text-muted">Noticias</p>
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="col-xl-3 col-lg-6 col-md-6 mb-4">
                        <div class="card h-100 mb-0">
                            <div class="card-body d-flex align-items-center">
                                <i class="fa fa-microphone fa-2x text-success mr-3"></i>
                                <div>
                                    <h3 class="mb-0"><?php echo (int) $stats['podcasts']; ?></h3>
                                    <p class="mb-0 text-muted">Podcasts</p>
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="col-xl-3 col-lg-6 col-md-6 mb-4">
                        <div class="card h-100 mb-0">
                            <div class="card-body d-flex align-items-center">
                                <i class="fa fa-video-camera fa-2x text-danger mr-3"></i>
                                <div>
                                    <h3 class="mb-0"><?php echo (int) $stats['videos']; ?></h3>
                                    <p class="mb-0 text-muted">Videos</p>
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="col-xl-3 col-lg-6 col-md-6 mb-4">
                        <div class="card h-100 mb-0">
                            <div class="card-body d-flex align-items-center">
                                <i class="fa fa-user fa-2x text-info mr-3"></i>
                                <div>
                                    <h3 class="mb-0"><?php echo (int) $stats['autores']; ?></h3>
                                    <p class="mb-0 text-muted">Autores</p>
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="col-xl-3 col-lg-6 col-md-6 mb-4">
                        <div class="card h-100 mb-0">
                            <div class="card-body d-flex align-items-center">
                                <i class="fa fa-users fa-2x text-warning mr-3"></i>
                                <div>
                                    <h3 class="mb-0"><?php echo (int) $stats['usuarios']; ?></h3>
                                    <p class="mb-0 text-muted">Usuarios del panel</p>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="row">
                    <div class="col-lg-4">
                        <div class="card">
                            <div class="card-header">
                                <h4 class="card-title">Autor destacado</h4>
                            </div>
                            <div class="card-body text-center">
                                <?php if ($topAutor && (int) $topAutor['total'] > 0): ?>
                                    <h2 class="mb-0"><?php echo htmlspecialchars($topAutor['nombre']); ?></h2>
                                    <p class="text-muted mb-0">Con <?php echo (int) $topAutor['total']; ?> reportaje(s) publicados</p>
                                <?php else: ?>
                                    <p class="text-muted mb-0">Aun no hay reportajes asignados a un autor.</p>
                                <?php endif; ?>
                            </div>
                        </div>
                        <div class="card">
                            <div class="card-header">
                                <h4 class="card-title">Reportajes destacados</h4>
                            </div>
                            <div class="card-body text-center">
                                <h2 class="mb-0"><?php echo (int) $stats['destacados']; ?></h2>
                                <p class="text-muted mb-0">de <?php echo (int) $stats['reportajes']; ?> reportaje(s) totales</p>
                            </div>
                        </div>
                    </div>
                    <div class="col-lg-8">
                        <div class="card">
                            <div class="card-header">
                                <h4 class="card-title">Reportajes por autor</h4>
                            </div>
                            <div class="card-body">
                                <?php if (empty($autoresLabels)): ?>
                                    <p class="text-muted mb-0 text-center">Aun no hay autores registrados.</p>
                                <?php else: ?>
                                    <canvas id="chart-autores" height="140"></canvas>
                                <?php endif; ?>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="row">
                    <div class="col-lg-5">
                        <div class="card">
                            <div class="card-header">
                                <h4 class="card-title">Distribucion de contenido</h4>
                            </div>
                            <div class="card-body">
                                <?php if (array_sum($distribucionData) === 0): ?>
                                    <p class="text-muted mb-0 text-center">Aun no hay contenido publicado.</p>
                                <?php else: ?>
                                    <canvas id="chart-distribucion" height="220"></canvas>
                                <?php endif; ?>
                            </div>
                        </div>
                    </div>
                    <div class="col-lg-7">
                        <div class="card">
                            <div class="card-header">
                                <h4 class="card-title">Contenido reciente</h4>
                            </div>
                            <div class="card-body">
                                <div class="table-responsive">
                                    <table class="table table-bordered verticle-middle">
                                        <thead>
                                            <tr>
                                                <th>Tipo</th>
                                                <th>Titulo</th>
                                                <th>Fecha</th>
                                            </tr>
                                        </thead>
                                        <tbody>
                                            <?php if (empty($reciente)): ?>
                                            <tr>
                                                <td colspan="3" class="text-center">Aun no hay contenido publicado.</td>
                                            </tr>
                                            <?php endif; ?>
                                            <?php foreach ($reciente as $item): ?>
                                            <tr>
                                                <td><span class="badge <?php echo $tipoBadge[$item['tipo']] ?? 'badge-secondary'; ?>"><?php echo htmlspecialchars($item['tipo']); ?></span></td>
                                                <td><?php echo htmlspecialchars($item['titulo']); ?></td>
                                                <td><?php echo htmlspecialchars($item['fecha_publicacion']); ?></td>
                                            </tr>
                                            <?php endforeach; ?>
                                        </tbody>
                                    </table>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
<?php
$extra_scripts = '
    <script src="../vendor/chart.js/Chart.bundle.min.js"></script>
    <script>
        (function() {
            var autoresLabels = ' . json_encode($autoresLabels, JSON_UNESCAPED_UNICODE) . ';
            var autoresData = ' . json_encode($autoresData) . ';
            var distribucionLabels = ' . json_encode($distribucionLabels, JSON_UNESCAPED_UNICODE) . ';
            var distribucionData = ' . json_encode($distribucionData) . ';

            var elAutores = document.getElementById("chart-autores");
            if (elAutores) {
                new Chart(elAutores.getContext("2d"), {
                    type: "bar",
                    data: {
                        labels: autoresLabels,
                        datasets: [{
                            label: "Reportajes",
                            data: autoresData,
                            backgroundColor: "#6b51df"
                        }]
                    },
                    options: {
                        legend: { display: false },
                        scales: {
                            yAxes: [{ ticks: { beginAtZero: true, precision: 0 } }]
                        }
                    }
                });
            }

            var elDist = document.getElementById("chart-distribucion");
            if (elDist) {
                Chart.pluginService.register({
                    afterDraw: function(chart) {
                        if (chart.config.type !== "doughnut") return;
                        var ctx = chart.ctx;
                        chart.data.datasets.forEach(function(dataset, i) {
                            var meta = chart.getDatasetMeta(i);
                            meta.data.forEach(function(element, index) {
                                var value = dataset.data[index];
                                if (!value) return;
                                var position = element.tooltipPosition();
                                ctx.save();
                                ctx.font = "bold 13px Arial";
                                ctx.fillStyle = "#fff";
                                ctx.textAlign = "center";
                                ctx.textBaseline = "middle";
                                ctx.fillText(value, position.x, position.y);
                                ctx.restore();
                            });
                        });
                    }
                });

                new Chart(elDist.getContext("2d"), {
                    type: "doughnut",
                    data: {
                        labels: distribucionLabels,
                        datasets: [{
                            data: distribucionData,
                            backgroundColor: ["#6b51df", "#3f97e3", "#f3c44e", "#3ac47d", "#d92550"]
                        }]
                    },
                    options: {
                        legend: {
                            position: "bottom",
                            labels: {
                                generateLabels: function(chart) {
                                    var data = chart.data;
                                    return data.labels.map(function(label, i) {
                                        return {
                                            text: label + " (" + data.datasets[0].data[i] + ")",
                                            fillStyle: data.datasets[0].backgroundColor[i],
                                            index: i
                                        };
                                    });
                                }
                            }
                        }
                    }
                });
            }
        })();
    </script>
';
require __DIR__ . '/../config/layout_bottom.php';
