-- Migracion: agrega el estado "borrador" a reportajes.
-- Uso: SOLO si tu base de datos en Hostinger ya existia ANTES de esta funcion
-- (es decir, ya habias importado 03_completo_esquema_y_datos.sql en una sesion
-- anterior). Si vas a importar la base por primera vez, no necesitas este
-- archivo: 03_completo_esquema_y_datos.sql ya incluye estos cambios.
--
-- Es segura de ejecutar sobre una base con datos reales: no borra ni modifica
-- filas existentes, solo cambia la definicion de 3 columnas de "reportajes".

ALTER TABLE reportajes
  MODIFY COLUMN fecha_publicacion DATE NULL,
  MODIFY COLUMN autor_id INT UNSIGNED NULL,
  MODIFY COLUMN estado ENUM('publicado','oculto','borrador') NOT NULL DEFAULT 'borrador';
