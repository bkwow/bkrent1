<?php

class MenuModel {
    private $pdo;

    public function __construct() {
        $this->pdo = Database::getConnection();
    }

    /**
     * Obtiene todas las categorías de menú activas, ordenadas.
     */
    public function getMenuCategories() {
        // Aquí podrías añadir un WHERE para filtrar por rol en el futuro
        $sql = "SELECT * FROM tbl_system_menu_cat WHERE activo = 1 ORDER BY orden ASC";
        $stmt = $this->pdo->prepare($sql);
        $stmt->execute();
        return $stmt->fetchAll();
    }

    /**
     * Obtiene todos los ítems de menú (las tarjetas) para todas las categorías.
     * Usamos una sola consulta para ser más eficientes.
     */
    public function getMenuItems() {
        // Aquí también podrías filtrar por rol
        $sql = "SELECT * FROM tbl_system_menu WHERE activom = 1 ORDER BY orden ASC";
        $stmt = $this->pdo->prepare($sql);
        $stmt->execute();
        return $stmt->fetchAll();
    }
}