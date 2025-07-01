<?php

/**
 * Clase Database para gestionar la conexión a la base de datos usando PDO.
 * Utiliza el patrón Singleton para asegurar una única instancia de conexión.
 */
class Database {
    
    // Propiedades para la conexión
    private static $instance = null;
    private $pdo;
    private $host = DB_HOST;
    private $dbname = DB_NAME;
    private $user = DB_USER;
    private $pass = DB_PASS;
    private $charset = DB_CHARSET;

    /**
     * El constructor es privado para prevenir la creación de nuevas instancias
     * con el operador 'new'.
     */
    private function __construct() {
        // Data Source Name (DSN) para la conexión PDO
        $dsn = "mysql:host={$this->host};dbname={$this->dbname};charset={$this->charset}";
        
        // Opciones para la conexión PDO
        $options = [
            PDO::ATTR_ERRMODE            => PDO::ERRMODE_EXCEPTION, // Lanza excepciones en caso de error
            PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,       // Devuelve resultados como arrays asociativos
            PDO::ATTR_EMULATE_PREPARES   => false,                  // Usa preparaciones de sentencias nativas
        ];

        try {
            // Intentar crear la instancia de PDO
            $this->pdo = new PDO($dsn, $this->user, $this->pass, $options);
        } catch (PDOException $e) {
            // Si la conexión falla, detener la aplicación y mostrar un error
            throw new PDOException("Error de conexión: " . $e->getMessage());
        }
    }

    /**
     * Método estático público para obtener la instancia de la base de datos.
     * Esta es la única forma de acceder a la clase.
     *
     * @return PDO La instancia de la conexión PDO.
     */
    public static function getConnection() {
        // Si no existe una instancia, crear una.
        if (self::$instance === null) {
            self::$instance = new self();
        }
        
        // Devolver el objeto PDO de la instancia.
        return self::$instance->pdo;
    }

    /**
     * Prevenir la clonación de la instancia (parte del patrón Singleton).
     */
    private function __clone() {}

    /**
     * Prevenir la deserialización de la instancia (parte del patrón Singleton).
     */
    public function __wakeup() {}
}
?>