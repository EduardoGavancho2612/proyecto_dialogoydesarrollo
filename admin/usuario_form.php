<?php
session_start();
require __DIR__ . '/../config/conexion.php';
require __DIR__ . '/../config/upload_helper.php';
require __DIR__ . '/../config/permisos.php';

if (empty($_SESSION['usuario_id'])) {
    header('Location: login.php');
    exit;
}
exigir('usuarios', isset($_GET['id']) ? 'editar' : 'crear');

$roles_validos = ['admin', 'editor', 'redactor'];

$id = isset($_GET['id']) ? (int) $_GET['id'] : 0;
$usuario = ['nombres' => '', 'ap_paterno' => '', 'ap_materno' => '', 'email' => '', 'rol' => ''];
$errors = [];

if ($id) {
    $stmt = $pdo->prepare('SELECT * FROM usuarios WHERE id = ?');
    $stmt->execute([$id]);
    $usuario = $stmt->fetch();
    if (!$usuario) {
        set_flash('error', 'Usuario no encontrado.');
        header('Location: usuarios.php');
        exit;
    }
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $nombres = trim($_POST['nombres'] ?? '');
    $apPaterno = trim($_POST['ap_paterno'] ?? '');
    $apMaterno = trim($_POST['ap_materno'] ?? '');
    $email = trim($_POST['email'] ?? '');
    $rol = trim($_POST['rol'] ?? '');
    $password = $_POST['password'] ?? '';

    if ($nombres === '') $errors[] = 'Los nombres son obligatorios.';
    if ($apPaterno === '') $errors[] = 'El apellido paterno es obligatorio.';
    if ($email === '' || !filter_var($email, FILTER_VALIDATE_EMAIL)) $errors[] = 'El correo no es valido.';
    if (!in_array($rol, $roles_validos, true)) $errors[] = 'El rol seleccionado no es valido.';
    if (!$id && $password === '') $errors[] = 'La contrasena es obligatoria para un usuario nuevo.';
    if ($password !== '' && strlen($password) < 6) $errors[] = 'La contrasena debe tener al menos 6 caracteres.';

    if (empty($errors)) {
        try {
            if ($id) {
                if ($password !== '') {
                    $stmt = $pdo->prepare('UPDATE usuarios SET nombres = ?, ap_paterno = ?, ap_materno = ?, email = ?, rol = ?, password_hash = ? WHERE id = ?');
                    $stmt->execute([$nombres, $apPaterno, ($apMaterno !== '' ? $apMaterno : null), $email, $rol, password_hash($password, PASSWORD_DEFAULT), $id]);
                } else {
                    $stmt = $pdo->prepare('UPDATE usuarios SET nombres = ?, ap_paterno = ?, ap_materno = ?, email = ?, rol = ? WHERE id = ?');
                    $stmt->execute([$nombres, $apPaterno, ($apMaterno !== '' ? $apMaterno : null), $email, $rol, $id]);
                }
                set_flash('success', 'Usuario actualizado correctamente.');
            } else {
                $stmt = $pdo->prepare('INSERT INTO usuarios (nombres, ap_paterno, ap_materno, email, password_hash, rol) VALUES (?, ?, ?, ?, ?, ?)');
                $stmt->execute([$nombres, $apPaterno, ($apMaterno !== '' ? $apMaterno : null), $email, password_hash($password, PASSWORD_DEFAULT), $rol]);
                set_flash('success', 'Usuario creado correctamente.');
            }
            header('Location: usuarios.php');
            exit;
        } catch (PDOException $e) {
            if ($e->getCode() === '23000') {
                $errors[] = 'Ya existe un usuario con ese correo.';
            } else {
                $errors[] = 'Error al guardar: ' . $e->getMessage();
            }
        }
    }

    $usuario['nombres'] = $nombres;
    $usuario['ap_paterno'] = $apPaterno;
    $usuario['ap_materno'] = $apMaterno;
    $usuario['email'] = $email;
    $usuario['rol'] = $rol;
}

$page_title = $id ? 'Editar usuario' : 'Nuevo usuario';
$active_menu = 'usuarios';

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
                                        <input type="text" name="nombres" class="form-control" value="<?php echo htmlspecialchars($usuario['nombres'] ?? ''); ?>" required>
                                    </div>
                                    <div class="form-group">
                                        <label><strong>Apellido paterno</strong></label>
                                        <input type="text" name="ap_paterno" class="form-control" value="<?php echo htmlspecialchars($usuario['ap_paterno'] ?? ''); ?>" required>
                                    </div>
                                    <div class="form-group">
                                        <label><strong>Apellido materno</strong></label>
                                        <input type="text" name="ap_materno" class="form-control" value="<?php echo htmlspecialchars($usuario['ap_materno'] ?? ''); ?>">
                                    </div>
                                    <div class="form-group">
                                        <label><strong>Email</strong></label>
                                        <input type="email" name="email" class="form-control" value="<?php echo htmlspecialchars($usuario['email']); ?>" required>
                                    </div>
                                    <div class="form-group">
                                        <label><strong>Rol</strong></label>
                                        <select name="rol" class="form-control" required>
                                            <option value="">-- Selecciona un rol --</option>
                                            <?php foreach ($roles_validos as $r): ?>
                                                <option value="<?php echo $r; ?>" <?php echo (($usuario['rol'] ?? '') === $r) ? 'selected' : ''; ?>><?php echo ucfirst($r); ?></option>
                                            <?php endforeach; ?>
                                        </select>
                                    </div>
                                    <div class="form-group">
                                        <label><strong>Contraseña</strong></label>
                                        <input type="password" name="password" class="form-control" placeholder="<?php echo $id ? 'Dejar en blanco para no cambiarla' : ''; ?>">
                                    </div>
                                    <button type="submit" class="btn btn-primary">Guardar</button>
                                    <a href="usuarios.php" class="btn btn-light">Cancelar</a>
                                </form>
                            </div>
                        </div>
                    </div>
                </div>
<?php
require __DIR__ . '/../config/layout_bottom.php';
