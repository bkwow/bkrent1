<?php

class ConfigModel {
    private $pdo;

    public function __construct() {
        $this->pdo = Database::getConnection();
    }

    /**
     * Obtiene todas las configuraciones del sistema.
     * Suponemos que solo hay una fila en la tabla de configuración.
     */
    public function getSettings() {
        $stmt = $this->pdo->prepare("SELECT * FROM tbl_system_configuracion WHERE id = 1");
        $stmt->execute();
        return $stmt->fetch();
    }
}