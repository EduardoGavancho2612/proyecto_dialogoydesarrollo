<?php
session_start();
require __DIR__ . '/../config/conexion.php';
require __DIR__ . '/../config/permisos.php';

if (empty($_SESSION['usuario_id'])) {
    header('Location: login.php');
    exit;
}
exigir('reportajes', 'ver');

$page_title = 'Reportajes';
$active_menu = 'reportajes';
$extra_head = '
    <link href="../vendor/datatables/css/jquery.dataTables.min.css" rel="stylesheet">
';

$stmt = $pdo->query("
    SELECT r.id, r.titulo, r.resumen_corto, r.fecha_publicacion, r.es_destacado, r.estado, r.pdf_adjunto,
           CASE WHEN a.es_nickname = 1 AND a.nickname IS NOT NULL AND a.nickname <> ''
                THEN a.nickname
                ELSE CONCAT_WS(' ', a.nombres, a.ap_paterno, a.ap_materno)
           END AS autor,
           CONCAT_WS(' ', u.nombres, u.ap_paterno, u.ap_materno) AS creado_por
    FROM reportajes r
    LEFT JOIN autores a ON a.id = r.autor_id
    LEFT JOIN usuarios u ON u.id = r.usuario_id
    ORDER BY r.fecha_publicacion DESC
");
$reportajes = $stmt->fetchAll();

require __DIR__ . '/../config/layout_top.php';
?>
                <div class="row">
                    <div class="col-12">
                        <div class="card">
                            <div class="card-header d-flex justify-content-between align-items-center">
                                <h4 class="card-title">Listado de reportajes</h4>
                                <?php if (puede('reportajes', 'crear')): ?>
                                <a href="reportaje_form.php" class="btn btn-primary btn-sm"><i class="fa fa-plus"></i> Nuevo reportaje</a>
                                <?php endif; ?>
                            </div>
                            <div class="card-body">
                                <div class="table-responsive">
                                    <table id="tabla-reportajes" class="display" style="width:100%">
                                        <thead>
                                            <tr>
                                                <th>Titulo</th>
                                                <th>Autor</th>
                                                <th>Resumen</th>
                                                <th>Fecha</th>
                                                <th>Destacado</th>
                                                <th>Estado</th>
                                                <th>PDF</th>
                                                <th>Acciones</th>
                                            </tr>
                                        </thead>
                                        <tbody>
                                            <?php foreach ($reportajes as $r): ?>
                                            <tr>
                                                <td><?php echo htmlspecialchars($r['titulo']); ?></td>
                                                <td><?php echo htmlspecialchars($r['autor'] ?? '—'); ?></td>
                                                <td><?php echo htmlspecialchars(mb_strimwidth((string) $r['resumen_corto'], 0, 80, '...')); ?></td>
                                                <td><?php echo htmlspecialchars($r['fecha_publicacion']); ?></td>
                                                <td>
                                                    <?php if ($r['es_destacado']): ?>
                                                        <span class="badge badge-success">Si</span>
                                                    <?php else: ?>
                                                        <span class="badge badge-secondary">No</span>
                                                    <?php endif; ?>
                                                </td>
                                                <td>
                                                    <?php if ($r['estado'] === 'publicado'): ?>
                                                        <span class="badge badge-success">Publicado</span>
                                                    <?php else: ?>
                                                        <span class="badge badge-secondary">Oculto</span>
                                                    <?php endif; ?>
                                                </td>
                                                <td>
                                                    <?php if (!empty($r['pdf_adjunto'])): ?>
                                                        <a href="../<?php echo htmlspecialchars($r['pdf_adjunto']); ?>" target="_blank">Ver PDF</a>
                                                    <?php else: ?>
                                                        —
                                                    <?php endif; ?>
                                                </td>
                                                <td>
                                                    <?php if (puede('reportajes', 'editar')): ?>
                                                    <a href="reportaje_form.php?id=<?php echo (int) $r['id']; ?>" class="btn btn-info btn-sm"><i class="fa fa-pencil"></i></a>
                                                    <?php endif; ?>
                                                    <?php if (puede('reportajes', 'alternar')): ?>
                                                    <form method="post" action="reportaje_toggle.php" class="d-inline" onsubmit="return confirm('<?php echo $r['estado'] === 'publicado' ? '¿Ocultar' : '¿Mostrar'; ?> este reportaje en el sitio publico?');">
                                                        <input type="hidden" name="id" value="<?php echo (int) $r['id']; ?>">
                                                        <button type="submit" class="btn <?php echo $r['estado'] === 'publicado' ? 'btn-secondary' : 'btn-success'; ?> btn-sm" title="<?php echo $r['estado'] === 'publicado' ? 'Ocultar' : 'Mostrar'; ?>">
                                                            <i class="fa <?php echo $r['estado'] === 'publicado' ? 'fa-eye-slash' : 'fa-eye'; ?>"></i>
                                                        </button>
                                                    </form>
                                                    <?php endif; ?>
                                                </td>
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
            $("#tabla-reportajes").DataTable({ language: dtLangEs, columnDefs: [{ orderable: false, targets: -1 }] });
        })(jQuery);
    </script>
';
require __DIR__ . '/../config/layout_bottom.php';
