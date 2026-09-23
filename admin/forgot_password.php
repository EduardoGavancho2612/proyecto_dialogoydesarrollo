<?php
session_start();
require __DIR__ . '/../config/conexion.php';

if (!empty($_SESSION['usuario_id'])) {
    header('Location: index.php');
    exit;
}

$enviado = false;
$errors = [];
$devLink = null; // Solo se rellena si no hay servidor de correo configurado (entorno local).

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $email = trim($_POST['email'] ?? '');

    if ($email === '' || !filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $errors[] = 'Ingresa un correo valido.';
    } else {
        $stmt = $pdo->prepare('SELECT id, nombres FROM usuarios WHERE email = ?');
        $stmt->execute([$email]);
        $usuario = $stmt->fetch();

        // Se responde igual exista o no la cuenta, para no revelar que correos estan registrados.
        $enviado = true;

        if ($usuario) {
            $token = bin2hex(random_bytes(32));
            $tokenHash = hash('sha256', $token);
            $expira = date('Y-m-d H:i:s', time() + 30 * 60);

            $upd = $pdo->prepare('UPDATE usuarios SET reset_token = ?, reset_expira = ? WHERE id = ?');
            $upd->execute([$tokenHash, $expira, $usuario['id']]);

            $enlace = (isset($_SERVER['HTTPS']) ? 'https://' : 'http://') . $_SERVER['HTTP_HOST']
                . dirname($_SERVER['REQUEST_URI']) . '/reset_password.php?token=' . $token;

            $asunto = 'Recuperar contrasena - Panel Dialogo y Desarrollo';
            $cuerpo = "Hola {$usuario['nombres']},\n\nPara elegir una nueva contrasena entra a este enlace (valido 30 minutos):\n{$enlace}\n\nSi no pediste esto, ignora este correo.";
            $cabeceras = 'From: no-reply@dialogoydesarrollo.local';

            $correoEnviado = @mail($email, $asunto, $cuerpo, $cabeceras);

            // Este servidor local no tiene un correo saliente configurado (XAMPP no trae
            // SMTP por defecto), asi que ademas se muestra el enlace en pantalla para poder
            // probar el flujo. En produccion (APP_ENV=production) nunca se muestra el enlace,
            // aunque el envio de correo falle: mostrarlo permitiria a cualquiera tomar el
            // control de una cuenta ajena con solo saber su email.
            if (!$correoEnviado && APP_ENV !== 'production') {
                $devLink = $enlace;
            }
        }
    }
}
?>
<!DOCTYPE html>
<html lang="es" class="h-100">
<head>
    <meta charset="utf-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width,initial-scale=1">
    <title>Recuperar contraseña - Dialogo y Desarrollo</title>
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
                                    <h4 class="text-center mb-4">Recuperar contraseña</h4>

                                    <?php foreach ($errors as $error): ?>
                                        <div class="alert alert-danger"><?php echo htmlspecialchars($error); ?></div>
                                    <?php endforeach; ?>

                                    <?php if ($enviado): ?>
                                        <div class="alert alert-success">
                                            Si el correo esta registrado, se enviaron instrucciones para restablecer la contraseña.
                                        </div>
                                        <?php if ($devLink): ?>
                                            <div class="alert alert-warning">
                                                <strong>Modo local:</strong> este servidor no tiene correo saliente configurado, asi que aqui esta el enlace directo para continuar la prueba:<br>
                                                <a href="<?php echo htmlspecialchars($devLink); ?>"><?php echo htmlspecialchars($devLink); ?></a>
                                            </div>
                                        <?php endif; ?>
                                        <div class="text-center mt-3">
                                            <a href="login.php">Volver a iniciar sesion</a>
                                        </div>
                                    <?php else: ?>
                                        <form method="post">
                                            <div class="form-group">
                                                <label><strong>Correo</strong></label>
                                                <input type="email" name="email" class="form-control" required autofocus>
                                            </div>
                                            <div class="text-center">
                                                <button type="submit" class="btn btn-primary btn-block">Enviar instrucciones</button>
                                            </div>
                                        </form>
                                        <div class="text-center mt-3">
                                            <a href="login.php">Volver a iniciar sesion</a>
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
