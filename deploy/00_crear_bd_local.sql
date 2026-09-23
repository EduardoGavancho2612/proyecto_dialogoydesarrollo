-- Uso: solo si vas a crear la base de datos tu mismo (servidor propio, VPS, XAMPP).
-- En Hostinger la base de datos se crea desde hPanel > Bases de datos > MySQL
-- (el usuario de hosting no tiene permiso para ejecutar CREATE DATABASE), asi que
-- en Hostinger este archivo NO se usa: empieza directo por 01_esquema.sql.

CREATE DATABASE IF NOT EXISTS revista_digital
    CHARACTER SET utf8mb4
    COLLATE utf8mb4_unicode_ci;
