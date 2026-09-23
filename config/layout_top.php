<?php
/**
 * Cabecera comun del panel (head, nav-header, header, sidebar, apertura de content-body).
 * Variables esperadas antes del include:
 *   $page_title   (string) Titulo de la pagina / breadcrumb.
 *   $active_menu  (string) Clave del item de menu activo (ver $menu_sections abajo).
 *   $extra_head   (string, opcional) HTML adicional para el <head> (CSS de plugins).
 */
require_once __DIR__ . '/permisos.php';

if (!isset($page_title)) {
    $page_title = 'Panel';
}
?>
<!DOCTYPE html>
<html lang="es">

<head>
    <meta charset="utf-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width,initial-scale=1">
    <title><?php echo htmlspecialchars($page_title); ?> - Dialogo y Desarrollo</title>
    <!-- Favicon icon -->
    <link rel="icon" type="image/png" sizes="16x16" href="../images/favicon.png">
    <link href="../css/style.css" rel="stylesheet">
    <?php if (!empty($extra_head)) { echo $extra_head; } ?>
</head>

<body>

    <!--*******************
        Preloader start
    ********************-->
    <div id="preloader">
        <div class="sk-three-bounce">
            <div class="sk-child sk-bounce1"></div>
            <div class="sk-child sk-bounce2"></div>
            <div class="sk-child sk-bounce3"></div>
        </div>
    </div>
    <!--*******************
        Preloader end
    ********************-->


    <!--**********************************
        Main wrapper start
    ***********************************-->
    <div id="main-wrapper">

        <!--**********************************
            Nav header start
        ***********************************-->
        <div class="nav-header">
            <a href="index.php" class="brand-logo">
                <span class="logo-abbr-text">DyD</span>
                <span class="brand-title-text">Dialogo y<br>Desarrollo</span>
            </a>
            <div class="nav-control">
                <div class="hamburger">
                    <span class="line"></span><span class="line"></span><span class="line"></span>
                </div>
            </div>
        </div>
        <!--**********************************
            Nav header end
        ***********************************-->

        <!--**********************************
            Header start
        ***********************************-->
        <div class="header">
            <div class="header-content">
                <nav class="navbar navbar-expand">
                    <div class="collapse navbar-collapse justify-content-between">
                        <div class="header-left">
                        </div>

                        <ul class="navbar-nav header-right">
                            <li class="nav-item dropdown header-profile">
                                <a class="nav-link" href="#" role="button" data-toggle="dropdown">
                                    <i class="mdi mdi-account"></i>
                                    <span class="ml-2 d-none d-md-inline"><?php echo htmlspecialchars($_SESSION['usuario_nombre'] ?? ''); ?></span>
                                </a>
                                <div class="dropdown-menu dropdown-menu-right">
                                    <a href="./logout.php" class="dropdown-item">
                                        <i class="icon-key"></i>
                                        <span class="ml-2">Logout </span>
                                    </a>
                                </div>
                            </li>
                        </ul>
                    </div>
                </nav>
            </div>
        </div>
        <!--**********************************
            Header end
        ***********************************-->

        <!--**********************************
            Sidebar start
        ***********************************-->
        <div class="quixnav">
            <div class="quixnav-scroll">
                <ul class="metismenu" id="menu">
                    <?php
                    $menu_sections = [
                        'Menu Principal' => [
                            ['key' => 'dashboard', 'label' => 'Dashboard', 'href' => 'index.php', 'recurso' => null],
                        ],
                        'Contenido' => [
                            ['key' => 'reportajes', 'label' => 'Reportajes', 'href' => 'reportajes.php', 'recurso' => 'reportajes'],
                            ['key' => 'boletines', 'label' => 'Boletines', 'href' => 'boletines.php', 'recurso' => 'boletines'],
                            ['key' => 'noticias', 'label' => 'Noticias', 'href' => 'noticias.php', 'recurso' => 'noticias'],
                            ['key' => 'podcasts', 'label' => 'Podcasts', 'href' => 'podcasts.php', 'recurso' => 'podcasts'],
                            ['key' => 'videos', 'label' => 'Videos', 'href' => 'videos.php', 'recurso' => 'videos'],
                        ],
                        'Multimedia' => [
                            ['key' => 'archivos', 'label' => 'Archivos', 'href' => 'archivos.php', 'recurso' => 'archivos'],
                        ],
                        'Personas' => [
                            ['key' => 'usuarios', 'label' => 'Usuarios', 'href' => 'usuarios.php', 'recurso' => 'usuarios'],
                            ['key' => 'autores', 'label' => 'Autores', 'href' => 'autores.php', 'recurso' => 'autores'],
                        ],
                    ];
                    foreach ($menu_sections as $group => $items):
                        // Solo items visibles para el rol actual (dashboard siempre visible).
                        $items = array_filter($items, fn($it) => $it['recurso'] === null || puede($it['recurso'], 'ver'));
                        if (empty($items)) continue;
                        ?>
                        <li class="nav-label"><b><?php echo htmlspecialchars($group); ?></b></li>
                        <?php foreach ($items as $item):
                            $isActive = (isset($active_menu) && $active_menu === $item['key']);
                            ?>
                            <li class="<?php echo $isActive ? 'mm-active' : ''; ?>">
                                <a href="<?php echo htmlspecialchars($item['href']); ?>"><span class="nav-text"><?php echo htmlspecialchars($item['label']); ?></span></a>
                            </li>
                        <?php endforeach; ?>
                    <?php endforeach; ?>
                </ul>
            </div>
        </div>
        <!--**********************************
            Sidebar end
        ***********************************-->

        <!--**********************************
            Content body start
        ***********************************-->
        <div class="content-body">
            <div class="container-fluid">
                <div class="row page-titles mx-0">
                    <div class="col-sm-6 p-md-0">
                        <div class="welcome-text">
                            <h4><?php echo htmlspecialchars($page_title); ?></h4>
                        </div>
                    </div>
                    <div class="col-sm-6 p-md-0 justify-content-sm-end mt-2 mt-sm-0 d-flex">
                        <ol class="breadcrumb">
                            <li class="breadcrumb-item"><a href="index.php">Dashboard</a></li>
                            <li class="breadcrumb-item active"><?php echo htmlspecialchars($page_title); ?></li>
                        </ol>
                    </div>
                </div>
                <?php if (!empty($_SESSION['flash'])): ?>
                    <?php $flash = $_SESSION['flash']; unset($_SESSION['flash']); ?>
                    <div class="alert alert-<?php echo $flash['type'] === 'error' ? 'danger' : 'success'; ?> alert-dismissible fade show" role="alert">
                        <?php echo htmlspecialchars($flash['message']); ?>
                        <button type="button" class="close" data-dismiss="alert" aria-label="Close"><span aria-hidden="true">&times;</span></button>
                    </div>
                <?php endif; ?>
