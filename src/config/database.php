<?php

/**
 * Configuración de la Base de Datos
 * 
 * Define las constantes para la conexión a la base de datos.
 * Es una buena práctica usar constantes para evitar que estos valores
 * se modifiquen accidentalmente en otras partes del código.
 * 
 * Cambia estos valores para que coincidan con tu entorno de desarrollo.
 */

define('DB_HOST', 'localhost');       // El servidor donde está la base de datos (generalmente 'localhost')
define('DB_NAME', 'sistema_leslyrent_2024');   // El nombre de tu base de datos (tendrás que crearla)
define('DB_USER', 'root');            // El usuario de la base de datos (en XAMPP/WAMP suele ser 'root')
define('DB_PASS', '');                // La contraseña del usuario (en XAMPP/WAMP suele ser vacía)
define('DB_CHARSET', 'utf8mb4');      // El conjunto de caracteres para la conexión

?>