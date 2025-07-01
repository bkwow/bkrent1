<?php
class PermissionModel {
    private $pdo;

    public function __construct() {
        $this->pdo = Database::getConnection();
    }

    public function createPermission(array $data) {
        $sql = "INSERT INTO tbl_control_permisos (id_rel_rental, id_cli, destino, placa, keyfield) VALUES (?, ?, ?, ?, ?)";
        $stmt = $this->pdo->prepare($sql);
        // Aquí vincularías los datos del permiso
        return $stmt->execute([
            $data['id_rel_rental'], 
            $data['id_cli'],
            $data['destino'],
            $data['placa'],
            $data['keyfield']
        ]);
    }
}