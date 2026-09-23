<?php
session_start();
require __DIR__ . '/../config/conexion.php';
require __DIR__ . '/../config/permisos.php';

if (empty($_SESSION['usuario_id'])) {
    header('Location: login.php');
    exit;
}
exigir('boletines', 'ver');

$page_title = 'Boletines';
$active_menu = 'boletines';
$extra_head = '
    <link href="../vendor/datatables/css/jquery.dataTables.min.css" rel="stylesheet">
';

$stmt = $pdo->query("
    SELECT b.id, b.numero_boletin, b.resumen, b.fecha_publicacion, b.estado, b.archivo_pdf,
           CONCAT_WS(' ', u.nombres, u.ap_paterno, u.ap_materno) AS creado_por
    FROM boletines b
    LEFT JOIN usuarios u ON u.id = b.usuario_id
    ORDER BY b.fecha_publicacion DESC, b.id DESC
");
$boletines = $stmt->fetchAll();

require __DIR__ . '/../config/layout_top.php';
?>
                <div class="row">
                    <div class="col-12">
                        <div class="card">
                            <div class="card-header d-flex justify-content-between align-items-center">
                                <h4 class="card-title">Listado de boletines</h4>
                                <?php if (puede('boletines', 'crear')): ?>
                                <a href="boletin_form.php" class="btn btn-primary btn-sm"><i class="fa fa-plus"></i> Nuevo boletin</a>
                                <?php endif; ?>
                            </div>
                            <div class="card-body">
                                <div class="table-responsive">
                                    <table id="tabla-boletines" class="display" style="width:100%">
                                        <thead>
                                            <tr>
                                                <th>No. Boletin</th>
                                                <th>Resumen</th>
                                                <th>Fecha</th>
                                                <th>Publicado por</th>
                                                <th>Estado</th>
                                                <th>PDF</th>
                                                <th>Acciones</th>
                                            </tr>
                                        </thead>
                                        <tbody>
                                            <?php foreach ($boletines as $b): ?>
                                            <tr>
                                                <td><?php echo htmlspecialchars((string) $b['numero_boletin']); ?></td>
                                                <td><?php echo htmlspecialchars(mb_strimwidth((string) $b['resumen'], 0, 100, '...')); ?></td>
                                                <td><?php echo htmlspecialchars($b['fecha_publicacion']); ?></td>
                                                <td><?php echo htmlspecialchars($b['creado_por'] ?? '—'); ?></td>
                                                <td>
                                                    <?php if ($b['estado'] === 'publicado'): ?>
                                                        <span class="badge badge-success">Publicado</span>
                                                    <?php else: ?>
                                                        <span class="badge badge-secondary">Oculto</span>
                                                    <?php endif; ?>
                                                </td>
                                                <td>
                                                    <?php if (!empty($b['archivo_pdf'])): ?>
                                                        <a href="../<?php echo htmlspecialchars($b['archivo_pdf']); ?>" target="_blank">Ver PDF</a>
                                                    <?php else: ?>
                                                        —
                                                    <?php endif; ?>
                                                </td>
                                                <td>
                                                    <?php if (puede('boletines', 'editar')): ?>
                                                    <a href="boletin_form.php?id=<?php echo (int) $b['id']; ?>" class="btn btn-info btn-sm"><i class="fa fa-pencil"></i></a>
                                                    <?php endif; ?>
                                                    <?php if (puede('boletines', 'alternar')): ?>
                                                    <form method="post" action="boletin_toggle.php" class="d-inline" onsubmit="return confirm('<?php echo $b['estado'] === 'publicado' ? '¿Ocultar' : '¿Mostrar'; ?> este boletin en el sitio publico?');">
                                                        <input type="hidden" name="id" value="<?php echo (int) $b['id']; ?>">
                                                        <button type="submit" class="btn <?php echo $b['estado'] === 'publicado' ? 'btn-secondary' : 'btn-success'; ?> btn-sm" title="<?php echo $b['estado'] === 'publicado' ? 'Ocultar' : 'Mostrar'; ?>">
                                                            <i class="fa <?php echo $b['estado'] === 'publicado' ? 'fa-eye-slash' : 'fa-eye'; ?>"></i>
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
            $("#tabla-boletines").DataTable({ language: dtLangEs, columnDefs: [{ orderable: false, targets: -1 }] });
        })(jQuery);
    </script>
';
require __DIR__ . '/../config/layout_bottom.php';
