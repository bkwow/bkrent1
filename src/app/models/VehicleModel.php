<?php
// src/app/models/VehicleModel.php - CORREGIDO PARA USAR 'avb'

class VehicleModel {
    private $pdo;

    public function __construct() {
        $this->pdo = Database::getConnection();
    }

    /**
     * Función profesional para manejar la subida y redimensionamiento de imágenes.
     */
    private function handleImageUpload($fileInput, $carId) {
        if (isset($fileInput['picloc_file']) && $fileInput['picloc_file']['error'] === UPLOAD_ERR_OK) {
            
            $originalDir = 'public/vehiculos_flota/original/';
            $thumbDir = 'public/vehiculos_flota/thumb/';
            if (!is_dir($originalDir)) { mkdir($originalDir, 0775, true); }
            if (!is_dir($thumbDir)) { mkdir($thumbDir, 0775, true); }

            $tmpName = $fileInput['picloc_file']['tmp_name'];
            $fileExtension = strtolower(pathinfo($fileInput['picloc_file']['name'], PATHINFO_EXTENSION));
            $newFileName = $carId . '_' . time() . '.' . $fileExtension;
            
            $originalPath = $originalDir . $newFileName;
            $thumbPath = $thumbDir . $newFileName;

            // Mover el archivo original
            if (!move_uploaded_file($tmpName, $originalPath)) {
                return null; // Falló al mover el archivo
            }

            // Crear thumbnail
            list($width, $height) = getimagesize($originalPath);
            $thumbWidth = 240;
            $thumbHeight = 180;
            $thumb = imagecreatetruecolor($thumbWidth, $thumbHeight);

            $source = null;
            if ($fileExtension == 'jpg' || $fileExtension == 'jpeg') {
                $source = imagecreatefromjpeg($originalPath);
            } elseif ($fileExtension == 'png') {
                $source = imagecreatefrompng($originalPath);
                // Conservar transparencia para PNG
                imagealphablending($thumb, false);
                imagesavealpha($thumb, true);
            } else {
                // Si es otro formato, simplemente copiamos el original al thumb
                copy($originalPath, $thumbPath);
                return $newFileName;
            }

            if ($source) {
                imagecopyresampled($thumb, $source, 0, 0, 0, 0, $thumbWidth, $thumbHeight, $width, $height);
                
                if ($fileExtension == 'jpg' || $fileExtension == 'jpeg') {
                    imagejpeg($thumb, $thumbPath, 90); // 90% de calidad para un buen balance
                } elseif ($fileExtension == 'png') {
                    imagepng($thumb, $thumbPath, 7); // Nivel de compresión 7 para PNG
                }
                imagedestroy($thumb);
                imagedestroy($source);
                return $newFileName;
            }
        }
        return null;
    }

    public function getVehiclesForDataTable(array $request) {
        $baseSql = "FROM cars c LEFT JOIN cars_img ci ON c.id = ci.id_rel";
        $selectColumns = "c.id, c.License, c.Make, c.Model, c.year, c.avb, c.class, c.trans, c.fueltype, ci.precio, ci.picloc";
        $bindings = [];
        $where = " WHERE c.deleted_at = 0";

        // ==========================================================
        // CORRECCIÓN: Búsqueda ahora incluye el estado (avb)
        // ==========================================================
        if (!empty($request['search']['value'])) {
            $searchValue = '%' . $request['search']['value'] . '%';
            $rawSearchValue = strtolower($request['search']['value']);

            // Mapeo de texto a los códigos de estado de la columna 'avb'
            $statusMap = [
                'disponible' => 0,
                'mantenimiento' => 1,
                'accidente' => 2,
                'vendido' => 3,
                'deposito' => 4,
                'depósito' => 4
            ];

            $searchClauses = [
                "c.License LIKE ?", "c.Make LIKE ?", "c.Model LIKE ?", "c.year LIKE ?",
                "c.class LIKE ?", "c.trans LIKE ?", "c.fueltype LIKE ?"
            ];
            
            // Añadimos el valor para cada campo de texto
            for ($i = 0; $i < count($searchClauses); $i++) {
                $bindings[] = $searchValue;
            }

            // Si el término de búsqueda coincide con un estado, lo añadimos a la consulta
            if (array_key_exists($rawSearchValue, $statusMap)) {
                $searchClauses[] = "c.avb = ?";
                $bindings[] = $statusMap[$rawSearchValue];
            }
            
            $where .= " AND (" . implode(" OR ", $searchClauses) . ")";
        }

        $countSql = "SELECT COUNT(c.id) " . $baseSql . $where;
        $totalRecordsStmt = $this->pdo->prepare($countSql);
        $totalRecordsStmt->execute($bindings);
        $totalRecords = $totalRecordsStmt->fetchColumn();

        $dataSql = "SELECT " . $selectColumns . " " . $baseSql . $where . " ORDER BY c.id DESC LIMIT ?, ?";
        array_push($bindings, (int)$request['start'], (int)$request['length']);
        $stmt = $this->pdo->prepare($dataSql);
        
        foreach ($bindings as $key => $value) {
            $stmt->bindValue($key + 1, $value, is_int($value) ? PDO::PARAM_INT : PDO::PARAM_STR);
        }
        
        $stmt->execute();
        $results = $stmt->fetchAll(PDO::FETCH_ASSOC);

        return ["draw" => intval($request['draw']), "recordsTotal" => intval($totalRecords), "recordsFiltered" => intval($totalRecords), "data" => $results];
    }

    public function getVehicleById(int $id) {
        $stmt = $this->pdo->prepare("SELECT c.*, ci.precio, ci.capacidad, ci.picloc FROM cars c LEFT JOIN cars_img ci ON c.id = ci.id_rel WHERE c.id = ?");
        $stmt->execute([$id]);
        return $stmt->fetch(PDO::FETCH_ASSOC);
    }

    public function createVehicle(array $data, array $files) {
        $this->pdo->beginTransaction();
        try {
            $sqlCar = "INSERT INTO cars (License, Make, Model, year, avb, class, trans, fueltype, Description, vin, color, engine) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)";
            $stmtCar = $this->pdo->prepare($sqlCar);
            $stmtCar->execute([ $data['license'], $data['make'], $data['model'], $data['year'], $data['avb'], $data['class'], $data['trans'], $data['fueltype'], $data['description'], $data['vin'], $data['color'], $data['engine'] ]);
            $carId = $this->pdo->lastInsertId();
            
            $newImageName = $this->handleImageUpload($files, $carId);

            $sqlImg = "INSERT INTO cars_img (id_rel, precio, capacidad, picloc) VALUES (?, ?, ?, ?)";
            $stmtImg = $this->pdo->prepare($sqlImg);
            $stmtImg->execute([$carId, $data['precio'], $data['capacidad'], $newImageName]);

            $this->pdo->commit();
            return $carId;
        } catch (Exception $e) { $this->pdo->rollBack(); throw $e; }
    }

    public function updateVehicle(int $id, array $data, array $files) {
        $this->pdo->beginTransaction();
        try {
            $sqlCar = "UPDATE cars SET License=?, Make=?, Model=?, year=?, avb=?, class=?, trans=?, fueltype=?, Description=?, vin=?, color=?, engine=? WHERE id=?";
            $stmtCar = $this->pdo->prepare($sqlCar);
            $stmtCar->execute([ $data['license'], $data['make'], $data['model'], $data['year'], $data['avb'], $data['class'], $data['trans'], $data['fueltype'], $data['description'], $data['vin'], $data['color'], $data['engine'], $id ]);
            
            $newImageName = $this->handleImageUpload($files, $id);
            $piclocToSave = $newImageName ?? $data['existing_picloc'];

            $stmtCheck = $this->pdo->prepare("SELECT id FROM cars_img WHERE id_rel = ?");
            $stmtCheck->execute([$id]);
            if ($stmtCheck->fetch()) {
                 $sqlImg = "UPDATE cars_img SET precio=?, capacidad=?, picloc=? WHERE id_rel=?";
            } else {
                 $sqlImg = "INSERT INTO cars_img (precio, capacidad, picloc, id_rel) VALUES (?, ?, ?, ?)";
            }
            $stmtImg = $this->pdo->prepare($sqlImg);
            $stmtImg->execute([$data['precio'], $data['capacidad'], $piclocToSave, $id]);

            $this->pdo->commit();
            return true;
        } catch (Exception $e) { $this->pdo->rollBack(); throw $e; }
    }

    public function deleteVehicle(int $id) {
        $stmt = $this->pdo->prepare("UPDATE cars SET deleted_at = 1 WHERE id = ?");
        return $stmt->execute([$id]);
    }

    public function updateVehicleStatus(int $id, int $status) {
        // CORRECCIÓN: Se actualiza la columna 'avb'
        $stmt = $this->pdo->prepare("UPDATE cars SET avb = ? WHERE id = ?");
        return $stmt->execute([$status, $id]);
    }


    // ==========================================================
    // NUEVO: Método para obtener el historial de rentas de un vehículo
    // ==========================================================
    public function getRentalHistory(int $vehicleId) {
        $sql = "SELECT 
                    r.confnr,
                    r.drfirstname,
                    r.drlastname,
                    r.drhphone,
                    r.STARTTIME,
                    r.ENDTIME,
                    r.rentdays,
                    r.invoicetotal
                FROM 
                    rentals r
                WHERE 
                    r.vehicle = ? AND r.cancelled = 0
                ORDER BY 
                    r.STARTTIME DESC";
        
        try {
            $stmt = $this->pdo->prepare($sql);
            $stmt->execute([$vehicleId]);
            return $stmt->fetchAll(PDO::FETCH_ASSOC);
        } catch (PDOException $e) {
            error_log("Error en VehicleModel::getRentalHistory: " . $e->getMessage());
            return [];
        }
    }
}
?>