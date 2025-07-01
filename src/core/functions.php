<?php

/**
 * functions.php
 *
 * Este archivo contiene funciones de ayuda globales que se pueden usar
 * en cualquier parte de la aplicación.
 */

/**
 * Escapa el HTML para prevenir ataques XSS (Cross-Site Scripting).
 *
 * Es una buena práctica pasar todas las variables que se van a imprimir
 * en el HTML a través de esta función.
 *
 * @param string|null $string La cadena a escapar.
 * @return string La cadena escapada y segura para imprimir.
 */
function e(?string $string): string {
    return htmlspecialchars($string ?? '', ENT_QUOTES, 'UTF-8');
}

/**
 * Redirige a otra página de la aplicación.
 *
 * @param string $url La URL a la que se va a redirigir (ej. 'index.php?page=dashboard').
 * @return void
 */
function redirect(string $url): void {
    header("Location: {$url}");
    exit();
}

/**
 * Verifica si el usuario actual ha iniciado sesión.
 *
 * @return bool True si el usuario ha iniciado sesión, false en caso contrario.
 */
function isLoggedIn(): bool {
    return isset($_SESSION['user_id']);
}

/**
 * Obtiene un valor de la sesión de forma segura.
 *
 * @param string $key La clave del valor en la sesión.
 * @param mixed $default El valor a devolver si la clave no existe.
 * @return mixed El valor de la sesión o el valor por defecto.
 */
function session_get(string $key, $default = null) {
    return $_SESSION[$key] ?? $default;
}

/**
 * Establece un mensaje flash en la sesión.
 *
 * Los mensajes flash solo se muestran una vez y luego se eliminan.
 * Son útiles para mostrar notificaciones de éxito o error después de una redirección.
 *
 * @param string $type El tipo de mensaje (ej. 'success', 'error', 'info').
 * @param string $message El mensaje a mostrar.
 * @return void
 */
function set_flash_message(string $type, string $message): void {
    $_SESSION['flash_messages'][] = [
        'type' => $type,
        'message' => $message
    ];
}

/**
 * Muestra todos los mensajes flash y los elimina de la sesión.
 *
 * Se debe llamar en el layout (ej. header.php) para mostrar las notificaciones.
 *
 * @return void
 */
function display_flash_messages(): void {
    if (isset($_SESSION['flash_messages'])) {
        foreach ($_SESSION['flash_messages'] as $flash) {
            // Genera el HTML de la alerta de Bootstrap
            echo "<div class='alert alert-danger alert-dismissible fade show' role='alert'>"; // Será rojo para 'error'
            echo e($flash['message']);
            echo '<button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>';
            echo "</div>";
        }
        // Limpia el mensaje para que no se muestre de nuevo
        unset($_SESSION['flash_messages']);
    }
}
/**
 * Genera una URL base para la aplicación.
 * Útil para construir enlaces y rutas a archivos.
 * 
 * @param string $path La ruta a añadir a la URL base (opcional).
 * @return string La URL completa.
 */
function base_url(string $path = ''): string {
    $protocol = isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] === 'on' ? "https" : "http";
    $host = $_SERVER['HTTP_HOST'];
    $script = dirname($_SERVER['SCRIPT_NAME']);
    // Asegurarse de que el script no sea solo '/'
    $script = ($script === DIRECTORY_SEPARATOR) ? '' : $script;

    return rtrim("{$protocol}://{$host}{$script}", '/') . '/' . ltrim($path, '/');
}

/**
 * Verifica si el usuario actual tiene un rol específico o superior.
 * (Aún no implementado, pero es un buen lugar para ponerlo)
 *
 * @param string $requiredRole El rol mínimo requerido.
 * @return bool
 */
function has_role(string $requiredRole): bool {
    // Lógica para comparar roles...
    // Por ejemplo: super_admin > admin > operator > client
    // Lo implementaremos cuando tengamos el sistema de permisos.
    return true; // Placeholder
}


/**
 * Limpia y formatea un número de teléfono.
 * Elimina todos los caracteres no numéricos excepto el '+' inicial.
 *
 * @param string $phone El número de teléfono a limpiar.
 * @return string El número limpio.
 */
function sanitize_phone_number(string $phone): string {
    // Quitar todo lo que no sea un dígito o un '+'
    $cleaned = preg_replace('/[^\d+]/', '', $phone);
    
    // Si no empieza con '+', asumimos un prefijo (ej. de tu país) y lo añadimos.
    // Este prefijo debes ajustarlo a tu necesidad.
    // if (substr($cleaned, 0, 1) !== '+') {
    //     $cleaned = '+1' . $cleaned; // Ejemplo para prefijo de USA/Canada
    // }
    
    return $cleaned;
}
?>