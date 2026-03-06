<?php
/**
 * ProjektAdmin - Konfiguration
 * BBK Modul 307 - Interaktive Website mit Formularen
 */

// Fehlerbehandlung
error_reporting(E_ALL);
ini_set('display_errors', 0);
ini_set('log_errors', 1);

// Konstanten
define('DB_HOST', 'localhost');
define('DB_USER', 'root');
define('DB_PASS', '');
define('DB_NAME', 'projektadmin');
define('APP_NAME', 'ProjektAdmin');
define('APP_URL', 'http://localhost/projektadmin');

// Session Sicherheit
session_set_cookie_params([
    'lifetime' => 86400,
    'path' => '/',
    'httponly' => true,
    'samesite' => 'Strict'
]);

session_start();

// Datenbankverbindung
try {
    $pdo = new PDO(
        'mysql:host=' . DB_HOST . ';dbname=' . DB_NAME . ';charset=utf8mb4',
        DB_USER,
        DB_PASS,
        [
            PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
            PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
            PDO::ATTR_EMULATE_PREPARES => false
        ]
    );
} catch (PDOException $e) {
    die('Datenbankfehler: ' . $e->getMessage());
}

/**
 * HILFSFUNKTIONEN
 */

// Input Sanitierung
function sanitize($input) {
    return htmlspecialchars(trim($input), ENT_QUOTES, 'UTF-8');
}

// Email validieren
function validateEmail($email) {
    return filter_var($email, FILTER_VALIDATE_EMAIL) !== false;
}

// Telefon validieren
function validatePhone($phone) {
    return preg_match('/^[\d\s\-\+\(\)]{7,20}$/', $phone);
}

// Datum validieren
function validateDate($date) {
    return preg_match('/^\d{4}-\d{2}-\d{2}$/', $date) && strtotime($date) !== false;
}

// Passwort Hash
function hashPassword($password) {
    return password_hash($password, PASSWORD_BCRYPT, ['cost' => 10]);
}

// Passwort prüfen
function verifyPassword($password, $hash) {
    return password_verify($password, $hash);
}

// Login Status
function isLoggedIn() {
    return isset($_SESSION['user_id']) && !empty($_SESSION['user_id']);
}

// Berechtigung prüfen
function hasPermission($permission) {
    global $pdo;
    
    if (!isLoggedIn()) {
        return false;
    }
    
    try {
        $stmt = $pdo->prepare('
            SELECT p.permission FROM permissions p
            JOIN users u ON p.role = u.role
            WHERE u.id = ? AND p.permission = ?
        ');
        $stmt->execute([$_SESSION['user_id'], $permission]);
        return $stmt->rowCount() > 0;
    } catch (Exception $e) {
        return false;
    }
}

// Umleitung
function redirect($location) {
    header('Location: ' . $location);
    exit;
}

// Log Action
function logAction($action, $details = '') {
    global $pdo;
    if (!isLoggedIn()) return;
    
    try {
        $stmt = $pdo->prepare('
            INSERT INTO activity_log (user_id, action, details, ip_address) 
            VALUES (?, ?, ?, ?)
        ');
        $stmt->execute([
            $_SESSION['user_id'],
            $action,
            $details,
            $_SERVER['REMOTE_ADDR']
        ]);
    } catch (Exception $e) {
        // Ignorieren
    }
}

// Benutzer Info
function getCurrentUser() {
    global $pdo;
    
    if (!isLoggedIn()) {
        return null;
    }
    
    $stmt = $pdo->prepare('SELECT * FROM users WHERE id = ?');
    $stmt->execute([$_SESSION['user_id']]);
    return $stmt->fetch();
}
?>