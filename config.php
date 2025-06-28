<?php
// Configuración general del sitio
define('SITE_NAME', 'Portal Web Interactivo');
define('SITE_VERSION', '1.0.0');
define('SITE_AUTHOR', 'Tu Nombre');

// Configuración de la base de datos
define('DB_HOST', 'localhost');
define('DB_NAME', 'portal_web');
define('DB_USER', 'root');
define('DB_PASS', '');

// Configuración de sesiones
define('SESSION_LIFETIME', 3600); // 1 hora
define('SESSION_NAME', 'portal_session');

// Configuración de archivos
define('UPLOAD_MAX_SIZE', 5 * 1024 * 1024); // 5MB
define('ALLOWED_EXTENSIONS', ['jpg', 'jpeg', 'png', 'gif', 'pdf', 'doc', 'docx']);

// Configuración de seguridad
define('CSRF_TOKEN_LENGTH', 32);
define('PASSWORD_MIN_LENGTH', 8);

// Configuración de email (si necesitas enviar emails)
define('SMTP_HOST', 'smtp.gmail.com');
define('SMTP_PORT', 587);
define('SMTP_USERNAME', 'tu-email@gmail.com');
define('SMTP_PASSWORD', 'tu-password');

// Configuración de APIs externas (ejemplos)
define('WEATHER_API_KEY', 'tu-api-key-del-clima');
define('GOOGLE_MAPS_API_KEY', 'tu-api-key-de-google-maps');

// Configuración de logs
define('LOG_ERRORS', true);
define('LOG_FILE', 'logs/error.log');

// Configuración de cache
define('CACHE_ENABLED', true);
define('CACHE_LIFETIME', 3600); // 1 hora

// Configuración de desarrollo/producción
define('ENVIRONMENT', 'development'); // 'development' o 'production'
define('DEBUG_MODE', ENVIRONMENT === 'development');

// Configuración de URLs
define('BASE_URL', 'http://localhost/portal-web/');
define('ASSETS_URL', BASE_URL . 'assets/');
define('API_URL', BASE_URL . 'api/');

// Configuración de juegos
define('HANGMAN_MAX_ATTEMPTS', 6);
define('QUIZ_TIME_LIMIT', 30); // segundos por pregunta
define('MEMORY_BOARD_SIZE', 16); // número de cartas

// Configuración del chatbot
define('CHATBOT_MAX_MESSAGES', 100);
define('CHATBOT_RESPONSE_DELAY', 1000); // milisegundos

// Configuración de Real Madrid (datos que podrían venir de una API)
define('REAL_MADRID_FOUNDED', 1902);
define('REAL_MADRID_STADIUM', 'Santiago Bernabéu');
define('REAL_MADRID_CAPACITY', 81044);

// Funciones de utilidad
function getConfig($key, $default = null) {
    return defined($key) ? constant($key) : $default;
}

function isProduction() {
    return ENVIRONMENT === 'production';
}

function isDevelopment() {
    return ENVIRONMENT === 'development';
}

function getBaseUrl() {
    return BASE_URL;
}

function getAssetsUrl() {
    return ASSETS_URL;
}

// Configuración de zona horaria
date_default_timezone_set('America/Mexico_City');

// Configuración de errores según el entorno
if (isDevelopment()) {
    error_reporting(E_ALL);
    ini_set('display_errors', 1);
} else {
    error_reporting(0);
    ini_set('display_errors', 0);
    ini_set('log_errors', 1);
    ini_set('error_log', LOG_FILE);
}

// Iniciar sesión si no está iniciada
if (session_status() === PHP_SESSION_NONE) {
    session_name(SESSION_NAME);
    session_start();
}

// Función para generar tokens CSRF
function generateCSRFToken() {
    if (!isset($_SESSION['csrf_token'])) {
        $_SESSION['csrf_token'] = bin2hex(random_bytes(CSRF_TOKEN_LENGTH));
    }
    return $_SESSION['csrf_token'];
}

// Función para validar tokens CSRF
function validateCSRFToken($token) {
    return isset($_SESSION['csrf_token']) && hash_equals($_SESSION['csrf_token'], $token);
}

// Función para limpiar datos de entrada
function sanitizeInput($data) {
    $data = trim($data);
    $data = stripslashes($data);
    $data = htmlspecialchars($data, ENT_QUOTES, 'UTF-8');
    return $data;
}

// Función para validar email
function validateEmail($email) {
    return filter_var($email, FILTER_VALIDATE_EMAIL) !== false;
}

// Función para validar URL
function validateURL($url) {
    return filter_var($url, FILTER_VALIDATE_URL) !== false;
}

// Función para generar ID único
function generateUniqueId($prefix = '') {
    return $prefix . uniqid() . '_' . mt_rand(1000, 9999);
}

// Función para formatear fechas
function formatDate($date, $format = 'Y-m-d H:i:s') {
    if ($date instanceof DateTime) {
        return $date->format($format);
    }
    return date($format, strtotime($date));
}

// Función para logging
function logMessage($message, $level = 'INFO') {
    if (LOG_ERRORS) {
        $timestamp = date('Y-m-d H:i:s');
        $logEntry = "[$timestamp] [$level] $message" . PHP_EOL;
        
        // Crear directorio de logs si no existe
        $logDir = dirname(LOG_FILE);
        if (!is_dir($logDir)) {
            mkdir($logDir, 0755, true);
        }
        
        file_put_contents(LOG_FILE, $logEntry, FILE_APPEND | LOCK_EX);
    }
}

// Función para respuestas JSON
function jsonResponse($data, $statusCode = 200) {
    http_response_code($statusCode);
    header('Content-Type: application/json');
    echo json_encode($data);
    exit;
}

// Función para manejar errores
function handleError($message, $statusCode = 500) {
    logMessage("Error: $message", 'ERROR');
    jsonResponse(['error' => $message], $statusCode);
}

// Autoload simple para clases
spl_autoload_register(function ($className) {
    $classFile = __DIR__ . '/classes/' . $className . '.php';
    if (file_exists($classFile)) {
        require_once $classFile;
    }
});
?>