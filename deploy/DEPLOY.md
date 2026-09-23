# Despliegue en Hostinger

## 0. Qué se preparó en esta revisión

- `config/conexion.php` ya no tiene la contraseña de la base de datos escrita en el
  código: la lee de variables de entorno (`.env`), con `.env.example` como plantilla.
- `config/env.php` es un cargador mínimo de `.env` (sin librerías externas).
- `.htaccess` nuevos: bloquean el acceso web directo a `/config/`, a `.env`/`.sql`/`.md`,
  desactivan la ejecución de PHP dentro de `/uploads/` (por si alguien sube un archivo
  malicioso) y fuerzan HTTPS en el hosting real (no afecta a `localhost`).
- `index.php` (raíz) ahora redirige a `/public/` (el sitio de noticias) en vez de al
  panel de administración — así es como debe verse el dominio para un visitante real.
- `admin/forgot_password.php` deja de mostrar el enlace de recuperación en pantalla
  cuando `APP_ENV=production` (antes se mostraba si el correo fallaba, lo cual es un
  hueco de seguridad: cualquiera podría "recuperar" la contraseña de otra persona).
- `robots.txt` nuevo en la raíz, apuntando al sitemap y bloqueando `/admin/`, `/config/`,
  `/uploads/`, `/vendor/` para buscadores.
- `deploy/00_crear_bd_local.sql`, `01_esquema.sql`, `02_datos.sql` y
  `03_completo_esquema_y_datos.sql`: scripts SQL (ver sección 3).

Nada de esto cambia el comportamiento en XAMPP: sin archivo `.env`, todo sigue
funcionando con `root` sin contraseña sobre `revista_digital`, igual que antes.

## 1. Requisitos en Hostinger

- Plan con PHP **8.1 o superior** (el código usa `str_contains`/`str_starts_with`,
  disponibles desde PHP 8.0; se desarrolló y probó con 8.2).
- Extensiones (vienen activas por defecto en Hostinger): `pdo_mysql`, `mbstring`.
- Base de datos MySQL (hPanel las crea en MySQL 8 / MariaDB, ambas compatibles).

## 2. Subir los archivos

1. En hPanel: **Sitios web > [tu dominio] > Administrador de archivos**, entra a
   `public_html`.
2. Si el dominio es nuevo y `public_html` está vacío, sube ahí **todo el contenido**
   de esta carpeta (`theme/`) — es decir, `admin/`, `config/`, `public/`, `uploads/`,
   `vendor/`, `assets/`, `css/`, `js/`, `images/`, `icons/`, `index.php`, `.htaccess`,
   `robots.txt`, etc. **No subas** `deploy/`, `.env.example`, ni ningún `.git`.
3. El resultado debe ser que `public_html/admin/`, `public_html/public/`,
   `public_html/config/`, etc. queden todos al mismo nivel (igual que ahora en
   `htdocs/theme/`) — el dominio completo (`tu-dominio.com`) es la raíz de este
   proyecto, tal como `http://localhost/theme/` lo es en XAMPP.
4. Más simple: comprime la carpeta `theme/` en un `.zip`, súbelo con el Administrador
   de archivos y usa "Extraer" — evita subir archivo por archivo.

## 3. Crear la base de datos y cargar los datos

1. hPanel > **Bases de datos > Bases de datos MySQL** > crear una base de datos y un
   usuario (Hostinger los nombra `u123456789_algo`; anota el **nombre de la base**, el
   **usuario** y la **contraseña** que elijas — el host casi siempre es `localhost`).
2. Entra a **phpMyAdmin** desde el mismo panel, selecciona esa base de datos vacía.
3. Pestaña **Importar** > elige el archivo
   [`deploy/03_completo_esquema_y_datos.sql`](03_completo_esquema_y_datos.sql) > Continuar.
   Esto crea las 8 tablas (`usuarios`, `autores`, `reportajes`, `reportajes_fotos`,
   `boletines`, `noticias`, `podcasts`, `videos`) y carga todos los datos actuales
   (26 reportajes, usuarios, autores, etc.) en un solo paso.
   - Si prefieres hacerlo en dos pasos (por ejemplo para revisar la estructura antes de
     cargar datos), importa primero [`01_esquema.sql`](01_esquema.sql) y después
     [`02_datos.sql`](02_datos.sql).
   - `00_crear_bd_local.sql` **no se usa en Hostinger** (el usuario del hosting no tiene
     permiso para crear bases de datos); solo sirve si alguna vez montas esto en un VPS
     propio o quieres recrear la base en otra XAMPP desde cero.
4. Verifica que las 8 tablas aparecen con datos (phpMyAdmin > la base > "Estructura"
   muestra el conteo de filas de cada tabla).

**Los archivos subidos (fotos, PDFs, audio) no van en el SQL.** Sube la carpeta
`uploads/` completa por FTP/Administrador de archivos junto con el resto del código
(paso 2) — son unos 23 MB en total.

## 4. Configurar las variables de entorno

1. En el Administrador de archivos, dentro de `public_html`, crea un archivo nuevo
   llamado exactamente `.env` (copia el contenido de `.env.example` y complétalo con
   los datos reales que anotaste en el paso 3):

   ```
   DB_HOST=localhost
   DB_NAME=u123456789_revista
   DB_USER=u123456789_revista
   DB_PASS=la_contraseña_que_elegiste
   DB_CHARSET=utf8mb4
   APP_ENV=production
   ```

2. Guarda. Gracias al `.htaccess` de `/config/` y a la regla en el `.htaccess` raíz,
   este archivo no es accesible por navegador, pero **de todas formas no lo compartas
   ni lo subas a ningún repositorio público**.

## 5. Revisar las cuentas de usuario antes de anunciar el sitio

La base de datos que se importa (paso 3) trae las cuentas creadas durante las pruebas
en local. Entra a `phpMyAdmin` (o a `https://tu-dominio.com/admin/` una vez arriba,
sección **Usuarios**) y revisa la tabla `usuarios`: actualmente son 5 cuentas (3 admin,
1 editor, 1 redactor). Para cada una que vaya a usarse en producción, cambia su
contraseña desde el panel (**Usuarios > editar**) a una nueva y segura; borra las que
sean solo de prueba y no correspondan a una persona real del equipo.

## 6. Correo (recuperación de contraseña)

`admin/forgot_password.php` usa la función `mail()` de PHP. Hostinger sí trae un
servidor de correo saliente configurado, así que debería funcionar tal cual —
pero conviene probarlo una vez desplegado (Sección 8, punto 3): si el correo no
llega o cae en spam, la alternativa es configurar SMTP con las credenciales del
correo del propio hosting (hPanel > Correos electrónicos) usando PHPMailer; avísame
si quieres que lo agregue.

Con `APP_ENV=production` en el `.env`, el enlace de recuperación **nunca** se muestra
en pantalla (antes sí se mostraba si el correo fallaba) — solo llega por correo.

## 7. robots.txt y sitemap

Edita `robots.txt` (raíz del proyecto) y reemplaza `dialogoydesarrollo.pe` por el
dominio real una vez lo tengas:

```
Sitemap: https://tu-dominio-real.com/public/sitemap.php
```

## 8. Checklist final post-despliegue

1. `https://tu-dominio.com/` redirige a `/public/` y muestra la portada del sitio.
2. `https://tu-dominio.com/admin/` muestra el login del panel.
3. Login con cada rol (admin, editor, redactor) y prueba: crear/editar contenido,
   ocultar/mostrar, y que un reportaje oculto no aparezca en `/public/reportajes.php`
   ni sea accesible por `/public/reportaje.php?id=X` (debe dar 404).
4. Prueba "¿Olvidaste tu contraseña?" con un correo real y confirma que llega
   (revisa spam) y que el enlace funciona.
5. `https://tu-dominio.com/config/conexion.php` y `https://tu-dominio.com/.env` deben
   dar **403 Forbidden** (no cargar ni mostrar nada).
6. `https://tu-dominio.com/public/sitemap.php` responde XML válido.
7. Sube/edita un reportaje con foto para confirmar que `uploads/` tiene permisos de
   escritura (Hostinger normalmente ya da 755/775 por defecto; si falla, ajusta
   permisos de `uploads/` desde el Administrador de archivos).
