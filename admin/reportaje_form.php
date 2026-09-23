<?php
session_start();
require __DIR__ . '/../config/conexion.php';
require __DIR__ . '/../config/upload_helper.php';
require __DIR__ . '/../config/content_html.php';
require __DIR__ . '/../config/permisos.php';

if (empty($_SESSION['usuario_id'])) {
    header('Location: login.php');
    exit;
}

$id = isset($_GET['id']) ? (int) $_GET['id'] : 0;
exigir('reportajes', $id ? 'editar' : 'crear');
$reportaje = [
    'titulo' => '', 'resumen_corto' => '', 'desarrollo' => '', 'foto_principal' => '',
    'pdf_adjunto' => '', 'fecha_publicacion' => date('Y-m-d'), 'es_destacado' => 0, 'autor_id' => '',
];
$errors = [];

if ($id) {
    $stmt = $pdo->prepare('SELECT * FROM reportajes WHERE id = ?');
    $stmt->execute([$id]);
    $reportaje = $stmt->fetch();
    if (!$reportaje) {
        set_flash('error', 'Reportaje no encontrado.');
        header('Location: reportajes.php');
        exit;
    }
}

$autores = $pdo->query("
    SELECT id,
           CASE WHEN es_nickname = 1 AND nickname IS NOT NULL AND nickname <> ''
                THEN nickname
                ELSE CONCAT_WS(' ', nombres, ap_paterno, ap_materno)
           END AS nombre
    FROM autores
    ORDER BY nombres, ap_paterno
")->fetchAll();

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $titulo = trim($_POST['titulo'] ?? '');
    $resumen = trim($_POST['resumen_corto'] ?? '');
    $desarrolloRaw = trim($_POST['desarrollo'] ?? '');
    $desarrollo = desarrollo_normalizar($desarrolloRaw);
    $fecha = $_POST['fecha_publicacion'] ?? '';
    $destacado = isset($_POST['es_destacado']) ? 1 : 0;
    $autorId = (int) ($_POST['autor_id'] ?? 0);

    if ($titulo === '') $errors[] = 'El titulo es obligatorio.';
    if ($desarrolloRaw === '') $errors[] = 'El desarrollo del reportaje es obligatorio.';
    if ($fecha === '') $errors[] = 'La fecha es obligatoria.';
    if ($autorId <= 0) $errors[] = 'Debes seleccionar un autor.';

    $fotoPath = $reportaje['foto_principal'] ?? null;
    $pdfPath = $reportaje['pdf_adjunto'] ?? null;
    if (empty($errors)) {
        try {
            $fotoPath = handle_upload('foto_principal', 'reportajes', ['jpg', 'jpeg', 'png', 'webp', 'gif'], 5 * 1024 * 1024, $fotoPath);
            $pdfPath = handle_upload('pdf_adjunto', 'reportajes', ['pdf'], 15 * 1024 * 1024, $pdfPath);
        } catch (RuntimeException $e) {
            $errors[] = $e->getMessage();
        }
    }

    if (empty($errors)) {
        if ($id) {
            $stmt = $pdo->prepare('UPDATE reportajes SET titulo = ?, resumen_corto = ?, desarrollo = ?, foto_principal = ?, pdf_adjunto = ?, fecha_publicacion = ?, es_destacado = ?, autor_id = ? WHERE id = ?');
            $stmt->execute([$titulo, $resumen, $desarrollo, $fotoPath, $pdfPath, $fecha, $destacado, $autorId, $id]);
            set_flash('success', 'Reportaje actualizado correctamente.');
        } else {
            $stmt = $pdo->prepare('INSERT INTO reportajes (titulo, resumen_corto, desarrollo, foto_principal, pdf_adjunto, fecha_publicacion, es_destacado, autor_id, usuario_id) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?)');
            $stmt->execute([$titulo, $resumen, $desarrollo, $fotoPath, $pdfPath, $fecha, $destacado, $autorId, $_SESSION['usuario_id']]);
            $id = (int) $pdo->lastInsertId();
            set_flash('success', 'Reportaje creado correctamente. Ya puedes agregarle fotos en la seccion Imagenes.');
        }
        header('Location: reportajes.php');
        exit;
    }

    $reportaje['titulo'] = $titulo;
    $reportaje['resumen_corto'] = $resumen;
    $reportaje['desarrollo'] = $desarrolloRaw;
    $reportaje['fecha_publicacion'] = $fecha;
    $reportaje['es_destacado'] = $destacado;
    $reportaje['autor_id'] = $autorId;
}

$page_title = $id ? 'Editar reportaje' : 'Nuevo reportaje';
$active_menu = 'reportajes';

require __DIR__ . '/../config/layout_top.php';
?>
                <div class="row">
                    <div class="col-lg-8">
                        <div class="card">
                            <div class="card-header">
                                <h4 class="card-title"><?php echo htmlspecialchars($page_title); ?></h4>
                            </div>
                            <div class="card-body">
                                <?php foreach ($errors as $error): ?>
                                    <div class="alert alert-danger"><?php echo htmlspecialchars($error); ?></div>
                                <?php endforeach; ?>

                                <?php if (empty($autores)): ?>
                                    <div class="alert alert-warning">
                                        Aun no hay autores registrados. <a href="autor_form.php">Crea uno primero</a> para poder guardar el reportaje.
                                    </div>
                                <?php endif; ?>

                                <form method="post" enctype="multipart/form-data">
                                    <div class="form-group">
                                        <label><strong>Titulo</strong></label>
                                        <input type="text" name="titulo" class="form-control" value="<?php echo htmlspecialchars($reportaje['titulo']); ?>" required>
                                    </div>
                                    <div class="form-group">
                                        <label><strong>Autor</strong></label>
                                        <select name="autor_id" class="form-control" required <?php echo empty($autores) ? 'disabled' : ''; ?>>
                                            <option value="">-- Selecciona un autor --</option>
                                            <?php foreach ($autores as $a): ?>
                                                <option value="<?php echo (int) $a['id']; ?>" <?php echo ((int) $reportaje['autor_id'] === (int) $a['id']) ? 'selected' : ''; ?>>
                                                    <?php echo htmlspecialchars($a['nombre']); ?>
                                                </option>
                                            <?php endforeach; ?>
                                        </select>
                                    </div>
                                    <div class="form-group">
                                        <label><strong>Resumen corto</strong></label>
                                        <textarea name="resumen_corto" class="form-control" rows="3"><?php echo htmlspecialchars($reportaje['resumen_corto'] ?? ''); ?></textarea>
                                    </div>
                                    <div class="form-group">
                                        <label><strong>Desarrollo</strong></label>
                                        <textarea name="desarrollo" class="form-control" rows="8" required><?php echo htmlspecialchars($reportaje['desarrollo']); ?></textarea>
                                        <small class="text-muted">
                                            Escribe el texto normal (deja una linea en blanco entre parrafos) y se formatea solo.
                                            Tambien puedes usar HTML: <code>&lt;p&gt;</code>, <code>&lt;strong&gt;</code> para subtitulos en negrita,
                                            y <code>&lt;img src="uploads/fotos/archivo.jpg"&gt;</code> para intercalar una foto (subela antes desde
                                            <a href="imagen_form.php" target="_blank">Imagenes</a> y copia su ruta).
                                        </small>
                                    </div>
                                    <div class="form-group">
                                        <label><strong>Fecha de publicacion</strong></label>
                                        <input type="date" name="fecha_publicacion" class="form-control" value="<?php echo htmlspecialchars($reportaje['fecha_publicacion']); ?>" required>
                                    </div>
                                    <div class="form-group form-check">
                                        <input type="checkbox" name="es_destacado" id="es_destacado" class="form-check-input" <?php echo $reportaje['es_destacado'] ? 'checked' : ''; ?>>
                                        <label class="form-check-label" for="es_destacado">Marcar como destacado</label>
                                    </div>
                                    <div class="form-group">
                                        <label><strong>Foto principal</strong></label>
                                        <input type="file" name="foto_principal" class="form-control-file" accept="image/*">
                                        <?php if (!empty($reportaje['foto_principal'])): ?>
                                            <div class="mt-2"><img src="../<?php echo htmlspecialchars($reportaje['foto_principal']); ?>" style="max-width:200px" class="img-thumbnail"></div>
                                        <?php endif; ?>
                                    </div>
                                    <div class="form-group">
                                        <label><strong>PDF adjunto</strong></label>
                                        <input type="file" name="pdf_adjunto" class="form-control-file" accept="application/pdf">
                                        <?php if (!empty($reportaje['pdf_adjunto'])): ?>
                                            <div class="mt-2"><a href="../<?php echo htmlspecialchars($reportaje['pdf_adjunto']); ?>" target="_blank">Ver PDF actual</a></div>
                                        <?php endif; ?>
                                    </div>
                                    <button type="submit" class="btn btn-primary" <?php echo empty($autores) ? 'disabled' : ''; ?>>Guardar</button>
                                    <a href="reportajes.php" class="btn btn-light">Cancelar</a>
                                </form>

                                <?php if ($id): ?>
                                    <hr>
                                    <p class="mb-0">
                                        <a href="imagen_form.php?reportaje_id=<?php echo (int) $id; ?>" class="btn btn-secondary btn-sm">
                                            <i class="fa fa-image"></i> Agregar foto a la galeria de este reportaje
                                        </a>
                                    </p>
                                <?php endif; ?>
                            </div>
                        </div>
                    </div>
                </div>
<?php
require __DIR__ . '/../config/layout_bottom.php';
