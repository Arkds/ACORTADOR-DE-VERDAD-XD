<?php
$host = 'localhost';
$dbname = 'acortador';
$user = 'root';
$pass = '';

try {
    $pdo = new PDO("mysql:host=$host;dbname=$dbname;charset=utf8mb4", $user, $pass);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

    $pdo->exec("SET NAMES 'utf8mb4' COLLATE 'utf8mb4_unicode_ci'");

} catch (PDOException $e) {
    die("Error de conexión: " . $e->getMessage());
}

if (!defined('ENCRYPTION_KEY')) {
    define('ENCRYPTION_KEY', 'tu_clave_secreta_segura'); 
    define('ENCRYPTION_IV', '1234567890123456'); 
    define('ENCRYPTION_METHOD', 'AES-256-CBC');
}
?>