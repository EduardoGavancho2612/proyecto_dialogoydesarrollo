<?php
session_start();
require __DIR__ . '/../config/conexion.php';
require __DIR__ . '/../config/permisos.php';

if (empty($_SESSION['usuario_id'])) {
    header('Location: login.php');
    exit;
}
exigir('videos', 'ver');

$page_title = 'Videos';
$active_menu = 'videos';

$stmt = $pdo->query("
    SELECT v.id, v.titulo, v.url_embed, v.fecha_publicacion, v.estado,
           CONCAT_WS(' ', u.nombres, u.ap_paterno, u.ap_materno) AS creado_por
    FROM videos v
    LEFT JOIN usuarios u ON u.id = v.usuario_id
    ORDER BY v.fecha_publicacion DESC
");
$videos = $stmt->fetchAll();

require __DIR__ . '/../config/layout_top.php';
?>
                <div class="row">
                    <div class="col-12 d-flex justify-content-end mb-2">
                        <?php if (puede('videos', 'crear')): ?>
                        <a href="video_form.php" class="btn btn-primary btn-sm"><i class="fa fa-plus"></i> Nuevo video</a>
                        <?php endif; ?>
                    </div>
                    <?php if (empty($videos)): ?>
                    <div class="col-12">
                        <div class="card">
                            <div class="card-body text-center">Aun no hay videos registrados.</div>
                        </div>
                    </div>
                    <?php endif; ?>
                    <?php foreach ($videos as $v): ?>
                    <div class="col-lg-4 col-md-6">
                        <div class="card">
                            <div class="card-header d-flex justify-content-between align-items-center">
                                <h4 class="card-title mb-0"><?php echo htmlspecialchars($v['titulo']); ?></h4>
                                <?php if ($v['estado'] === 'publicado'): ?>
                                    <span class="badge badge-success">Publicado</span>
                                <?php else: ?>
                                    <span class="badge badge-secondary">Oculto</span>
                                <?php endif; ?>
                            </div>
                            <div class="card-body">
                                <div class="embed-responsive embed-responsive-16by9 mb-3">
                                    <iframe class="embed-responsive-item" src="<?php echo htmlspecialchars($v['url_embed']); ?>" allowfullscreen></iframe>
                                </div>
                                <p class="mb-1"><strong>Fecha:</strong> <?php echo htmlspecialchars($v['fecha_publicacion']); ?></p>
                                <p class="mb-2"><strong>Publicado por:</strong> <?php echo htmlspecialchars($v['creado_por'] ?? '—'); ?></p>
                                <?php if (puede('videos', 'editar')): ?>
                                <a href="video_form.php?id=<?php echo (int) $v['id']; ?>" class="btn btn-info btn-sm"><i class="fa fa-pencil"></i> Editar</a>
                                <?php endif; ?>
                                <?php if (puede('videos', 'alternar')): ?>
                                <form method="post" action="video_toggle.php" class="d-inline" onsubmit="return confirm('<?php echo $v['estado'] === 'publicado' ? '¿Ocultar' : '¿Mostrar'; ?> este video en el sitio publico?');">
                                    <input type="hidden" name="id" value="<?php echo (int) $v['id']; ?>">
                                    <button type="submit" class="btn <?php echo $v['estado'] === 'publicado' ? 'btn-secondary' : 'btn-success'; ?> btn-sm">
                                        <i class="fa <?php echo $v['estado'] === 'publicado' ? 'fa-eye-slash' : 'fa-eye'; ?>"></i> <?php echo $v['estado'] === 'publicado' ? 'Ocultar' : 'Mostrar'; ?>
                                    </button>
                                </form>
                                <?php endif; ?>
                            </div>
                        </div>
                    </div>
                    <?php endforeach; ?>
                </div>
<?php
require __DIR__ . '/../config/layout_bottom.php';
