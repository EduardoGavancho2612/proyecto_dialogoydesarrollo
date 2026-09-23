<?php
session_start();
require __DIR__ . '/../config/conexion.php';

if (!empty($_SESSION['usuario_id'])) {
    header('Location: index.php');
    exit;
}

$token = $_GET['token'] ?? $_POST['token'] ?? '';
$errors = [];
$ok = false;

$usuario = null;
if ($token !== '') {
    $tokenHash = hash('sha256', $token);
    $stmt = $pdo->prepare('SELECT id, nombres FROM usuarios WHERE reset_token = ? AND reset_expira > NOW()');
    $stmt->execute([$tokenHash]);
    $usuario = $stmt->fetch();
}

if (!$usuario) {
    $errors[] = 'El enlace no es valido o ya expiro. Solicita uno nuevo.';
} elseif ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $password = $_POST['password'] ?? '';
    $password2 = $_POST['password2'] ?? '';

    if (strlen($password) < 6) $errors[] = 'La contrasena debe tener al menos 6 caracteres.';
    if ($password !== $password2) $errors[] = 'Las contrasenas no coinciden.';

    if (empty($errors)) {
        $upd = $pdo->prepare('UPDATE usuarios SET password_hash = ?, reset_token = NULL, reset_expira = NULL WHERE id = ?');
        $upd->execute([password_hash($password, PASSWORD_DEFAULT), $usuario['id']]);
        $ok = true;
    }
}
?>
<!DOCTYPE html>
<html lang="es" class="h-100">
<head>
    <meta charset="utf-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width,initial-scale=1">
    <title>Restablecer contraseña - Dialogo y Desarrollo</title>
    <link rel="icon" type="image/png" sizes="16x16" href="../images/favicon.png">
    <link href="../css/style.css" rel="stylesheet">
</head>
<body class="h-100">
    <div class="authincation h-100">
        <div class="container-fluid h-100">
            <div class="row justify-content-center h-100 align-items-center">
                <div class="col-md-6">
                    <div class="authincation-content">
                        <div class="row no-gutters">
                            <div class="col-xl-12">
                                <div class="auth-form">
                                    <h4 class="text-center mb-4">Restablecer contraseña</h4>

                                    <?php foreach ($errors as $error): ?>
                                        <div class="alert alert-danger"><?php echo htmlspecialchars($error); ?></div>
                                    <?php endforeach; ?>

                                    <?php if ($ok): ?>
                                        <div class="alert alert-success">Tu contraseña se actualizo correctamente.</div>
                                        <div class="text-center mt-3">
                                            <a href="login.php" class="btn btn-primary">Iniciar sesion</a>
                                        </div>
                                    <?php elseif ($usuario): ?>
                                        <p class="text-center">Hola <?php echo htmlspecialchars($usuario['nombres']); ?>, elige tu nueva contraseña.</p>
                                        <form method="post">
                                            <input type="hidden" name="token" value="<?php echo htmlspecialchars($token); ?>">
                                            <div class="form-group">
                                                <label><strong>Nueva contraseña</strong></label>
                                                <input type="password" name="password" class="form-control" required minlength="6">
                                            </div>
                                            <div class="form-group">
                                                <label><strong>Confirmar contraseña</strong></label>
                                                <input type="password" name="password2" class="form-control" required minlength="6">
                                            </div>
                                            <div class="text-center">
                                                <button type="submit" class="btn btn-primary btn-block">Guardar nueva contraseña</button>
                                            </div>
                                        </form>
                                    <?php else: ?>
                                        <div class="text-center mt-3">
                                            <a href="forgot_password.php" class="btn btn-primary">Solicitar un enlace nuevo</a>
                                        </div>
                                    <?php endif; ?>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
    <script src="../vendor/global/global.min.js"></script>
    <script src="../js/quixnav-init.js"></script>
    <script src="../js/custom.min.js"></script>
</body>
</html>
