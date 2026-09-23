<?php
session_start();
require __DIR__ . '/../config/conexion.php';
require __DIR__ . '/../config/permisos.php';

if (empty($_SESSION['usuario_id'])) {
    header('Location: login.php');
    exit;
}
exigir('podcasts', 'ver');

$page_title = 'Podcasts';
$active_menu = 'podcasts';

$stmt = $pdo->query("
    SELECT p.id, p.titulo, p.audio_url, p.enlace_externo, p.fecha_publicacion, p.estado,
           CONCAT_WS(' ', u.nombres, u.ap_paterno, u.ap_materno) AS creado_por
    FROM podcasts p
    LEFT JOIN usuarios u ON u.id = p.usuario_id
    ORDER BY p.fecha_publicacion DESC
");
$podcasts = $stmt->fetchAll();

require __DIR__ . '/../config/layout_top.php';
?>
                <div class="row">
                    <div class="col-12 d-flex justify-content-end mb-2">
                        <?php if (puede('podcasts', 'crear')): ?>
                        <a href="podcast_form.php" class="btn btn-primary btn-sm"><i class="fa fa-plus"></i> Nuevo podcast</a>
                        <?php endif; ?>
                    </div>
                    <?php if (empty($podcasts)): ?>
                    <div class="col-12">
                        <div class="card">
                            <div class="card-body text-center">Aun no hay podcasts registrados.</div>
                        </div>
                    </div>
                    <?php endif; ?>
                    <?php foreach ($podcasts as $p): ?>
                    <div class="col-lg-4 col-md-6">
                        <div class="card">
                            <div class="card-header d-flex justify-content-between align-items-center">
                                <h4 class="card-title mb-0"><?php echo htmlspecialchars($p['titulo']); ?></h4>
                                <?php if ($p['estado'] === 'publicado'): ?>
                                    <span class="badge badge-success">Publicado</span>
                                <?php else: ?>
                                    <span class="badge badge-secondary">Oculto</span>
                                <?php endif; ?>
                            </div>
                            <div class="card-body">
                                <?php if (!empty($p['audio_url'])): ?>
                                    <?php if (filter_var($p['audio_url'], FILTER_VALIDATE_URL)): ?>
                                        <audio controls src="<?php echo htmlspecialchars($p['audio_url']); ?>" class="w-100 mb-3"></audio>
                                    <?php else: ?>
                                        <audio controls src="../<?php echo htmlspecialchars($p['audio_url']); ?>" class="w-100 mb-3"></audio>
                                    <?php endif; ?>
                                <?php else: ?>
                                    <p class="text-muted mb-3"><i class="fa fa-exclamation-triangle"></i> Sin audio todavia.</p>
                                <?php endif; ?>
                                <p class="mb-1"><strong>Fecha:</strong> <?php echo htmlspecialchars($p['fecha_publicacion']); ?></p>
                                <p class="mb-1"><strong>Publicado por:</strong> <?php echo htmlspecialchars($p['creado_por'] ?? '—'); ?></p>
                                <?php if (!empty($p['enlace_externo'])): ?>
                                    <p class="mb-2"><a href="<?php echo htmlspecialchars($p['enlace_externo']); ?>" target="_blank"><i class="fa fa-external-link"></i> Ver enlace externo</a></p>
                                <?php endif; ?>
                                <?php if (puede('podcasts', 'editar')): ?>
                                <a href="podcast_form.php?id=<?php echo (int) $p['id']; ?>" class="btn btn-info btn-sm"><i class="fa fa-pencil"></i> Editar</a>
                                <?php endif; ?>
                                <?php if (puede('podcasts', 'alternar')): ?>
                                <form method="post" action="podcast_toggle.php" class="d-inline" onsubmit="return confirm('<?php echo $p['estado'] === 'publicado' ? '¿Ocultar' : '¿Mostrar'; ?> este podcast en el sitio publico?');">
                                    <input type="hidden" name="id" value="<?php echo (int) $p['id']; ?>">
                                    <button type="submit" class="btn <?php echo $p['estado'] === 'publicado' ? 'btn-secondary' : 'btn-success'; ?> btn-sm">
                                        <i class="fa <?php echo $p['estado'] === 'publicado' ? 'fa-eye-slash' : 'fa-eye'; ?>"></i> <?php echo $p['estado'] === 'publicado' ? 'Ocultar' : 'Mostrar'; ?>
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
