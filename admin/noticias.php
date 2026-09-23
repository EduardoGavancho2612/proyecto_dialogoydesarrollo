<?php
session_start();
require __DIR__ . '/../config/conexion.php';
require __DIR__ . '/../config/permisos.php';

if (empty($_SESSION['usuario_id'])) {
    header('Location: login.php');
    exit;
}
exigir('noticias', 'ver');

$page_title = 'Noticias';
$active_menu = 'noticias';
$extra_head = '
    <link href="../vendor/datatables/css/jquery.dataTables.min.css" rel="stylesheet">
';

$stmt = $pdo->query("
    SELECT n.id, n.titulo, n.link_externo, n.fecha_publicacion, n.estado,
           CONCAT_WS(' ', u.nombres, u.ap_paterno, u.ap_materno) AS creado_por
    FROM noticias n
    LEFT JOIN usuarios u ON u.id = n.usuario_id
    ORDER BY n.fecha_publicacion DESC
");
$noticias = $stmt->fetchAll();

require __DIR__ . '/../config/layout_top.php';
?>
                <div class="row">
                    <div class="col-12">
                        <div class="card">
                            <div class="card-header d-flex justify-content-between align-items-center">
                                <h4 class="card-title">Listado de noticias</h4>
                                <?php if (puede('noticias', 'crear')): ?>
                                <a href="noticia_form.php" class="btn btn-primary btn-sm"><i class="fa fa-plus"></i> Nueva noticia</a>
                                <?php endif; ?>
                            </div>
                            <div class="card-body">
                                <div class="table-responsive">
                                    <table id="tabla-noticias" class="display" style="width:100%">
                                        <thead>
                                            <tr>
                                                <th>Titulo</th>
                                                <th>Enlace externo</th>
                                                <th>Fecha</th>
                                                <th>Publicado por</th>
                                                <th>Estado</th>
                                                <th>Acciones</th>
                                            </tr>
                                        </thead>
                                        <tbody>
                                            <?php foreach ($noticias as $n): ?>
                                            <tr>
                                                <td><?php echo htmlspecialchars($n['titulo']); ?></td>
                                                <td><a href="<?php echo htmlspecialchars($n['link_externo']); ?>" target="_blank" rel="noopener">Ver enlace</a></td>
                                                <td><?php echo htmlspecialchars($n['fecha_publicacion']); ?></td>
                                                <td><?php echo htmlspecialchars($n['creado_por'] ?? '—'); ?></td>
                                                <td>
                                                    <?php if ($n['estado'] === 'publicado'): ?>
                                                        <span class="badge badge-success">Publicado</span>
                                                    <?php else: ?>
                                                        <span class="badge badge-secondary">Oculto</span>
                                                    <?php endif; ?>
                                                </td>
                                                <td>
                                                    <?php if (puede('noticias', 'editar')): ?>
                                                    <a href="noticia_form.php?id=<?php echo (int) $n['id']; ?>" class="btn btn-info btn-sm"><i class="fa fa-pencil"></i></a>
                                                    <?php endif; ?>
                                                    <?php if (puede('noticias', 'alternar')): ?>
                                                    <form method="post" action="noticia_toggle.php" class="d-inline" onsubmit="return confirm('<?php echo $n['estado'] === 'publicado' ? '¿Ocultar' : '¿Mostrar'; ?> esta noticia en el sitio publico?');">
                                                        <input type="hidden" name="id" value="<?php echo (int) $n['id']; ?>">
                                                        <button type="submit" class="btn <?php echo $n['estado'] === 'publicado' ? 'btn-secondary' : 'btn-success'; ?> btn-sm" title="<?php echo $n['estado'] === 'publicado' ? 'Ocultar' : 'Mostrar'; ?>">
                                                            <i class="fa <?php echo $n['estado'] === 'publicado' ? 'fa-eye-slash' : 'fa-eye'; ?>"></i>
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
            $("#tabla-noticias").DataTable({ language: dtLangEs, columnDefs: [{ orderable: false, targets: -1 }] });
        })(jQuery);
    </script>
';
require __DIR__ . '/../config/layout_bottom.php';
