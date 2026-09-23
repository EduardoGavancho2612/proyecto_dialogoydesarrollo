<?php
session_start();
require __DIR__ . '/../config/conexion.php';
require __DIR__ . '/../config/upload_helper.php';
require __DIR__ . '/../config/permisos.php';

if (empty($_SESSION['usuario_id'])) {
    header('Location: login.php');
    exit;
}

$id = isset($_GET['id']) ? (int) $_GET['id'] : 0;
exigir('videos', $id ? 'editar' : 'crear');
$video = ['titulo' => '', 'url_embed' => '', 'fecha_publicacion' => date('Y-m-d')];
$errors = [];

if ($id) {
    $stmt = $pdo->prepare('SELECT * FROM videos WHERE id = ?');
    $stmt->execute([$id]);
    $video = $stmt->fetch();
    if (!$video) {
        set_flash('error', 'Video no encontrado.');
        header('Location: videos.php');
        exit;
    }
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $titulo = trim($_POST['titulo'] ?? '');
    $embed = trim($_POST['url_embed'] ?? '');
    $fecha = $_POST['fecha_publicacion'] ?? '';

    if ($titulo === '') $errors[] = 'El titulo es obligatorio.';
    if ($embed === '' || !filter_var($embed, FILTER_VALIDATE_URL)) $errors[] = 'La URL de embed no es valida.';
    if ($fecha === '') $errors[] = 'La fecha es obligatoria.';

    if (empty($errors)) {
        if ($id) {
            $stmt = $pdo->prepare('UPDATE videos SET titulo = ?, url_embed = ?, fecha_publicacion = ? WHERE id = ?');
            $stmt->execute([$titulo, $embed, $fecha, $id]);
            set_flash('success', 'Video actualizado correctamente.');
        } else {
            $stmt = $pdo->prepare('INSERT INTO videos (titulo, url_embed, fecha_publicacion, usuario_id) VALUES (?, ?, ?, ?)');
            $stmt->execute([$titulo, $embed, $fecha, $_SESSION['usuario_id']]);
            set_flash('success', 'Video creado correctamente.');
        }
        header('Location: videos.php');
        exit;
    }

    $video['titulo'] = $titulo;
    $video['url_embed'] = $embed;
    $video['fecha_publicacion'] = $fecha;
}

$page_title = $id ? 'Editar video' : 'Nuevo video';
$active_menu = 'videos';

require __DIR__ . '/../config/layout_top.php';
?>
                <div class="row">
                    <div class="col-lg-7">
                        <div class="card">
                            <div class="card-header">
                                <h4 class="card-title"><?php echo htmlspecialchars($page_title); ?></h4>
                            </div>
                            <div class="card-body">
                                <?php foreach ($errors as $error): ?>
                                    <div class="alert alert-danger"><?php echo htmlspecialchars($error); ?></div>
                                <?php endforeach; ?>
                                <form method="post">
                                    <div class="form-group">
                                        <label><strong>Titulo</strong></label>
                                        <input type="text" name="titulo" class="form-control" value="<?php echo htmlspecialchars($video['titulo']); ?>" required>
                                    </div>
                                    <div class="form-group">
                                        <label><strong>URL de embed</strong></label>
                                        <input type="url" name="url_embed" class="form-control" placeholder="https://www.youtube.com/embed/..." value="<?php echo htmlspecialchars($video['url_embed']); ?>" required>
                                    </div>
                                    <div class="form-group">
                                        <label><strong>Fecha de publicacion</strong></label>
                                        <input type="date" name="fecha_publicacion" class="form-control" value="<?php echo htmlspecialchars($video['fecha_publicacion']); ?>" required>
                                    </div>
                                    <button type="submit" class="btn btn-primary">Guardar</button>
                                    <a href="videos.php" class="btn btn-light">Cancelar</a>
                                </form>
                            </div>
                        </div>
                    </div>
                </div>
<?php
require __DIR__ . '/../config/layout_bottom.php';
