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
exigir('noticias', $id ? 'editar' : 'crear');
$noticia = ['titulo' => '', 'foto' => '', 'link_externo' => '', 'fecha_publicacion' => date('Y-m-d')];
$errors = [];

if ($id) {
    $stmt = $pdo->prepare('SELECT * FROM noticias WHERE id = ?');
    $stmt->execute([$id]);
    $noticia = $stmt->fetch();
    if (!$noticia) {
        set_flash('error', 'Noticia no encontrada.');
        header('Location: noticias.php');
        exit;
    }
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $titulo = trim($_POST['titulo'] ?? '');
    $link = trim($_POST['link_externo'] ?? '');
    $fecha = $_POST['fecha_publicacion'] ?? '';

    if ($titulo === '') $errors[] = 'El titulo es obligatorio.';
    if ($link === '' || !filter_var($link, FILTER_VALIDATE_URL)) $errors[] = 'El enlace externo no es valido.';
    if ($fecha === '') $errors[] = 'La fecha es obligatoria.';

    $fotoPath = $noticia['foto'] ?? null;
    if (empty($errors)) {
        try {
            $fotoPath = handle_upload('foto', 'noticias', ['jpg', 'jpeg', 'png', 'webp', 'gif'], 5 * 1024 * 1024, $fotoPath);
        } catch (RuntimeException $e) {
            $errors[] = $e->getMessage();
        }
    }

    if (empty($errors)) {
        if ($id) {
            $stmt = $pdo->prepare('UPDATE noticias SET titulo = ?, foto = ?, link_externo = ?, fecha_publicacion = ? WHERE id = ?');
            $stmt->execute([$titulo, $fotoPath, $link, $fecha, $id]);
            set_flash('success', 'Noticia actualizada correctamente.');
        } else {
            $stmt = $pdo->prepare('INSERT INTO noticias (titulo, foto, link_externo, fecha_publicacion, usuario_id) VALUES (?, ?, ?, ?, ?)');
            $stmt->execute([$titulo, $fotoPath, $link, $fecha, $_SESSION['usuario_id']]);
            set_flash('success', 'Noticia creada correctamente.');
        }
        header('Location: noticias.php');
        exit;
    }

    $noticia['titulo'] = $titulo;
    $noticia['link_externo'] = $link;
    $noticia['fecha_publicacion'] = $fecha;
}

$page_title = $id ? 'Editar noticia' : 'Nueva noticia';
$active_menu = 'noticias';

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
                                <form method="post" enctype="multipart/form-data">
                                    <div class="form-group">
                                        <label><strong>Titulo</strong></label>
                                        <input type="text" name="titulo" class="form-control" value="<?php echo htmlspecialchars($noticia['titulo']); ?>" required>
                                    </div>
                                    <div class="form-group">
                                        <label><strong>Enlace externo</strong></label>
                                        <input type="url" name="link_externo" class="form-control" placeholder="https://..." value="<?php echo htmlspecialchars($noticia['link_externo']); ?>" required>
                                    </div>
                                    <div class="form-group">
                                        <label><strong>Fecha de publicacion</strong></label>
                                        <input type="date" name="fecha_publicacion" class="form-control" value="<?php echo htmlspecialchars($noticia['fecha_publicacion']); ?>" required>
                                    </div>
                                    <div class="form-group">
                                        <label><strong>Foto</strong></label>
                                        <input type="file" name="foto" class="form-control-file" accept="image/*">
                                        <?php if (!empty($noticia['foto'])): ?>
                                            <div class="mt-2">
                                                <img src="../<?php echo htmlspecialchars($noticia['foto']); ?>" style="max-width:200px" class="img-thumbnail">
                                            </div>
                                        <?php endif; ?>
                                    </div>
                                    <button type="submit" class="btn btn-primary">Guardar</button>
                                    <a href="noticias.php" class="btn btn-light">Cancelar</a>
                                </form>
                            </div>
                        </div>
                    </div>
                </div>
<?php
require __DIR__ . '/../config/layout_bottom.php';
