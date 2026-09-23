<?php
/**
 * La raiz del sitio debe mostrar el sitio publico, no el panel de administracion.
 * El panel sigue disponible directamente en /admin/.
 */
header('Location: public/');
exit;
