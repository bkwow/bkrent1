<?php
/**
 * header.php
 * Parte superior del layout principal.
 */
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= $pageTitle ?? 'Rent a Car' ?></title>
    
    <!-- Bootstrap y DataTables CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.datatables.net/1.13.7/css/dataTables.bootstrap5.min.css">
    <link rel="stylesheet" href="https://cdn.datatables.net/responsive/2.5.0/css/responsive.bootstrap5.min.css">
    <!-- NUEVO: CSS para los botones de exportación -->
    <link rel="stylesheet" href="https://cdn.datatables.net/buttons/2.4.2/css/buttons.bootstrap5.min.css">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/sweetalert2@11/dist/sweetalert2.min.css">
    <!-- Estilos del Tema y Personalizados -->
    <link rel="stylesheet" media="screen, print" href="public/css/vendors.bundle.css">
    <link rel="stylesheet" href="public/css/styles.css?v=<?php echo time(); ?>">
    <link rel="stylesheet" href="public/css/propio.css?v=<?php echo time(); ?>">


 

</head>
<body> <!-- CORRECCIÓN: Se eliminó la clase 'sidebar-toggled' de aquí -->
    <header class="top-header sticky-top">
        <nav class="navbar navbar-expand-lg">
            <div class="container-fluid">
                <div class="d-flex align-items-center">
                    <a href="index.php?page=dashboard" class="navbar-brand text-dark text-decoration-none d-none d-lg-block me-3">
                        <i class="fas fa-car me-2"></i>
                        <span class="fw-bold">LESLEY RENT A CAR</span>
                    </a>
                    <button class="btn btn-light" id="sidebarToggle"><i class="fas fa-bars"></i></button>
                </div>
                <div class="ms-auto d-flex align-items-center">
                    <div class="dropdown">
                        <a href="#" class="d-block link-dark text-decoration-none dropdown-toggle" data-bs-toggle="dropdown" aria-expanded="false">
                            <span class="d-none d-sm-inline mx-2"><?= htmlspecialchars($_SESSION['user_name'] ?? 'Usuario') ?></span>
                            <img src="public/img/user-placeholder.png" alt="user" class="rounded-circle" width="32" height="32">
                        </a>
                        <ul class="dropdown-menu dropdown-menu-end text-small">
                            <li><a class="dropdown-item" href="#">Mi Perfil</a></li>
                            <li><hr class="dropdown-divider"></li>
                            <li><a class="dropdown-item" href="index.php?action=logout">Cerrar Sesión</a></li>
                        </ul>
                    </div>
                </div>
            </div>
        </nav>
    </header>
    <!-- Abre el contenedor principal del layout -->
    <div id="layoutSidenav">