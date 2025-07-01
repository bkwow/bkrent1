<?php
class CustomerModel {
    private $pdo;

    public function __construct() {
        $this->pdo = Database::getConnection();
    }

    /**
     * Busca un cliente por su número de teléfono.
     * Busca en múltiples campos de teléfono.
     */
    public function findByPhone(string $phone) {
        // CORRECCIÓN: Se utilizan placeholders con nombres únicos para cada campo de teléfono.
        $sql = "SELECT * FROM customers 
                WHERE drhphone = :phone1 
                   OR drlphone = :phone2 
                   OR drmphone = :phone3 
                   OR workphone = :phone4 
                LIMIT 1";
        
        $stmt = $this->pdo->prepare($sql);
        
        // CORRECCIÓN: Se asigna el mismo valor de teléfono a cada uno de los placeholders únicos.
        $stmt->execute([
            'phone1' => $phone,
            'phone2' => $phone,
            'phone3' => $phone,
            'phone4' => $phone
        ]);
        
        return $stmt->fetch(PDO::FETCH_ASSOC);
    }
    
    /**
     * Cuenta el número total de alquileres (no cancelados) para un cliente específico.
     *
     * @param int $clientId El ID del cliente (columna 'Client' de la tabla customers).
     * @return int El número de alquileres.
     */
    public function countRentalsByClientId(int $clientId): int {
        // Asumiendo que la columna en `rentals` que referencia a `customers.Client` es `id_cli`.
        $sql = "SELECT COUNT(*) FROM rentals WHERE id_cli = :client_id AND cancelled = 0";
        try {
            $stmt = $this->pdo->prepare($sql);
            $stmt->execute([':client_id' => $clientId]);
            return (int)$stmt->fetchColumn();
        } catch (PDOException $e) {
            error_log("Error en CustomerModel::countRentalsByClientId: " . $e->getMessage());
            return 0; // Devolvemos 0 en caso de error.
        }
    }

    /**
     * Crea un nuevo cliente en la base de datos.
     * Esta es una función de ejemplo que necesitará ser implementada con todos los campos.
     */
    public function create(array $data) {
        // Esta función es un placeholder y debe ser implementada completamente.
        // Ejemplo de cómo podría ser:
        // $sql = "INSERT INTO customers (drfirstname, drlastname, DREMAIL, drhphone) VALUES (?, ?, ?, ?)";
        // $stmt = $this->pdo->prepare($sql);
        // $stmt->execute([$data['drfirstname'], $data['drlastname'], $data['DREMAIL'], $data['drhphone']]);
        // return $this->pdo->lastInsertId();
        
        // Devolvemos un ID falso solo para propósitos de demostración.
        return rand(10000, 20000);
    }

    // Dentro de la clase CustomerModel en CustomerModel.php
public function getCustomerById($id) {
    $sql = "SELECT * FROM customers WHERE Client = :id";
    $stmt = $this->pdo->prepare($sql);
    $stmt->execute([':id' => (int)$id]);
    return $stmt->fetch(PDO::FETCH_ASSOC);
}
}