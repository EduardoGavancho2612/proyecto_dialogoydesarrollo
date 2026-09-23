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
exigir('podcasts', $id ? 'editar' : 'crear');
$podcast = ['titulo' => '', 'audio_url' => '', 'enlace_externo' => '', 'fecha_publicacion' => date('Y-m-d')];
$errors = [];

if ($id) {
    $stmt = $pdo->prepare('SELECT * FROM podcasts WHERE id = ?');
    $stmt->execute([$id]);
    $podcast = $stmt->fetch();
    if (!$podcast) {
        set_flash('error', 'Podcast no encontrado.');
        header('Location: podcasts.php');
        exit;
    }
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $titulo = trim($_POST['titulo'] ?? '');
    $audioUrlManual = trim($_POST['audio_url'] ?? '');
    $externo = trim($_POST['enlace_externo'] ?? '');
    $fecha = $_POST['fecha_publicacion'] ?? '';

    if ($titulo === '') $errors[] = 'El titulo es obligatorio.';
    if ($fecha === '') $errors[] = 'La fecha es obligatoria.';
    if ($externo !== '' && !filter_var($externo, FILTER_VALIDATE_URL)) $errors[] = 'El enlace externo no es valido.';
    if ($audioUrlManual !== '' && !filter_var($audioUrlManual, FILTER_VALIDATE_URL)) $errors[] = 'La URL de audio no es valida.';

    $audioPath = $podcast['audio_url'] ?? null;
    if (empty($errors)) {
        try {
            // Si se sube un archivo de audio, tiene prioridad sobre la URL escrita a mano.
            $subido = handle_upload('audio_file', 'podcasts', ['mp3', 'wav', 'm4a', 'ogg'], 40 * 1024 * 1024, null);
            if ($subido) {
                $audioPath = $subido;
            } elseif ($audioUrlManual !== '') {
                $audioPath = $audioUrlManual;
            }
        } catch (RuntimeException $e) {
            $errors[] = $e->getMessage();
        }
    }

    if (empty($errors) && empty($audioPath) && $externo === '') {
        $errors[] = 'Agrega un audio (archivo o URL) o al menos un enlace externo.';
    }

    if (empty($errors)) {
        if ($id) {
            $stmt = $pdo->prepare('UPDATE podcasts SET titulo = ?, audio_url = ?, enlace_externo = ?, fecha_publicacion = ? WHERE id = ?');
            $stmt->execute([$titulo, ($audioPath !== '' ? $audioPath : null), ($externo !== '' ? $externo : null), $fecha, $id]);
            set_flash('success', 'Podcast actualizado correctamente.');
        } else {
            $stmt = $pdo->prepare('INSERT INTO podcasts (titulo, audio_url, enlace_externo, fecha_publicacion, usuario_id) VALUES (?, ?, ?, ?, ?)');
            $stmt->execute([$titulo, ($audioPath !== '' ? $audioPath : null), ($externo !== '' ? $externo : null), $fecha, $_SESSION['usuario_id']]);
            set_flash('success', 'Podcast creado correctamente.');
        }
        header('Location: podcasts.php');
        exit;
    }

    $podcast['titulo'] = $titulo;
    $podcast['audio_url'] = $audioPath ?? $audioUrlManual;
    $podcast['enlace_externo'] = $externo;
    $podcast['fecha_publicacion'] = $fecha;
}

$page_title = $id ? 'Editar podcast' : 'Nuevo podcast';
$active_menu = 'podcasts';

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
                                        <input type="text" name="titulo" class="form-control" value="<?php echo htmlspecialchars($podcast['titulo']); ?>" required>
                                    </div>

                                    <div class="form-group">
                                        <label><strong>Archivo de audio</strong></label>
                                        <input type="file" name="audio_file" class="form-control-file" accept="audio/*">
                                        <?php if (!empty($podcast['audio_url']) && !filter_var($podcast['audio_url'], FILTER_VALIDATE_URL)): ?>
                                            <div class="mt-2">
                                                <audio controls src="../<?php echo htmlspecialchars($podcast['audio_url']); ?>" style="width:100%;max-width:400px"></audio>
                                            </div>
                                        <?php endif; ?>
                                        <small class="text-muted">MP3, WAV, M4A u OGG (máx. 40 MB). Sube el episodio para que se reproduzca directo en la página.</small>
                                    </div>

                                    <div class="form-group">
                                        <label><strong>...o URL de audio directa</strong></label>
                                        <input type="url" name="audio_url" class="form-control" placeholder="https://.../episodio.mp3" value="<?php echo (!empty($podcast['audio_url']) && filter_var($podcast['audio_url'], FILTER_VALIDATE_URL)) ? htmlspecialchars($podcast['audio_url']) : ''; ?>">
                                        <small class="text-muted">Alternativa a subir el archivo: un enlace directo a un .mp3 (se ignora si subes un archivo arriba).</small>
                                    </div>

                                    <div class="form-group">
                                        <label><strong>Enlace externo (opcional)</strong></label>
                                        <input type="url" name="enlace_externo" class="form-control" placeholder="https://open.spotify.com/... o https://youtube.com/..." value="<?php echo htmlspecialchars($podcast['enlace_externo'] ?? ''); ?>">
                                        <small class="text-muted">Página del episodio en Spotify, iVoox, YouTube, etc. Se muestra como boton "Ver en la plataforma".</small>
                                    </div>

                                    <div class="form-group">
                                        <label><strong>Fecha de publicacion</strong></label>
                                        <input type="date" name="fecha_publicacion" class="form-control" value="<?php echo htmlspecialchars($podcast['fecha_publicacion']); ?>" required>
                                    </div>
                                    <button type="submit" class="btn btn-primary">Guardar</button>
                                    <a href="podcasts.php" class="btn btn-light">Cancelar</a>
                                </form>
                            </div>
                        </div>
                    </div>
                </div>
<?php
require __DIR__ . '/../config/layout_bottom.php';
