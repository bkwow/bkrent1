<?php
// src/app/models/CallLogModel.php
// Versión actualizada: gestiona fuentes en call_inquiry_sources

class CallLogModel {
    private $pdo;

    public function __construct() {
        $this->pdo = Database::getConnection();
    }

    public function logCall(string $phone, int $userId) {
        try {
            $sql = "INSERT INTO call_logs (phone_number, user_id) VALUES (?, ?)";
            $stmt = $this->pdo->prepare($sql);
            $stmt->execute([$phone, $userId]);
        } catch (\PDOException $e) {
            error_log("Error en CallLogModel::logCall: " . $e->getMessage());
        }
    }

    public function countCallsToday(string $phone): int {
        try {
            $sql = "SELECT COUNT(*) FROM call_logs WHERE phone_number = ? AND DATE(call_time) = CURDATE()";
            $stmt = $this->pdo->prepare($sql);
            $stmt->execute([$phone]);
            return (int) $stmt->fetchColumn();
        } catch (\PDOException $e) {
            error_log("Error en CallLogModel::countCallsToday: " . $e->getMessage());
            return 0;
        }
    }

    /**
     * Inserta o actualiza una consulta y sus fuentes asociadas
     * @param array $data
     * @return array ['success'=>bool, 'id'=>int|string]
     */
    public function saveInquiryDetails(array $data) {
        $inquiryId = $data['inquiry_id'] ?? null;
        $isUpdate  = !empty($inquiryId);

        // Sanitizar teléfono
        $cleanPhone = isset($data['phone'])
            ? sanitize_phone_number($data['phone'])
            : null;

        if ($isUpdate) {
            $sql = "UPDATE call_inquiries SET
                        phone_number = :phone,
                        requested_start_date = :start_date,
                        requested_end_date   = :end_date,
                        vehicle_type         = :vehicle_type,
                        transmission         = :transmission,
                        fuel                 = :fuel,
                        notes                = :notes,
                        user_id              = :user_id
                    WHERE id = :inquiry_id";
        } else {
            $sql = "INSERT INTO call_inquiries
                        (customer_id, phone_number, user_id, requested_start_date,
                         requested_end_date, vehicle_type, transmission, fuel, notes)
                    VALUES
                        (:customer_id, :phone, :user_id, :start_date,
                         :end_date, :vehicle_type, :transmission, :fuel, :notes)";
        }

        try {
            $stmt = $this->pdo->prepare($sql);
            $bindings = [
                ':phone'        => $cleanPhone,
                ':start_date'   => $data['start_date'] ?? null,
                ':end_date'     => $data['end_date'] ?? null,
                ':vehicle_type' => ($data['vehicle_type'] ?? '') !== 'Cualquiera'
                                     ? $data['vehicle_type'] : null,
                ':transmission' => ($data['transmission'] ?? '') !== 'Cualquiera'
                                     ? $data['transmission'] : null,
                ':fuel'         => ($data['fuel'] ?? '') !== 'Cualquiera'
                                     ? $data['fuel'] : null,
                ':notes'        => $data['observacion'] ?? '',
                ':user_id'      => $_SESSION['user_id'] ?? null
            ];
            if ($isUpdate) {
                $bindings[':inquiry_id'] = $inquiryId;
            } else {
                $bindings[':customer_id'] = $data['customer_id'] ?? null;
            }

            $stmt->execute($bindings);
            $id = $isUpdate ? $inquiryId : $this->pdo->lastInsertId();

            // Actualizar fuentes: borrar y volver a insertar
            $this->pdo
                 ->prepare("DELETE FROM call_inquiry_sources WHERE inquiry_id = ?")
                 ->execute([$id]);

            if (!empty($data['sources']) && is_array($data['sources'])) {
                $stmtSrc = $this->pdo->prepare(
                    "INSERT INTO call_inquiry_sources (inquiry_id, source)
                     VALUES (:inquiry_id, :source)"
                );
                foreach ($data['sources'] as $src) {
                    $stmtSrc->execute([
                        ':inquiry_id' => $id,
                        ':source'     => $src
                    ]);
                }
            }

            return ['success' => true, 'id' => $id];
        } catch (\PDOException $e) {
            error_log("Error en CallLogModel::saveInquiryDetails: " . $e->getMessage());
            return ['success' => false, 'message' => 'Error al guardar en la base de datos.'];
        }
    }

    /**
     * Obtiene las últimas 5 consultas y sus fuentes para un teléfono
     * @param string $phone
     * @return array
     */
    public function getRecentInquiriesByPhone(string $phone) {
        try {
            $sql = "SELECT
                        ci.id,
                        ci.customer_id,
                        ci.phone_number,
                        ci.requested_start_date,
                        ci.requested_end_date,
                        ci.vehicle_type,
                        ci.transmission,
                        ci.fuel,
                        ci.notes,
                        ci.user_id,
                        ci.inquiry_date,
                        GROUP_CONCAT(cis.source) AS sources
                    FROM call_inquiries ci
                    LEFT JOIN call_inquiry_sources cis
                      ON cis.inquiry_id = ci.id
                    WHERE ci.phone_number = ?
                    GROUP BY ci.id
                    ORDER BY ci.inquiry_date DESC
                    LIMIT 5";
            $stmt = $this->pdo->prepare($sql);
            $stmt->execute([$phone]);
            $rows = $stmt->fetchAll(PDO::FETCH_ASSOC);

            // Convertir lista de fuentes en array
            foreach ($rows as &$row) {
                $row['sources'] = isset($row['sources'])
                    ? explode(',', $row['sources'])
                    : [];
            }
            return $rows;
        } catch (\PDOException $e) {
            error_log("Error en CallLogModel::getRecentInquiriesByPhone: " . $e->getMessage());
            return [];
        }
    }
}
