<?php

class RentalModel {
    private $pdo;

    public function __construct() {
        $this->pdo = Database::getConnection();
    }

    public function getDataForDataTable(array $request, string $filterType = 'todas') {
        
        // CORRECCIÓN DEFINITIVA: Se seleccionan SOLO las columnas que sabemos que existen.
        $selectColumns = "
            r.confnr, r.invoice, r.STARTTIME, r.ENDTIME, r.amount, r.vehicle,
            r.pafirstname, r.palastname, r.drhphone,
            c.License AS car_plate, c.Make AS car_make, c.Model AS car_model, c.year AS car_year,
            ci.picloc AS car_image
        ";
        
        $baseSql = "FROM rentals r 
                    LEFT JOIN cars c ON r.vehicle = c.id
                    LEFT JOIN cars_img ci ON c.id = ci.id_rel";
        
        $whereClauses = ["r.cancelled = 0"];
        $bindings = [];
        
        // La lógica de filtros se mantiene
        switch ($filterType) {
            case 'hoy': $whereClauses[] = "DATE(r.timestamp) = CURDATE()"; break;
            case 'en_renta': $whereClauses[] = "NOW() BETWEEN r.STARTTIME AND r.ENDTIME"; break;
            case 'entregas_hoy': $whereClauses[] = "DATE(r.STARTTIME) = CURDATE()"; break;
            case 'entregas_manana': $whereClauses[] = "DATE(r.STARTTIME) = DATE_ADD(CURDATE(), INTERVAL 1 DAY)"; break;
            case 'retornos_hoy': $whereClauses[] = "DATE(r.ENDTIME) = CURDATE()"; break;
            case 'retornos_manana': $whereClauses[] = "DATE(r.ENDTIME) = DATE_ADD(CURDATE(), INTERVAL 1 DAY)"; break;
            case 'proximas': $whereClauses[] = "r.STARTTIME > NOW()"; break;
        }

        if (!empty($request['search']['value'])) {
            $searchValue = '%' . $request['search']['value'] . '%';
            $searchClauses = ["r.invoice LIKE ?", "r.confnr LIKE ?", "r.pafirstname LIKE ?", "r.palastname LIKE ?", "c.License LIKE ?", "c.Make LIKE ?", "c.Model LIKE ?"];
            $whereClauses[] = "(" . implode(" OR ", $searchClauses) . ")";
            for ($i = 0; $i < count($searchClauses); $i++) {
                $bindings[] = $searchValue;
            }
        }

        $where = " WHERE " . implode(" AND ", $whereClauses);

        $countSql = "SELECT COUNT(r.confnr) " . $baseSql . $where;
        $totalRecordsStmt = $this->pdo->prepare($countSql);
        $totalRecordsStmt->execute($bindings);
        $totalRecords = $totalRecordsStmt->fetchColumn();
        
        $order = "";
        $columns = [null, 'r.confnr', null, 'r.invoice', 'car_plate', 'car_make', 'car_model', 'car_year', 'r.pafirstname', 'r.palastname', 'r.drhphone', 'r.STARTTIME', 'r.ENDTIME', 'r.amount', null];
        if (isset($request['order'])) {
            $colIndex = intval($request['order'][0]['column']);
            if (isset($columns[$colIndex]) && $columns[$colIndex] !== null) {
                $colName = $columns[$colIndex];
                $direction = strtolower($request['order'][0]['dir']) === 'asc' ? 'ASC' : 'DESC';
                $order = " ORDER BY {$colName} {$direction}";
            }
        } else {
            $order = " ORDER BY r.STARTTIME DESC";
        }

        $dataSql = "SELECT " . $selectColumns . " " . $baseSql . $where . $order . " LIMIT ?, ?";
        $stmt = $this->pdo->prepare($dataSql);
        
        $pdoBindings = $bindings;
        $pdoBindings[] = (int)$request['start'];
        $pdoBindings[] = (int)$request['length'];

        foreach ($pdoBindings as $key => $value) {
            $paramType = is_int($value) ? PDO::PARAM_INT : PDO::PARAM_STR;
            $stmt->bindValue($key + 1, $value, $paramType);
        }
        
        $stmt->execute();
        $results = $stmt->fetchAll(PDO::FETCH_ASSOC);

        $data = [];
        foreach ($results as $row) {
            $actions = '<div class="action-buttons"><a href="#" class="btn btn-circle btn-success" title="Check-out"><i class="fas fa-arrow-up"></i></a><a href="#" class="btn btn-circle btn-info" title="Check-in"><i class="fas fa-arrow-down"></i></a><a href="#" class="btn btn-circle btn-secondary" title="Imprimir Contrato"><i class="fas fa-file-contract"></i></a><a href="#" class="btn btn-circle btn-primary" title="Editar"><i class="fas fa-pencil-alt"></i></a><a href="#" class="btn btn-circle btn-danger" title="Eliminar"><i class="fas fa-trash-alt"></i></a></div>';
            
            // CORRECCIÓN: Eliminada la columna 'mode' y se envían solo los datos que existen.
            $data[] = [
                null,
                "confnr" => e($row['confnr']),
                "car_image" => '<img src="public/vehiculos_flota/thumb/' . e($row['car_image'] ?? 'noimage.jpg') . '" alt="VehÃ­culo" class="car-thumbnail">',
                "invoice" => e($row['invoice']),
                "car_plate" => e($row['car_plate']),
                "car_make" => e($row['car_make']),
                "car_model" => e($row['car_model']),
                "car_year" => $row['car_year'],
                "pafirstname" => e($row['pafirstname']),
                "palastname" => e($row['palastname']),
                "drhphone" => e($row['drhphone']),
                "start_date" => date('d/m/Y', strtotime($row['STARTTIME'])),
                "end_date" => date('d/m/Y', strtotime($row['ENDTIME'])),
                "total" => '$' . number_format($row['amount'], 2),
                "actions" => $actions
            ];
        }

        return [
            "draw"            => intval($request['draw']),
            "recordsTotal"    => intval($totalRecords),
            "recordsFiltered" => intval($totalRecords),
            "data"            => $data
        ];
    }


    // Dentro de la clase RentalModel en RentalModel.php

public function getActiveCars() {
    $sql = "SELECT id, Description FROM cars WHERE active = 1 AND avb = 1 ORDER BY Description ASC";
    $stmt = $this->pdo->prepare($sql);
    $stmt->execute();
    return $stmt->fetchAll(PDO::FETCH_ASSOC);
}

public function createFullReservation($data, $files) {
    $this->pdo->beginTransaction();
    try {
        $customerId = $data['customer_id'];
        $newPhone = $data['new_phone'];
        $customerData = [];

        if (!empty($customerId)) {
            // Cliente existente, solo obtenemos sus datos
            $stmt = $this->pdo->prepare("SELECT * FROM customers WHERE Client = ?");
            $stmt->execute([$customerId]);
            $customerData = $stmt->fetch();
        } else {
            // Cliente nuevo, lo creamos
            $sql = "INSERT INTO customers (drfirstname, drlastname, DREMAIL, drhphone, draddress) VALUES (?, ?, ?, ?, ?)";
            $stmt = $this->pdo->prepare($sql);
            $stmt->execute([
                $data['drfirstname'], $data['drlastname'], $data['DREMAIL'], 
                $data['drhphone'] ?? $newPhone, $data['draddress']
            ]);
            $customerId = $this->pdo->lastInsertId();
            // Guardamos los datos para usarlos en la tabla rentals
            $customerData = $data;
        }

        $keyfield = strtoupper(sprintf('{%08X-%04X-%04X-%04X-%12X}', mt_rand(0, 0xffffffff), mt_rand(0, 0xffff), mt_rand(0, 0xffff), mt_rand(0, 0xffff), mt_rand(0, 0xffffffffffff)));
            
        // CORRECCIÓN: Se añade 'inquiry_id' a la consulta INSERT
        $rentalSql = "INSERT INTO rentals (KEYFIELD, STARTTIME, ENDTIME, Client, vehicle, drfirstname, drlastname, dremail, drhphone, draddress, dr2fname, dr2lastname, dr3name, dr3licnr, inquiry_id) 
                      VALUES (:keyfield, :starttime, :endtime, :client_id, :vehicle_id, :fname, :lname, :email, :phone, :address, :dr2fname, :dr2lname, :dr3name, :dr3licnr, :inquiry_id)";
        
        $this->db->query($rentalSql);
        $this->db->bindMultiple([
            ':keyfield' => $keyfield,
            ':starttime' => $data['start_date'],
            ':endtime' => $data['end_date'],
            ':client_id' => $data['customer_id'], // Asumiendo que ya tienes el ID del cliente
            ':vehicle_id' => $data['vehicle_id'],
            // ... (resto de los bindings de datos del cliente) ...
            ':inquiry_id' => !empty($data['inquiry_id']) ? $data['inquiry_id'] : null // Se guarda el ID de la consulta
        ]);
        $this->db->execute();
        $confnr = $this->db->lastInsertId();

        // Subida de archivos
        $uploadDir = 'public/uploads/documents/';
        if (!is_dir($uploadDir)) { mkdir($uploadDir, 0775, true); }
        
        $docPaths = [];
        $docFields = ['img1', 'img2', 'img01', 'img02', 'img001', 'img002', 'imgd1', 'imgd2', 'pasaporte'];
        foreach ($docFields as $field) {
            $docPaths[$field] = null;
            if (isset($files[$field]) && $files[$field]['error'] === UPLOAD_ERR_OK) {
                $ext = pathinfo($files[$field]['name'], PATHINFO_EXTENSION);
                $filename = "doc_{$confnr}_{$field}_" . time() . ".{$ext}";
                if(move_uploaded_file($files[$field]['tmp_name'], $uploadDir . $filename)) {
                    $docPaths[$field] = $filename;
                }
            }
        }

        $imgSql = "INSERT INTO tbl_img_photos_licencias_doc (id_rel, img1, img2, img01, img02, img001, img002, imgd1, imgd2, ruta) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?)";
        $stmt = $this->pdo->prepare($imgSql);
        $stmt->execute([ $keyfield, $docPaths['img1'], $docPaths['img2'], $docPaths['img01'], $docPaths['img02'], $docPaths['img001'], $docPaths['img002'], $docPaths['imgd1'], $docPaths['imgd2'], $uploadDir ]);
        
        $this->pdo->commit();
        return ['success' => true, 'confnr' => $confnr];

    } catch (Exception $e) {
            $this->pdo->rollBack();
            return ['success' => false, 'message' => $e->getMessage()];
    }
}

// Dentro de la clase RentalModel en RentalModel.php

/**
 * Busca vehículos que NO tengan reservas que se solapen con un rango de fechas.
 */
public function getAvailableCars(string $startDate, string $endDate)
{
    // Un vehículo está OCUPADO si su reserva se solapa con el rango solicitado.
    // Condición de solapamiento: (InicioReserva < FinSolicitud) Y (FinReserva > InicioSolicitud)
    $sql = "
        SELECT 
            c.id, 
            c.Description, 
            c.License, 
            ci.picloc AS car_image
        FROM 
            cars c
        LEFT JOIN 
            cars_img ci ON c.id = ci.id_rel
        WHERE 
            c.active = 1 
            AND c.id NOT IN (
                SELECT DISTINCT r.vehicle 
                FROM rentals r
                WHERE 
                    r.cancelled = 0 
                    AND r.vehicle IS NOT NULL
                    AND (r.STARTTIME < :end_date AND r.ENDTIME > :start_date)
            )
        GROUP BY c.id
        ORDER BY c.Description ASC
    ";

    try {
        $stmt = $this->pdo->prepare($sql);
        $stmt->execute([
            ':start_date' => $startDate,
            ':end_date' => $endDate
        ]);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    } catch (PDOException $e) {
        error_log("Error en RentalModel::getAvailableCars: " . $e->getMessage());
        return []; // Devolver un array vacío en caso de error
    }
}


// Dentro de la clase RentalModel en RentalModel.php

// En la clase RentalModel, REEMPLAZA el método anterior con este

 /**
     * VERSIÓN FINAL Y CORREGIDA
     * Busca vehículos disponibles aplicando la lógica correcta de negocio.
     *
     * @param array $filters Los filtros del formulario de consulta.
     * @return array La lista de vehículos realmente disponibles.
     */
    public function getAvailableCarsWithFilters(array $filters)
    {
        // Aseguramos que las fechas tengan el formato correcto para MySQL
        $startDateTime = date('Y-m-d H:i:s', strtotime($filters['start_date']));
        $endDateTime = date('Y-m-d H:i:s', strtotime($filters['end_date']));

        // 1. Consulta base que une 'cars' con 'cars_img'
        $baseSql = "
            SELECT 
                c.id, c.Description, c.License AS placa, c.Make AS marca, c.Model AS modelo, c.year AS anio,
                c.trans, c.class AS tipo_vehiculo, c.fueltype AS tipo_combustible,
                ci.capacidad, ci.precio AS precio_dia, ci.picloc AS car_image
            FROM 
                cars c
            LEFT JOIN 
                cars_img ci ON c.id = ci.id_rel
        ";

        // 2. Preparamos las condiciones WHERE
        $whereClauses = [];
        $bindings = [];

        // =================================================================================
        // CORRECCIÓN LÓGICA CLAVE APLICADA
        // =================================================================================

        // CONDICIÓN 1: El vehículo DEBE estar marcado como DISPONIBLE (avb = 0).
        // Esto excluye automáticamente los que están en mantenimiento, vendidos, etc.
        $whereClauses[] = "c.avb = 0";

        // CONDICIÓN 2: El vehículo NO DEBE tener una reserva activa o futura que se cruce con las fechas solicitadas.
        $whereClauses[] = "c.id NOT IN (
            SELECT DISTINCT r.vehicle 
            FROM rentals r
            WHERE 
                r.cancelled = 0 
                AND r.vehicle IS NOT NULL
                AND (r.STARTTIME < :end_date AND r.ENDTIME > :start_date)
        )";
        $bindings[':start_date'] = $startDateTime;
        $bindings[':end_date'] = $endDateTime;
        
        // FILTROS OPCIONALES del formulario
        if (!empty($filters['vehicle_type']) && $filters['vehicle_type'] !== 'Cualquiera') {
            $whereClauses[] = "LOWER(c.class) = LOWER(:vehicle_type)";
            $bindings[':vehicle_type'] = $filters['vehicle_type'];
        }
        if (!empty($filters['transmission']) && $filters['transmission'] !== 'Cualquiera') {
            $whereClauses[] = "LOWER(c.trans) = LOWER(:transmission)";
            $bindings[':transmission'] = $filters['transmission'];
        }
        if (!empty($filters['fuel']) && $filters['fuel'] !== 'Cualquiera') {
            $whereClauses[] = "LOWER(c.fueltype) = LOWER(:fuel)";
            $bindings[':fuel'] = $filters['fuel'];
        }

        // 3. Unimos todo para formar la consulta final
        $finalSql = $baseSql . " WHERE " . implode(" AND ", $whereClauses) . " GROUP BY c.id ORDER BY ci.precio ASC";

        try {
            $stmt = $this->pdo->prepare($finalSql);
            $stmt->execute($bindings);
            return $stmt->fetchAll(PDO::FETCH_ASSOC);
        } catch (PDOException $e) {
            error_log("Error en RentalModel::getAvailableCarsWithFilters: " . $e->getMessage());
            return [];
        }
    }





}