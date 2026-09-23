<?php
session_start();
require __DIR__ . '/../config/conexion.php';
require __DIR__ . '/../config/permisos.php';

if (empty($_SESSION['usuario_id'])) {
    header('Location: login.php');
    exit;
}
exigir('archivos', 'ver');

$page_title = 'Archivos';
$active_menu = 'archivos';
$extra_head = '
    <link href="../vendor/datatables/css/jquery.dataTables.min.css" rel="stylesheet">
';

$stmt = $pdo->query('
    SELECT "Reportaje" AS origen, titulo AS nombre, pdf_adjunto AS archivo, fecha_publicacion
    FROM reportajes
    WHERE pdf_adjunto IS NOT NULL AND pdf_adjunto <> ""
    UNION ALL
    SELECT "Boletin" AS origen, CONCAT("Boletin #", numero_boletin) AS nombre, archivo_pdf AS archivo, fecha_publicacion
    FROM boletines
    WHERE archivo_pdf IS NOT NULL AND archivo_pdf <> ""
    ORDER BY fecha_publicacion DESC
');
$archivos = $stmt->fetchAll();

require __DIR__ . '/../config/layout_top.php';
?>
                <div class="row">
                    <div class="col-12">
                        <div class="card">
                            <div class="card-header">
                                <h4 class="card-title">Archivos PDF (reportajes y boletines)</h4>
                            </div>
                            <div class="card-body pb-0">
                                <div class="alert alert-info mb-0">
                                    Estos archivos pertenecen a un reportaje o boletin especifico. Para subir o cambiar un PDF, edita el
                                    <a href="reportajes.php">reportaje</a> o <a href="boletines.php">boletin</a> correspondiente.
                                </div>
                            </div>
                            <div class="card-body">
                                <div class="table-responsive">
                                    <table id="tabla-archivos" class="display" style="width:100%">
                                        <thead>
                                            <tr>
                                                <th>Origen</th>
                                                <th>Nombre</th>
                                                <th>Fecha</th>
                                                <th>Archivo</th>
                                            </tr>
                                        </thead>
                                        <tbody>
                                            <?php foreach ($archivos as $a): ?>
                                            <tr>
                                                <td><?php echo htmlspecialchars($a['origen']); ?></td>
                                                <td><?php echo htmlspecialchars($a['nombre']); ?></td>
                                                <td><?php echo htmlspecialchars($a['fecha_publicacion']); ?></td>
                                                <td><a href="../<?php echo htmlspecialchars($a['archivo']); ?>" target="_blank">Descargar</a></td>
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
    <script src="../vendor/datatables/js/jquery.dataTables.min.js"></script>
    <script src="../js/es-datatable.js"></script>
    <script>
        (function($) {
            $("#tabla-archivos").DataTable({ language: dtLangEs });
        })(jQuery);
    </script>
';
require __DIR__ . '/../config/layout_bottom.php';
