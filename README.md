# DDP Noticias — Diálogo y Desarrollo Perú

Sistema de gestión de contenidos (CMS) y sitio de noticias para **Diálogo y Desarrollo Perú**: un portal de periodismo independiente sobre minería, canon y desarrollo territorial, con un panel de administración para el equipo de redacción.

| | URL |
|---|---|
| **Sitio público** | https://palegoldenrod-scorpion-651987.hostingersite.com/public/ |
| **Panel de administración** | https://palegoldenrod-scorpion-651987.hostingersite.com/admin/ |
| **Sitemap** | https://palegoldenrod-scorpion-651987.hostingersite.com/public/sitemap.php |

## Características

**Sitio público**
- Portada con reportaje destacado, reportajes recientes, noticias, boletín NTEP, podcast y videos (carruseles).
- Listado de reportajes con paginación y filtro por mes; detalle con galería y PDF adjunto.
- Reproductor de podcast propio (con respaldo al reproductor de Spotify).
- SEO: meta description, Open Graph / Twitter Cards, URL canónica, `robots.txt` y `sitemap.php` dinámico.
- Solo se muestra contenido en estado **publicado**; borradores y ocultos nunca son visibles ni accesibles por URL directa.

**Panel de administración**
- CRUD de reportajes, boletines, noticias, podcasts y videos, más gestión de autores y usuarios.
- Editor de texto enriquecido (CKEditor) para el desarrollo de los reportajes.
- Estados de publicación: **borrador**, **publicado** y **oculto**. El contenido no se elimina; solo se oculta o se muestra.
- Diseño responsivo con menú lateral tipo hamburguesa en móviles.
- Recuperación de contraseña por correo con enlace de un solo uso (válido 30 minutos).

## Roles y permisos

| Rol | Permisos |
|---|---|
| **Admin** | Acceso total, incluida la gestión de usuarios y autores. |
| **Editor** | Crear y editar reportajes, boletines, noticias, podcasts y videos; ocultar y mostrar contenido. |
| **Redactor** | Solo crear y editar reportajes (incluye guardar borradores). |

Los permisos se validan en el servidor en cada página y acción (`config/permisos.php`), no solo en el menú.

## Tecnologías

- **Backend:** PHP 8.1+ (probado en 8.2), PDO con consultas preparadas.
- **Base de datos:** MySQL / MariaDB (`utf8mb4`).
- **Frontend público:** Bootstrap 4, jQuery, Owl Carousel, Magnific Popup.
- **Panel:** tema *Focus* (Bootstrap 4) y CKEditor 4.
- **Servidor:** Apache con `.htaccess` (sin framework ni Composer).

## Estructura del proyecto

```
├── admin/          Panel de administración (login, CRUD, roles)
├── public/         Sitio público (portada, reportajes, podcasts, sitemap)
├── config/         Conexión a la BD, .env, permisos, helpers y layout del panel
├── uploads/        Archivos subidos desde el panel (no se versionan)
├── deploy/         Scripts SQL y guía de despliegue
├── css/ js/ vendor/ images/ icons/   Recursos del tema del panel
├── .env.example    Plantilla de variables de entorno
├── .htaccess       Bloqueos de seguridad y redirección a HTTPS
└── robots.txt
```

## Variables de entorno

Se leen desde un archivo `.env` en la raíz (ver [.env.example](.env.example)). Si no existe, se usan los valores de XAMPP para desarrollo local.

| Variable | Descripción | Ejemplo |
|---|---|---|
| `DB_HOST` | Servidor MySQL | `localhost` |
| `DB_NAME` | Nombre de la base de datos | `u123456789_revista` |
| `DB_USER` | Usuario de MySQL | `u123456789_revista` |
| `DB_PASS` | Contraseña de MySQL | — |
| `DB_CHARSET` | Juego de caracteres | `utf8mb4` |
| `APP_ENV` | `production` oculta errores detallados y el enlace de recuperación en pantalla | `production` |

> El archivo `.env` nunca debe subirse al repositorio (ya está en `.gitignore`).

## Instalación local (XAMPP)

1. Copia el proyecto en `C:\xampp\htdocs\theme` y arranca **Apache** y **MySQL**.
2. Crea la base de datos e importa el esquema y los datos:
   ```bash
   mysql -u root -e "CREATE DATABASE revista_digital CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;"
   mysql -u root revista_digital < deploy/03_completo_esquema_y_datos.sql
   ```
3. Abre `http://localhost/theme/public/` (sitio) o `http://localhost/theme/admin/` (panel).

## Despliegue en Hostinger

1. **Base de datos:** en hPanel → *Bases de datos → MySQL*, crea una base de datos y un usuario. Anota nombre, usuario y contraseña.
2. **Importar datos:** en phpMyAdmin, selecciona la base creada → *Importar* → sube [`deploy/03_completo_esquema_y_datos.sql`](deploy/03_completo_esquema_y_datos.sql).
   - Si tu base ya existía de una versión anterior, ejecuta solo [`deploy/04_migracion_borradores.sql`](deploy/04_migracion_borradores.sql).
3. **Subir el código:** en el Administrador de archivos (o mediante Git / FTP), sube el contenido del proyecto a `public_html`, de modo que `admin/`, `config/` y `public/` queden al mismo nivel. No subas la carpeta `deploy/`.
4. **Subir los archivos multimedia:** copia el contenido de `uploads/` (fotos, PDFs y audios), que no viaja por Git.
5. **Crear el `.env`** en la raíz con los datos del paso 1 y `APP_ENV=production`.
6. **Verificar** con la lista de comprobación de abajo.

Guía detallada en [deploy/DEPLOY.md](deploy/DEPLOY.md).

### Lista de comprobación posterior al despliegue

- [ ] `/public/` carga la portada con imágenes.
- [ ] `/admin/` muestra el login e inicia sesión con cada rol.
- [ ] Un reportaje oculto o en borrador no aparece en el sitio ni por URL directa.
- [ ] `/config/conexion.php` y `/.env` responden **403**.
- [ ] `/public/sitemap.php` devuelve XML válido.
- [ ] Se puede subir una foto desde el panel (permisos de `uploads/`).
- [ ] "¿Olvidaste tu contraseña?" envía el correo.

## Seguridad

- Contraseñas con `password_hash()` (bcrypt) y verificación con `password_verify()`.
- Consultas preparadas (PDO) contra inyección SQL y salida escapada contra XSS.
- Roles verificados en el servidor; el rol se refresca desde la BD en cada petición.
- Recuperación de contraseña con token aleatorio guardado como hash SHA-256, con expiración y uso único.
- `.htaccess` bloquea `config/`, `.env`, `.sql` y `.md`, desactiva la ejecución de scripts en `uploads/` y fuerza HTTPS fuera de localhost.
- Errores detallados ocultos con `APP_ENV=production`.

Antes de publicar, cambia las contraseñas de las cuentas de prueba desde **Usuarios** en el panel.

