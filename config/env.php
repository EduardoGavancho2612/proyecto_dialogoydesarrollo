<?php
/**
 * Carga variables de entorno desde el archivo .env en la raiz del proyecto
 * (si existe) hacia getenv()/$_ENV, sin depender de ninguna libreria externa.
 *
 * Se usa para que config/conexion.php no tenga credenciales de base de datos
 * escritas directamente en el codigo. En XAMPP (desarrollo local), si no hay
 * .env, se usan los valores por defecto ya conocidos (root sin contraseña).
 */
function cargar_env(string $rutaEnv): void
{
    if (!is_file($rutaEnv) || !is_readable($rutaEnv)) {
        return;
    }

    foreach (file($rutaEnv, FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES) as $linea) {
        $linea = trim($linea);
        if ($linea === '' || $linea[0] === '#') {
            continue;
        }
        if (!str_contains($linea, '=')) {
            continue;
        }
        [$clave, $valor] = explode('=', $linea, 2);
        $clave = trim($clave);
        $valor = trim($valor);
        // Quita comillas envolventes, si las hay: KEY="valor" o KEY='valor'.
        if (strlen($valor) >= 2 && $valor[0] === $valor[-1] && in_array($valor[0], ['"', "'"], true)) {
            $valor = substr($valor, 1, -1);
        }
        if ($clave === '' || getenv($clave) !== false) {
            continue; // No sobreescribe variables de entorno reales ya definidas por el hosting.
        }
        putenv("$clave=$valor");
        $_ENV[$clave] = $valor;
    }
}

cargar_env(__DIR__ . '/../.env');

/** Lee una variable de entorno con valor por defecto si no existe. */
function env(string $clave, ?string $default = null): ?string
{
    $valor = getenv($clave);
    return $valor === false ? $default : $valor;
}
