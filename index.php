<?php
// index.php - VERSIÓN COMPLETA Y CORREGIDA

header('Content-Type: text/html; charset=utf-8');
session_start();

// --- Carga de Archivos Esenciales ---
require_once 'src/config/database.php';
require_once 'src/core/functions.php';
require_once 'src/core/Database.php';

// --- Carga de Modelos ---
require_once 'src/app/models/UserModel.php';
require_once 'src/app/models/ConfigModel.php';
require_once 'src/app/models/MenuModel.php';
require_once 'src/app/models/RentalModel.php';
require_once 'src/app/models/CustomerModel.php';
require_once 'src/app/models/CallLogModel.php';
require_once 'src/app/models/VehicleModel.php';

// --- Inicialización de Modelos ---
$userModel = new UserModel();
$configModel = new ConfigModel();
$menuModel = new MenuModel();
$rentalModel = new RentalModel();
$customerModel = new CustomerModel();
$callLogModel = new CallLogModel();
$vehicleModel = new VehicleModel();

// --- Obtener Configuración Global ---
$settings = $configModel->getSettings();

// --- Manejo de Acciones (como login/logout) ---
$action = $_GET['action'] ?? null;
if ($action) {
    switch ($action) {
        case 'do_login':
            $identifier = $_POST['identifier'] ?? '';
            $password = $_POST['password'] ?? '';
            $user = $userModel->verifyPassword($identifier, $password);
            if ($user) {
                $_SESSION['user_id'] = $user['id'];
                $_SESSION['user_role'] = $user['role'];
                // ==========================================================
                // CORRECCIÓN: Guardamos el nombre del usuario en la sesión
                // Se usa el nombre del perfil, o el username como alternativa.
                // ==========================================================
                $_SESSION['user_name'] = $user['nombre'];
                
                redirect('index.php?page=dashboard');
            } else {
                set_flash_message('error', 'Usuario o contraseña incorrectos.');
                redirect('index.php?page=login');
            }
            break;
        case 'logout':
            session_destroy();
            redirect('index.php?page=login');
            break;
    }
}

// --- Enrutador Principal de Páginas ---
$page = $_GET['page'] ?? 'login';

$pagesRequiringAuth = [
    'dashboard', 'reservas', 'new_rental', 
    'new_call_triage', 'vehiculos', 'usuarios','reportes','vehicle_history', 'text_report'
];

if (in_array($page, $pagesRequiringAuth) && !isLoggedIn()) {
    redirect('index.php?page=login');
}

$menuCategories = [];
$itemsByCategory = [];
if (in_array($page, $pagesRequiringAuth)) {
    $menuCategories = $menuModel->getMenuCategories();
    $menuItems = $menuModel->getMenuItems();
    foreach ($menuItems as $item) {
        $itemsByCategory[$item['tit_padre']][] = $item;
    }
}

switch ($page) {
    case 'login':
        $pageTitle = 'Iniciar Sesión';
        require 'src/app/views/pages/login.php';
        break;

    case 'dashboard':
    case 'reservas':
    case 'new_rental':
    case 'new_call_triage':
    case 'vehiculos':
    case 'reportes': // <-- Añadir aquí
    case 'usuarios':
     case 'text_report':
    case 'vehicle_history':


        $pageTitle = ucfirst($page) . ' | Rent a Car';
        require 'src/app/views/partials/header.php';
        echo '<div id="layoutSidenav_content_wrapper">';
        require 'src/app/views/partials/sidebar.php';
        echo '<main id="layoutSidenav_content">';
        
        if (file_exists('src/app/views/pages/' . $page . '.php')) {
            require 'src/app/views/pages/' . $page . '.php';
        } else {
            http_response_code(404);
            require 'src/app/views/pages/404.php';
        }
        
        echo '</main>';
        echo '</div>';
        require 'src/app/views/partials/footer.php';
        break;
        
    default:
        http_response_code(404);
        $pageTitle = 'Página no encontrada';
        require 'src/app/views/partials/header.php';
        require 'src/app/views/pages/404.php';
        require 'src/app/views/partials/footer.php';
        break;
}
?>
