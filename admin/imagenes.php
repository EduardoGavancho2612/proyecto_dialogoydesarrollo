<?php
/**
 * La seccion "Imagenes" (listado global de fotos de galeria) se retiro del panel.
 * Las fotos de cada reportaje se administran desde su propio formulario
 * (reportaje_form.php -> "Agregar foto a la galeria de este reportaje").
 */
session_start();
header('Location: reportajes.php');
exit;
