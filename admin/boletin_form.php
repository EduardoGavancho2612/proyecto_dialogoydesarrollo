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
exigir('boletines', $id ? 'editar' : 'crear');
$boletin = ['numero_boletin' => '', 'resumen' => '', 'foto_portada' => '', 'archivo_pdf' => '', 'fecha_publicacion' => date('Y-m-d')];
$errors = [];

if ($id) {
    $stmt = $pdo->prepare('SELECT * FROM boletines WHERE id = ?');
    $stmt->execute([$id]);
    $boletin = $stmt->fetch();
    if (!$boletin) {
        set_flash('error', 'Boletin no encontrado.');
        header('Location: boletines.php');
        exit;
    }
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $numero = trim($_POST['numero_boletin'] ?? '');
    $resumen = trim($_POST['resumen'] ?? '');
    $fecha = $_POST['fecha_publicacion'] ?? '';

    if ($numero === '') $errors[] = 'El numero de boletin es obligatorio.';
    if (mb_strlen($numero) > 50) $errors[] = 'El numero de boletin no puede superar los 50 caracteres.';
    if ($fecha === '') $errors[] = 'La fecha es obligatoria.';

    $fotoPath = $boletin['foto_portada'] ?? null;
    $pdfPath = $boletin['archivo_pdf'] ?? null;
    if (empty($errors)) {
        try {
            $fotoPath = handle_upload('foto_portada', 'boletines', ['jpg', 'jpeg', 'png', 'webp', 'gif'], 5 * 1024 * 1024, $fotoPath);
            $pdfPath = handle_upload('archivo_pdf', 'boletines', ['pdf'], 15 * 1024 * 1024, $pdfPath);
        } catch (RuntimeException $e) {
            $errors[] = $e->getMessage();
        }
    }

    if (empty($errors) && empty($pdfPath)) {
        $errors[] = 'El archivo PDF del boletin es obligatorio.';
    }

    if (empty($errors)) {
        try {
            if ($id) {
                $stmt = $pdo->prepare('UPDATE boletines SET numero_boletin = ?, resumen = ?, foto_portada = ?, archivo_pdf = ?, fecha_publicacion = ? WHERE id = ?');
                $stmt->execute([$numero, ($resumen !== '' ? $resumen : null), $fotoPath, $pdfPath, $fecha, $id]);
                set_flash('success', 'Boletin actualizado correctamente.');
            } else {
                $stmt = $pdo->prepare('INSERT INTO boletines (numero_boletin, resumen, foto_portada, archivo_pdf, fecha_publicacion, usuario_id) VALUES (?, ?, ?, ?, ?, ?)');
                $stmt->execute([$numero, ($resumen !== '' ? $resumen : null), $fotoPath, $pdfPath, $fecha, $_SESSION['usuario_id']]);
                set_flash('success', 'Boletin creado correctamente.');
            }
            header('Location: boletines.php');
            exit;
        } catch (PDOException $e) {
            if ($e->getCode() === '23000') {
                $errors[] = 'Ya existe un boletin con ese numero.';
            } else {
                $errors[] = 'Error al guardar: ' . $e->getMessage();
            }
        }
    }

    $boletin['numero_boletin'] = $numero;
    $boletin['resumen'] = $resumen;
    $boletin['fecha_publicacion'] = $fecha;
}

$page_title = $id ? 'Editar boletin' : 'Nuevo boletin';
$active_menu = 'boletines';

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
                                        <label><strong>Numero de boletin</strong></label>
                                        <input type="text" name="numero_boletin" maxlength="50" class="form-control" value="<?php echo htmlspecialchars($boletin['numero_boletin']); ?>" required>
                                    </div>
                                    <div class="form-group">
                                        <label><strong>Resumen</strong></label>
                                        <textarea name="resumen" class="form-control" rows="4"><?php echo htmlspecialchars($boletin['resumen'] ?? ''); ?></textarea>
                                    </div>
                                    <div class="form-group">
                                        <label><strong>Fecha de publicacion</strong></label>
                                        <input type="date" name="fecha_publicacion" class="form-control" value="<?php echo htmlspecialchars($boletin['fecha_publicacion']); ?>" required>
                                    </div>
                                    <div class="form-group">
                                        <label><strong>Foto de portada</strong></label>
                                        <input type="file" name="foto_portada" class="form-control-file" accept="image/*">
                                        <?php if (!empty($boletin['foto_portada'])): ?>
                                            <div class="mt-2"><img src="../<?php echo htmlspecialchars($boletin['foto_portada']); ?>" style="max-width:200px" class="img-thumbnail"></div>
                                        <?php endif; ?>
                                    </div>
                                    <div class="form-group">
                                        <label><strong>Archivo PDF</strong></label>
                                        <input type="file" name="archivo_pdf" class="form-control-file" accept="application/pdf">
                                        <?php if (!empty($boletin['archivo_pdf'])): ?>
                                            <div class="mt-2"><a href="../<?php echo htmlspecialchars($boletin['archivo_pdf']); ?>" target="_blank">Ver PDF actual</a></div>
                                        <?php endif; ?>
                                    </div>
                                    <button type="submit" class="btn btn-primary">Guardar</button>
                                    <a href="boletines.php" class="btn btn-light">Cancelar</a>
                                </form>
                            </div>
                        </div>
                    </div>
                </div>
<?php
require __DIR__ . '/../config/layout_bottom.php';
