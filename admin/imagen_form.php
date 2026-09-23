<?php
session_start();
require __DIR__ . '/../config/conexion.php';
require __DIR__ . '/../config/upload_helper.php';
require __DIR__ . '/../config/permisos.php';

if (empty($_SESSION['usuario_id'])) {
    header('Location: login.php');
    exit;
}
exigir('reportajes', 'editar');

$id = isset($_GET['id']) ? (int) $_GET['id'] : 0;
$foto = ['reportaje_id' => (int) ($_GET['reportaje_id'] ?? 0), 'url_foto' => '', 'descripcion' => '', 'orden' => 0];
$errors = [];

if ($id) {
    $stmt = $pdo->prepare('SELECT * FROM reportajes_fotos WHERE id = ?');
    $stmt->execute([$id]);
    $foto = $stmt->fetch();
    if (!$foto) {
        set_flash('error', 'Imagen no encontrada.');
        header('Location: reportajes.php');
        exit;
    }
}

$reportajes = $pdo->query('SELECT id, titulo FROM reportajes ORDER BY titulo')->fetchAll();

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $reportajeId = (int) ($_POST['reportaje_id'] ?? 0);
    $descripcion = trim($_POST['descripcion'] ?? '');
    $orden = (int) ($_POST['orden'] ?? 0);

    if ($reportajeId <= 0) $errors[] = 'Debes seleccionar un reportaje.';

    $urlFoto = $foto['url_foto'] ?? null;
    if (empty($errors)) {
        try {
            $urlFoto = handle_upload('url_foto', 'fotos', ['jpg', 'jpeg', 'png', 'webp', 'gif'], 5 * 1024 * 1024, $urlFoto);
        } catch (RuntimeException $e) {
            $errors[] = $e->getMessage();
        }
    }
    if (empty($urlFoto)) $errors[] = 'Debes subir una imagen.';

    if (empty($errors)) {
        if ($id) {
            $stmt = $pdo->prepare('UPDATE reportajes_fotos SET reportaje_id = ?, url_foto = ?, descripcion = ?, orden = ? WHERE id = ?');
            $stmt->execute([$reportajeId, $urlFoto, $descripcion, $orden, $id]);
            set_flash('success', 'Imagen actualizada correctamente.');
        } else {
            $stmt = $pdo->prepare('INSERT INTO reportajes_fotos (reportaje_id, url_foto, descripcion, orden) VALUES (?, ?, ?, ?)');
            $stmt->execute([$reportajeId, $urlFoto, $descripcion, $orden]);
            set_flash('success', 'Imagen agregada correctamente.');
        }
        header('Location: reportaje_form.php?id=' . $reportajeId);
        exit;
    }

    $foto['reportaje_id'] = $reportajeId;
    $foto['descripcion'] = $descripcion;
    $foto['orden'] = $orden;
}

$page_title = $id ? 'Editar imagen' : 'Nueva imagen';
$active_menu = 'reportajes';

require __DIR__ . '/../config/layout_top.php';
?>
                <div class="row">
                    <div class="col-lg-6">
                        <div class="card">
                            <div class="card-header">
                                <h4 class="card-title"><?php echo htmlspecialchars($page_title); ?></h4>
                            </div>
                            <div class="card-body">
                                <?php foreach ($errors as $error): ?>
                                    <div class="alert alert-danger"><?php echo htmlspecialchars($error); ?></div>
                                <?php endforeach; ?>

                                <?php if (empty($reportajes)): ?>
                                    <div class="alert alert-warning">
                                        Aun no hay reportajes registrados. <a href="reportaje_form.php">Crea uno primero</a> para poder subir imagenes.
                                    </div>
                                <?php endif; ?>

                                <form method="post" enctype="multipart/form-data">
                                    <div class="form-group">
                                        <label><strong>Reportaje</strong></label>
                                        <select name="reportaje_id" class="form-control" required <?php echo empty($reportajes) ? 'disabled' : ''; ?>>
                                            <option value="">-- Selecciona un reportaje --</option>
                                            <?php foreach ($reportajes as $r): ?>
                                                <option value="<?php echo (int) $r['id']; ?>" <?php echo ((int) $foto['reportaje_id'] === (int) $r['id']) ? 'selected' : ''; ?>>
                                                    <?php echo htmlspecialchars($r['titulo']); ?>
                                                </option>
                                            <?php endforeach; ?>
                                        </select>
                                    </div>
                                    <div class="form-group">
                                        <label><strong>Imagen</strong></label>
                                        <input type="file" name="url_foto" class="form-control-file" accept="image/*">
                                        <?php if (!empty($foto['url_foto'])): ?>
                                            <div class="mt-2"><img src="../<?php echo htmlspecialchars($foto['url_foto']); ?>" style="max-width:200px" class="img-thumbnail"></div>
                                        <?php endif; ?>
                                    </div>
                                    <div class="form-group">
                                        <label><strong>Descripcion</strong></label>
                                        <input type="text" name="descripcion" class="form-control" value="<?php echo htmlspecialchars($foto['descripcion'] ?? ''); ?>">
                                    </div>
                                    <div class="form-group">
                                        <label><strong>Orden</strong></label>
                                        <input type="number" name="orden" class="form-control" value="<?php echo (int) $foto['orden']; ?>">
                                    </div>
                                    <button type="submit" class="btn btn-primary" <?php echo empty($reportajes) ? 'disabled' : ''; ?>>Guardar</button>
                                    <a href="<?php echo $foto['reportaje_id'] ? 'reportaje_form.php?id=' . (int) $foto['reportaje_id'] : 'reportajes.php'; ?>" class="btn btn-light">Cancelar</a>
                                </form>
                            </div>
                        </div>
                    </div>
                </div>
<?php
require __DIR__ . '/../config/layout_bottom.php';
