<?php
session_start();
require __DIR__ . '/../config/conexion.php';
require __DIR__ . '/../config/upload_helper.php';
require __DIR__ . '/../config/permisos.php';

if (empty($_SESSION['usuario_id'])) {
    header('Location: login.php');
    exit;
}
exigir('autores', isset($_GET['id']) ? 'editar' : 'crear');

$id = isset($_GET['id']) ? (int) $_GET['id'] : 0;
$autor = ['nombres' => '', 'ap_paterno' => '', 'ap_materno' => '', 'nickname' => '', 'es_nickname' => 0];
$errors = [];

if ($id) {
    $stmt = $pdo->prepare('SELECT * FROM autores WHERE id = ?');
    $stmt->execute([$id]);
    $autor = $stmt->fetch();
    if (!$autor) {
        set_flash('error', 'Autor no encontrado.');
        header('Location: autores.php');
        exit;
    }
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $nombres = trim($_POST['nombres'] ?? '');
    $apPaterno = trim($_POST['ap_paterno'] ?? '');
    $apMaterno = trim($_POST['ap_materno'] ?? '');
    $nickname = trim($_POST['nickname'] ?? '');
    $esNickname = isset($_POST['es_nickname']) ? 1 : 0;

    if ($nombres === '') {
        $errors[] = 'Los nombres son obligatorios.';
    }
    if ($esNickname && $nickname === '') {
        $errors[] = 'Si se usa el nickname para mostrar, debes escribirlo.';
    }

    if (empty($errors)) {
        if ($id) {
            $stmt = $pdo->prepare('UPDATE autores SET nombres = ?, ap_paterno = ?, ap_materno = ?, nickname = ?, es_nickname = ? WHERE id = ?');
            $stmt->execute([$nombres, ($apPaterno !== '' ? $apPaterno : null), ($apMaterno !== '' ? $apMaterno : null), ($nickname !== '' ? $nickname : null), $esNickname, $id]);
            set_flash('success', 'Autor actualizado correctamente.');
        } else {
            $stmt = $pdo->prepare('INSERT INTO autores (nombres, ap_paterno, ap_materno, nickname, es_nickname) VALUES (?, ?, ?, ?, ?)');
            $stmt->execute([$nombres, ($apPaterno !== '' ? $apPaterno : null), ($apMaterno !== '' ? $apMaterno : null), ($nickname !== '' ? $nickname : null), $esNickname]);
            set_flash('success', 'Autor creado correctamente.');
        }
        header('Location: autores.php');
        exit;
    }

    $autor['nombres'] = $nombres;
    $autor['ap_paterno'] = $apPaterno;
    $autor['ap_materno'] = $apMaterno;
    $autor['nickname'] = $nickname;
    $autor['es_nickname'] = $esNickname;
}

$page_title = $id ? 'Editar autor' : 'Nuevo autor';
$active_menu = 'autores';

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
                                <form method="post">
                                    <div class="form-group">
                                        <label><strong>Nombres</strong></label>
                                        <input type="text" name="nombres" class="form-control" value="<?php echo htmlspecialchars($autor['nombres'] ?? ''); ?>" required>
                                    </div>
                                    <div class="form-group">
                                        <label><strong>Apellido paterno</strong></label>
                                        <input type="text" name="ap_paterno" class="form-control" value="<?php echo htmlspecialchars($autor['ap_paterno'] ?? ''); ?>">
                                    </div>
                                    <div class="form-group">
                                        <label><strong>Apellido materno</strong></label>
                                        <input type="text" name="ap_materno" class="form-control" value="<?php echo htmlspecialchars($autor['ap_materno'] ?? ''); ?>">
                                    </div>
                                    <div class="form-group">
                                        <label><strong>Nickname</strong></label>
                                        <input type="text" name="nickname" class="form-control" value="<?php echo htmlspecialchars($autor['nickname'] ?? ''); ?>">
                                    </div>
                                    <div class="form-group form-check">
                                        <input type="checkbox" name="es_nickname" id="es_nickname" class="form-check-input" <?php echo !empty($autor['es_nickname']) ? 'checked' : ''; ?>>
                                        <label class="form-check-label" for="es_nickname">Mostrar el nickname en lugar del nombre real</label>
                                    </div>
                                    <button type="submit" class="btn btn-primary">Guardar</button>
                                    <a href="autores.php" class="btn btn-light">Cancelar</a>
                                </form>
                            </div>
                        </div>
                    </div>
                </div>
<?php
require __DIR__ . '/../config/layout_bottom.php';
