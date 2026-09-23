<?php
session_start();
require __DIR__ . '/../config/conexion.php';
require __DIR__ . '/../config/permisos.php';

if (empty($_SESSION['usuario_id'])) {
    header('Location: login.php');
    exit;
}
exigir('autores', 'ver');

$page_title = 'Autores';
$active_menu = 'autores';

$stmt = $pdo->query("
    SELECT a.id,
           CASE WHEN a.es_nickname = 1 AND a.nickname IS NOT NULL AND a.nickname <> ''
                THEN a.nickname
                ELSE CONCAT_WS(' ', a.nombres, a.ap_paterno, a.ap_materno)
           END AS nombre,
           COUNT(r.id) AS total_reportajes
    FROM autores a
    LEFT JOIN reportajes r ON r.autor_id = a.id
    GROUP BY a.id, a.nombres, a.ap_paterno, a.ap_materno, a.nickname, a.es_nickname
    ORDER BY a.nombres, a.ap_paterno
");
$autores = $stmt->fetchAll();

require __DIR__ . '/../config/layout_top.php';
?>
                <div class="row">
                    <div class="col-12">
                        <div class="card">
                            <div class="card-header d-flex justify-content-between align-items-center">
                                <h4 class="card-title">Autores</h4>
                                <a href="autor_form.php" class="btn btn-primary btn-sm"><i class="fa fa-plus"></i> Nuevo autor</a>
                            </div>
                            <div class="card-body">
                                <div class="table-responsive">
                                    <table class="table table-bordered verticle-middle">
                                        <thead>
                                            <tr>
                                                <th>Nombre</th>
                                                <th>Reportajes publicados</th>
                                                <th>Acciones</th>
                                            </tr>
                                        </thead>
                                        <tbody>
                                            <?php if (empty($autores)): ?>
                                            <tr>
                                                <td colspan="3" class="text-center">Aun no hay autores registrados.</td>
                                            </tr>
                                            <?php endif; ?>
                                            <?php foreach ($autores as $a): ?>
                                            <tr>
                                                <td><?php echo htmlspecialchars($a['nombre']); ?></td>
                                                <td><?php echo (int) $a['total_reportajes']; ?></td>
                                                <td>
                                                    <a href="autor_form.php?id=<?php echo (int) $a['id']; ?>" class="btn btn-info btn-sm"><i class="fa fa-pencil"></i></a>
                                                    <form method="post" action="autor_delete.php" class="d-inline" onsubmit="return confirm('¿Eliminar este autor?');">
                                                        <input type="hidden" name="id" value="<?php echo (int) $a['id']; ?>">
                                                        <button type="submit" class="btn btn-danger btn-sm"><i class="fa fa-trash"></i></button>
                                                    </form>
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
require __DIR__ . '/../config/layout_bottom.php';
