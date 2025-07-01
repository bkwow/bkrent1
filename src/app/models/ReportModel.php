<?php
class ReportModel {
    private $pdo;
    public function __construct() { $this->pdo = Database::getConnection(); }

    public function getAllMakes() {
        $stmt = $this->pdo->prepare("SELECT nombre FROM marcas ORDER BY nombre ASC");
        $stmt->execute();
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    public function getModelsByMake($makeName) {
        $sql = "SELECT mo.nombre FROM modelos mo JOIN marcas ma ON mo.id_marca = ma.id WHERE ma.nombre = ? ORDER BY mo.nombre ASC";
        $stmt = $this->pdo->prepare($sql);
        $stmt->execute([$makeName]);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    public function getAllClasses() {
        $stmt = $this->pdo->prepare("SELECT class, descr FROM classes ORDER BY class ASC");
        $stmt->execute();
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    // Un único método para obtener los años de las RENTAS para el gráfico
    public function getDistinctRentalYears() {
        $stmt = $this->pdo->prepare("SELECT DISTINCT YEAR(STARTTIME) as year FROM rentals WHERE cancelled = 0 AND YEAR(STARTTIME) > 1990 ORDER BY year DESC");
        $stmt->execute();
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    public function getFilteredRentals(array $filters) {
        $baseSql = "FROM rentals r LEFT JOIN cars c ON r.vehicle = c.id LEFT JOIN customers cust ON r.Client = cust.Client";
        $selectColumns = "r.confnr, r.STARTTIME, r.ENDTIME, cust.drfirstname, cust.drlastname, c.Make, c.Model, c.year, c.fueltype, r.amount";
        $whereClauses = ["r.cancelled = 0"];
        $bindings = [];

        $reportType = $filters['report_type'] ?? 'entregas';
        $dateColumn = ($reportType === 'retornos') ? 'r.ENDTIME' : 'r.STARTTIME';

        if (!empty($filters['start_date'])) { $whereClauses[] = "DATE($dateColumn) >= ?"; $bindings[] = $filters['start_date']; }
        if (!empty($filters['end_date'])) { $whereClauses[] = "DATE($dateColumn) <= ?"; $bindings[] = $filters['end_date']; }
        if (!empty($filters['make'])) { $whereClauses[] = "c.Make = ?"; $bindings[] = $filters['make']; }
        if (!empty($filters['model'])) { $whereClauses[] = "c.Model = ?"; $bindings[] = $filters['model']; }
        if (!empty($filters['class'])) { $whereClauses[] = "c.class = ?"; $bindings[] = $filters['class']; }
        if (!empty($filters['year'])) { $whereClauses[] = "c.year = ?"; $bindings[] = $filters['year']; }
        if (!empty($filters['fueltype'])) { $whereClauses[] = "c.fueltype = ?"; $bindings[] = $filters['fueltype']; }
        
        $where = " WHERE " . implode(" AND ", $whereClauses);
        $dataSql = "SELECT " . $selectColumns . " " . $baseSql . $where . " ORDER BY $dateColumn DESC";
        $stmt = $this->pdo->prepare($dataSql);
        $stmt->execute($bindings);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    public function getSalesComparison(int $currentYear) {
        $previousYear = $currentYear - 1;
        $sql = "SELECT MONTH(STARTTIME) as sale_month, YEAR(STARTTIME) as sale_year, SUM(amount) as total_sales FROM rentals WHERE YEAR(STARTTIME) IN (?, ?) AND cancelled = 0 GROUP BY sale_year, sale_month ORDER BY sale_year, sale_month;";
        $stmt = $this->pdo->prepare($sql);
        $stmt->execute([$currentYear, $previousYear]);
        $results = $stmt->fetchAll(PDO::FETCH_ASSOC);
        $data = ['labels' => ['Ene', 'Feb', 'Mar', 'Abr', 'May', 'Jun', 'Jul', 'Ago', 'Sep', 'Oct', 'Nov', 'Dic'], 'currentYear' => array_fill(0, 12, 0), 'previousYear' => array_fill(0, 12, 0), 'currentYearLabel' => $currentYear, 'previousYearLabel' => $previousYear];
        foreach ($results as $row) {
            $monthIndex = $row['sale_month'] - 1;
            if ($row['sale_year'] == $currentYear) { $data['currentYear'][$monthIndex] = (float)$row['total_sales']; } 
            else if ($row['sale_year'] == $previousYear) { $data['previousYear'][$monthIndex] = (float)$row['total_sales']; }
        }
        return $data;
    }


   // ==========================================================
    // NUEVO: Método para obtener los vehículos más rentados por modelo
    // ==========================================================
     public function getMostRentedByModel(int $year) {
        $sql = "
            SELECT 
                CONCAT(c.Make, ' ', c.Model) as vehicle_name,
                COUNT(r.confnr) as rental_count,
                SUM(r.amount) as total_revenue
            FROM 
                rentals r
            JOIN 
                cars c ON r.vehicle = c.id
            WHERE 
                YEAR(r.STARTTIME) = ? AND r.cancelled = 0
            GROUP BY 
                c.Make, c.Model
            ORDER BY 
                rental_count DESC; -- Aún se ordenan para ver los más populares primero
        ";
        try {
            $stmt = $this->pdo->prepare($sql);
            $stmt->execute([$year]);
            return $stmt->fetchAll(PDO::FETCH_ASSOC);
        } catch (PDOException $e) {
            error_log("Error en ReportModel::getMostRentedByModel: " . $e->getMessage());
            return [];
        }
    }

    public function getMostRentedByClass(int $year) {
        $sql = "
            SELECT 
                c.class as vehicle_class,
                COUNT(r.confnr) as rental_count
            FROM 
                rentals r
            JOIN 
                cars c ON r.vehicle = c.id
            WHERE 
                YEAR(r.STARTTIME) = ? AND r.cancelled = 0 AND c.class IS NOT NULL AND c.class != ''
            GROUP BY 
                c.class
            ORDER BY 
                rental_count DESC;
        ";
        try {
            $stmt = $this->pdo->prepare($sql);
            $stmt->execute([$year]);
            return $stmt->fetchAll(PDO::FETCH_ASSOC);
        } catch (PDOException $e) {
            error_log("Error en ReportModel::getMostRentedByClass: " . $e->getMessage());
            return [];
        }
    }

    // ==========================================================
    // NUEVO: Método para obtener las rentas agrupadas por departamento/estado
    // ==========================================================
    public function getRentalsByState(int $year) {
        // COALESCE(NULLIF(r.drstate, ''), 'Desconocido')
        // NULLIF convierte las cadenas vacías ('') en NULL.
        // COALESCE toma el primer valor que no sea NULL. Si drstate es NULL o '', usa 'Desconocido'.
        $sql = "
            SELECT 
                COALESCE(NULLIF(r.drstate, ''), 'Desconocido') as state_name,
                COUNT(r.confnr) as rental_count
            FROM 
                rentals r
            WHERE 
                YEAR(r.STARTTIME) = ? AND r.cancelled = 0
            GROUP BY 
                state_name
            ORDER BY 
                rental_count DESC;
        ";
        try {
            $stmt = $this->pdo->prepare($sql);
            $stmt->execute([$year]);
            return $stmt->fetchAll(PDO::FETCH_ASSOC);
        } catch (PDOException $e) {
            error_log("Error en ReportModel::getRentalsByState: " . $e->getMessage());
            return [];
        }
    }

     // ==========================================================
    // NUEVO: Método para obtener las rentas agrupadas por mes y país/departamento
    // ==========================================================
    public function getRentalsByMonthAndState(int $year) {
        $sql = "
            SELECT 
                MONTH(r.STARTTIME) as sale_month,
                COALESCE(NULLIF(r.drcountry, ''), 'Desconocido') as country_name,
                COUNT(r.confnr) as rental_count
            FROM 
                rentals r
            WHERE 
                YEAR(r.STARTTIME) = ? AND r.cancelled = 0
            GROUP BY 
                sale_month, country_name
            ORDER BY 
                sale_month, rental_count DESC;
        ";
        try {
            $stmt = $this->pdo->prepare($sql);
            $stmt->execute([$year]);
            return $stmt->fetchAll(PDO::FETCH_ASSOC);
        } catch (PDOException $e) {
            error_log("Error en ReportModel::getRentalsByMonthAndState: " . $e->getMessage());
            return [];
        }
    }

    // ==========================================================
    // NUEVOS MÉTODOS PARA EL ANÁLISIS GEOGRÁFICO
    //===========================================================

    /**
     * Obtiene el conteo total de rentas agrupadas por país para un año específico.
     * Trata los valores nulos o vacíos como 'Desconocido'.
     *
     * @param int $year El año a consultar.
     * @return array La lista de países y su total de rentas.
     */
    public function getRentalsByCountry(int $year): array
    {
        $sql = "
            SELECT 
                COALESCE(NULLIF(r.drcountry, ''), 'Desconocido') as country, 
                COUNT(r.confnr) as rental_count
            FROM 
                rentals r 
            WHERE 
                YEAR(r.STARTTIME) = ? AND r.cancelled = 0
            GROUP BY 
                country 
            ORDER BY 
                rental_count DESC
        ";
        try {
            $stmt = $this->pdo->prepare($sql);
            $stmt->execute([$year]);
            return $stmt->fetchAll(PDO::FETCH_ASSOC);
        } catch (PDOException $e) {
            error_log("Error en ReportModel::getRentalsByCountry: " . $e->getMessage());
            return [];
        }
    }

    /**
     * Obtiene los datos de detalle para un país específico: rentas por departamento y el top 20 de vehículos.
     *
     * @param int $year El año a consultar.
     * @param string $country El país seleccionado para el detalle.
     * @return array Un array con dos claves: 'by_state' y 'top_vehicles'.
     */
    public function getGeographicDrilldown(int $year, string $country): array
    {
        // Consulta para el detalle por departamento/estado
        $sqlStates = "
            SELECT 
                COALESCE(NULLIF(r.drstate, ''), 'Desconocido') as state, 
                COUNT(r.confnr) as rental_count
            FROM 
                rentals r 
            WHERE 
                YEAR(r.STARTTIME) = ? 
                AND COALESCE(NULLIF(r.drcountry, ''), 'Desconocido') = ? 
                AND r.cancelled = 0
            GROUP BY 
                state 
            ORDER BY 
                rental_count DESC
        ";
        $stmtStates = $this->pdo->prepare($sqlStates);
        $stmtStates->execute([$year, $country]);
        $statesData = $stmtStates->fetchAll(PDO::FETCH_ASSOC);

        // Consulta para el Top 20 de vehículos en ese país
        $sqlVehicles = "
            SELECT 
                CONCAT(c.Make, ' ', c.Model) as vehicle_name, 
                COUNT(r.confnr) as rental_count
            FROM 
                rentals r 
            JOIN 
                cars c ON r.vehicle = c.id
            WHERE 
                YEAR(r.STARTTIME) = ? 
                AND COALESCE(NULLIF(r.drcountry, ''), 'Desconocido') = ? 
                AND r.cancelled = 0
            GROUP BY 
                vehicle_name 
            ORDER BY 
                rental_count DESC 
            LIMIT 20
        ";
        $stmtVehicles = $this->pdo->prepare($sqlVehicles);
        $stmtVehicles->execute([$year, $country]);
        $topVehiclesData = $stmtVehicles->fetchAll(PDO::FETCH_ASSOC);

        return ['by_state' => $statesData, 'top_vehicles' => $topVehiclesData];
    }

    // ==========================================================
    // NUEVO: Método para obtener los datos del reporte de texto
    // ==========================================================
    /**
     * Obtiene los datos para el reporte de texto diario.
     * CORRECCIÓN: La consulta ahora selecciona todos los campos necesarios para el nuevo formato.
     */
    public function getTextReportData() {
        $selectFields = "
            r.STARTTIME, r.ENDTIME, r.drfirstname, r.drlastname, r.drhphone, 
            r.currbal as amount, r.fuellevel, r.picinfo, 
            c.Make, c.Model, c.year, c.License, c.fueltype, c.trans
        ";
        
        $queries = [
            'entregas_hoy' => "SELECT $selectFields FROM rentals r LEFT JOIN cars c ON r.vehicle = c.id WHERE r.cancelled = 0 AND DATE(r.STARTTIME) = CURDATE() ORDER BY r.STARTTIME ASC",
            'entregas_manana' => "SELECT $selectFields FROM rentals r LEFT JOIN cars c ON r.vehicle = c.id WHERE r.cancelled = 0 AND DATE(r.STARTTIME) = DATE_ADD(CURDATE(), INTERVAL 1 DAY) ORDER BY r.STARTTIME ASC",
            'retornos_hoy' => "SELECT $selectFields FROM rentals r LEFT JOIN cars c ON r.vehicle = c.id WHERE r.cancelled = 0 AND DATE(r.ENDTIME) = CURDATE() ORDER BY r.ENDTIME ASC",
            'retornos_manana' => "SELECT $selectFields FROM rentals r LEFT JOIN cars c ON r.vehicle = c.id WHERE r.cancelled = 0 AND DATE(r.ENDTIME) = DATE_ADD(CURDATE(), INTERVAL 1 DAY) ORDER BY r.ENDTIME ASC"
        ];

        $results = [];
        foreach ($queries as $key => $sql) {
            try {
                $stmt = $this->pdo->prepare($sql);
                $stmt->execute();
                $results[$key] = $stmt->fetchAll(PDO::FETCH_ASSOC);
            } catch (PDOException $e) {
                error_log("Error en ReportModel::getTextReportData ($key): " . $e->getMessage());
                $results[$key] = [];
            }
        }
        return $results;
    }

}
?>
