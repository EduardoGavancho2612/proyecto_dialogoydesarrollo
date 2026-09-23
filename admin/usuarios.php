<?php
session_start();
require __DIR__ . '/../config/conexion.php';
require __DIR__ . '/../config/permisos.php';

if (empty($_SESSION['usuario_id'])) {
    header('Location: login.php');
    exit;
}
exigir('usuarios', 'ver');

$page_title = 'Usuarios';
$active_menu = 'usuarios';
$extra_head = '
    <link href="../vendor/datatables/css/jquery.dataTables.min.css" rel="stylesheet">
';

$stmt = $pdo->query("SELECT id, CONCAT_WS(' ', nombres, ap_paterno, ap_materno) AS nombre_completo, email, rol, created_at FROM usuarios ORDER BY nombres, ap_paterno");
$usuarios = $stmt->fetchAll();

require __DIR__ . '/../config/layout_top.php';
?>
                <div class="row">
                    <div class="col-12">
                        <div class="card">
                            <div class="card-header d-flex justify-content-between align-items-center">
                                <h4 class="card-title">Usuarios del panel</h4>
                                <a href="usuario_form.php" class="btn btn-primary btn-sm"><i class="fa fa-plus"></i> Nuevo usuario</a>
                            </div>
                            <div class="card-body">
                                <div class="table-responsive">
                                    <table id="tabla-usuarios" class="display" style="width:100%">
                                        <thead>
                                            <tr>
                                                <th>Nombre</th>
                                                <th>Email</th>
                                                <th>Rol</th>
                                                <th>Creado</th>
                                                <th>Acciones</th>
                                            </tr>
                                        </thead>
                                        <tbody>
                                            <?php foreach ($usuarios as $u): ?>
                                            <tr>
                                                <td><?php echo htmlspecialchars($u['nombre_completo']); ?></td>
                                                <td><?php echo htmlspecialchars($u['email']); ?></td>
                                                <td>
                                                    <?php
                                                    $rolBadge = $u['rol'] === 'admin' ? 'badge-primary' : ($u['rol'] === 'editor' ? 'badge-info' : 'badge-secondary');
                                                    ?>
                                                    <span class="badge <?php echo $rolBadge; ?>"><?php echo htmlspecialchars($u['rol']); ?></span>
                                                </td>
                                                <td><?php echo htmlspecialchars($u['created_at']); ?></td>
                                                <td>
                                                    <a href="usuario_form.php?id=<?php echo (int) $u['id']; ?>" class="btn btn-info btn-sm"><i class="fa fa-pencil"></i></a>
                                                    <?php if ((int) $u['id'] !== (int) $_SESSION['usuario_id']): ?>
                                                    <form method="post" action="usuario_delete.php" class="d-inline" onsubmit="return confirm('¿Eliminar este usuario?');">
                                                        <input type="hidden" name="id" value="<?php echo (int) $u['id']; ?>">
                                                        <button type="submit" class="btn btn-danger btn-sm"><i class="fa fa-trash"></i></button>
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
            $("#tabla-usuarios").DataTable({ language: dtLangEs, columnDefs: [{ orderable: false, targets: -1 }] });
        })(jQuery);
    </script>
';
require __DIR__ . '/../config/layout_bottom.php';
