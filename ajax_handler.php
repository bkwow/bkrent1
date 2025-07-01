<?php
// ajax_handler.php
// Maneja peticiones AJAX para el sistema de llamadas y reservas.

session_start();
header('Content-Type: application/json');

// --- Cargar Dependencias Esenciales ---
require_once 'src/config/database.php';
require_once 'src/core/functions.php';
require_once 'src/core/Database.php';

// --- Seguridad Básica ---
if (!isLoggedIn()) {
	echo json_encode(['error' => 'Acceso no autorizado. Por favor, inicie sesión.']);
	exit();
}

$action = $_POST['action'] ?? '';

switch ($action) {

	case 'search_customer_by_phone':
	require_once 'src/app/models/CustomerModel.php';
	require_once 'src/app/models/CallLogModel.php';

	$phone = $_POST['phone'] ?? '';
	if (empty($phone)) {
		echo json_encode(['error' => 'Número de teléfono requerido.']);
		exit();
	}

		// Sanitizar teléfono (solo + y dígitos)
	$cleanPhone = sanitize_phone_number($phone);

		// Registrar llamada y contar llamadas de hoy
	$callLogModel = new CallLogModel();
	$callLogModel->logCall($cleanPhone, $_SESSION['user_id']);
	$callsToday = $callLogModel->countCallsToday($cleanPhone);

		// Buscar cliente
	$customerModel = new CustomerModel();
	$customer = $customerModel->findByPhone($cleanPhone);

		// Obtener historial de las últimas 5 consultas, con sus fuentes
	$inquiries = $callLogModel->getRecentInquiriesByPhone($cleanPhone);

		// Construir respuesta
	$response = [
		'status'               => 'success',
		'clean_phone'          => $cleanPhone,
		'calls_today'          => $callsToday,
		'customer_found'       => false,
		'customer_data'        => null,
		'rental_history_count' => 0,
		'inquiries'            => $inquiries
	];

	if ($customer) {
		$response['customer_found']       = true;
		$response['customer_data']        = $customer;
		$response['rental_history_count'] = $customerModel->countRentalsByClientId($customer['Client']);
	}

	echo json_encode($response);
	break;

	case 'get_rentals':
		require_once 'src/app/models/RentalModel.php';
		$rentalModel = new RentalModel();
		$filterType  = $_POST['filter_type'] ?? 'todas';
		$data        = $rentalModel->getDataForDataTable($_POST, $filterType);
		echo json_encode($data);
	break;

	case 'check_availability_with_filters':
		require_once 'src/app/models/RentalModel.php';
		$rentalModel = new RentalModel();

		$filters = [
			'start_date'   => $_POST['start_date']   ?? null,
			'end_date'     => $_POST['end_date']     ?? null,
			'vehicle_type' => $_POST['vehicle_type'] ?? 'Cualquiera',
			'transmission' => $_POST['transmission'] ?? 'Cualquiera',
			'fuel'         => $_POST['fuel']         ?? 'Cualquiera'
		];

		if (empty($filters['start_date']) || empty($filters['end_date'])) {
			echo json_encode(['success' => false, 'message' => 'Las fechas son requeridas.']);
			exit();
		}

		try {
			$available = $rentalModel->getAvailableCarsWithFilters($filters);
			echo json_encode(['success' => true, 'data' => $available]);
		} catch (Exception $e) {
			echo json_encode(['success' => false, 'message' => $e->getMessage()]);
		}
	break;

	case 'save_call_inquiry':
		require_once 'src/app/models/CallLogModel.php';
		$callLogModel = new CallLogModel();

			// Sanitizar teléfono
		if (isset($_POST['phone'])) {
			$_POST['phone'] = sanitize_phone_number($_POST['phone']);
		}

			// Recoger array de fuentes (sources[])
		$sources = $_POST['sources'] ?? [];

			// Guardar consulta con sus fuentes
		$result = $callLogModel->saveInquiryDetails(array_merge($_POST, [
			'sources' => $sources
		]));

		echo json_encode($result);
	break;



	/* == MODULO DE VEHICULOS ==*/
	case 'get_vehicles':
		require_once 'src/app/models/VehicleModel.php';
		$vehicleModel = new VehicleModel();
		echo json_encode($vehicleModel->getVehiclesForDataTable($_POST));
		break;

		case 'get_vehicle':
		require_once 'src/app/models/VehicleModel.php';
		$vehicleModel = new VehicleModel();
		$id = $_POST['id'] ?? 0;
		echo json_encode(['success' => true, 'data' => $vehicleModel->getVehicleById((int)$id)]);
		break;

		case 'save_vehicle':
		require_once 'src/app/models/VehicleModel.php';
		$vehicleModel = new VehicleModel();
		$id = $_POST['id'] ?? null;
		try {
			if (empty($id)) {
					// Al crear, pasamos los datos del formulario y los archivos
				$vehicleModel->createVehicle($_POST, $_FILES);
				echo json_encode(['success' => true, 'message' => 'Vehículo registrado con éxito.']);
			} else {
					// CORRECCIÓN: Al actualizar, ahora también pasamos el array $_FILES
				$vehicleModel->updateVehicle((int)$id, $_POST, $_FILES);
				echo json_encode(['success' => true, 'message' => 'Vehículo actualizado con éxito.']);
			}
		} catch (Exception $e) {
			echo json_encode(['success' => false, 'message' => 'Error al guardar el vehículo: ' . $e->getMessage()]);
		}
	break;

	case 'delete_vehicle':
		require_once 'src/app/models/VehicleModel.php';
		$vehicleModel = new VehicleModel();
		$id = $_POST['id'] ?? 0;
		if ($vehicleModel->deleteVehicle((int)$id)) {
			echo json_encode(['success' => true, 'message' => 'Vehículo eliminado con éxito.']);
		} else {
			echo json_encode(['success' => false, 'message' => 'No se pudo eliminar el vehículo.']);
		}
	break;

	case 'update_vehicle_status':
	require_once 'src/app/models/VehicleModel.php';
	$vehicleModel = new VehicleModel();
	$id = $_POST['id'] ?? 0;
	$status = $_POST['status'] ?? 0;
	if ($vehicleModel->updateVehicleStatus((int)$id, (int)$status)) {
		echo json_encode(['success' => true, 'message' => 'Estado actualizado.']);
	} else {
		echo json_encode(['success' => false, 'message' => 'No se pudo actualizar el estado.']);
	}
	break;
	/*=====================*/



	// --- ACCIONES PARA EL MÓDULO DE USUARIOS ---

	case 'get_users':
	require_once 'src/app/models/UserModel.php';
	$userModel = new UserModel();
	echo json_encode($userModel->getUsersForDataTable($_POST));
	break;

	case 'get_user':
	require_once 'src/app/models/UserModel.php';
	$userModel = new UserModel();
	$id = $_POST['id'] ?? 0;
	echo json_encode(['success' => true, 'data' => $userModel->getUserById((int)$id)]);
	break;

	case 'save_user':
	require_once 'src/app/models/UserModel.php';
	$userModel = new UserModel();
	$id = $_POST['id'] ?? null;
		// Validación de contraseña solo al crear
	if (empty($id) && empty($_POST['password'])) {
		echo json_encode(['success' => false, 'message' => 'La contraseña es obligatoria para nuevos usuarios.']);
		exit();
	}
	try {
		if (empty($id)) {
			$userModel->createUser($_POST);
			echo json_encode(['success' => true, 'message' => 'Usuario registrado con éxito.']);
		} else {
			$userModel->updateUser((int)$id, $_POST);
			echo json_encode(['success' => true, 'message' => 'Usuario actualizado con éxito.']);
		}
	} catch (Exception $e) {
		echo json_encode(['success' => false, 'message' => 'Error al guardar: ' . $e->getMessage()]);
	}
	break;

	case 'delete_user':
	require_once 'src/app/models/UserModel.php';
	$userModel = new UserModel();
	$id = $_POST['id'] ?? 0;
	if ($userModel->deleteUser((int)$id)) {
		echo json_encode(['success' => true, 'message' => 'Usuario desactivado con éxito.']);
	} else {
		echo json_encode(['success' => false, 'message' => 'No se pudo desactivar el usuario.']);
	}
	break;
	/*======*/


// En ajax_handler.php, dentro del switch ($action)

	case 'get_all_makes':
	require_once 'src/app/models/ReportModel.php';
	$reportModel = new ReportModel();
	echo json_encode(['success' => true, 'data' => $reportModel->getAllMakes()]);
	break;

	// ==========================================================
	// NUEVO: Case para obtener todas las Clases
	// ==========================================================
	case 'get_all_classes':
	require_once 'src/app/models/ReportModel.php';
	$reportModel = new ReportModel();
	echo json_encode(['success' => true, 'data' => $reportModel->getAllClasses()]);
	break;

	case 'get_models_by_make':
	require_once 'src/app/models/ReportModel.php';
	$reportModel = new ReportModel();
	$makeName = $_POST['make'] ?? '';
	echo json_encode(['success' => true, 'data' => $reportModel->getModelsByMake($makeName)]);
	break;

	case 'get_report_data':
	require_once 'src/app/models/ReportModel.php';
	$reportModel = new ReportModel();
	echo json_encode(['data' => $reportModel->getFilteredRentals($_POST)]);
	break;


	case 'get_sales_comparison':
	require_once 'src/app/models/ReportModel.php';
	$reportModel = new ReportModel();
		$year = $_POST['year'] ?? date('Y'); // Usar año actual por defecto
		$data = $reportModel->getSalesComparison((int)$year);
		echo json_encode(['success' => true, 'data' => $data]);
		break;
		case 'get_most_rented_vehicles':
		require_once 'src/app/models/ReportModel.php';
		$reportModel = new ReportModel();
		$year = $_POST['year'] ?? date('Y');
		$data = $reportModel->getMostRentedByModel((int)$year);
		echo json_encode(['success' => true, 'data' => $data]);
		break;
// ==========================================================
	// NUEVO: Case para obtener los datos por CLASE
	// ==========================================================
		case 'get_most_rented_by_class':
		require_once 'src/app/models/ReportModel.php';
		$reportModel = new ReportModel();
		$year = $_POST['year'] ?? date('Y');
		$data = $reportModel->getMostRentedByClass((int)$year);
		echo json_encode(['success' => true, 'data' => $data]);
		break;
		case 'get_rentals_by_state':
		require_once 'src/app/models/ReportModel.php';
		$reportModel = new ReportModel();
		$year = $_POST['year'] ?? date('Y');
		$data = $reportModel->getRentalsByState((int)$year);
		echo json_encode(['success' => true, 'data' => $data]);
		break;

		case 'get_rentals_by_month_state':
		require_once 'src/app/models/ReportModel.php';
		$reportModel = new ReportModel();
		$year = $_POST['year'] ?? date('Y');
		$data = $reportModel->getRentalsByMonthAndState((int)$year);
		echo json_encode(['success' => true, 'data' => $data]);
		break;
		case 'get_rentals_by_country':
		require_once 'src/app/models/ReportModel.php';
		$reportModel = new ReportModel();
		
		$year = $_POST['year'] ?? date('Y'); // Usa el año actual si no se especifica
		
		$data = $reportModel->getRentalsByCountry((int)$year);
		
		echo json_encode(['success' => true, 'data' => $data]);
		break;

	/**
	 * Acción para obtener los datos de detalle (drill-down) para un país específico.
	 * Devuelve las rentas por departamento y el top 20 de vehículos.
	 */
	case 'get_geo_drilldown':
	require_once 'src/app/models/ReportModel.php';
	$reportModel = new ReportModel();

	$year = $_POST['year'] ?? date('Y');
	$country = $_POST['country'] ?? '';

	if (empty($country)) {
		echo json_encode(['success' => false, 'message' => 'El país es requerido.']);
		exit();
	}

	$data = $reportModel->getGeographicDrilldown((int)$year, $country);

	echo json_encode(['success' => true, 'data' => $data]);
	break;



	case 'get_vehicle_rental_history':
	require_once 'src/app/models/VehicleModel.php';
	$vehicleModel = new VehicleModel();
	$id = $_POST['id'] ?? 0;
	$history = $vehicleModel->getRentalHistory((int)$id);
	echo json_encode(['data' => $history]); // Formato que espera DataTables
	break;



	case 'get_text_report_data':
	require_once 'src/app/models/ReportModel.php';
	$reportModel = new ReportModel();
	$data = $reportModel->getTextReportData();
	echo json_encode(['success' => true, 'data' => $data]);
	break;

	case 'create_full_reservation':
	require_once 'src/app/models/RentalModel.php';
	$rentalModel = new RentalModel();
	
	// CORRECCIÓN: Ahora pasamos $_POST completo, que incluirá el inquiry_id
	$result = $rentalModel->createFullReservation($_POST, $_FILES);
	
	echo json_encode($result);
	break;


	
	default:
	echo json_encode(['error' => 'Acción no válida o no especificada.']);
	break;
}
