<?php
require 'db.php';

if (!isset($_GET['alias'])) {
    http_response_code(400);
    echo "Alias no especificado.";
    exit;
}

$alias = $_GET['alias'];

// Buscar el enlace por alias
$stmt = $pdo->prepare("SELECT id, numero, mensaje FROM links WHERE alias = ?");
$stmt->execute([$alias]);
$link = $stmt->fetch(PDO::FETCH_ASSOC);

if ($link) {
    $link_id = $link['id'];
    $numero = $link['numero'];
    $mensaje = $link['mensaje'] ?? '';

    // Captura de datos
    $ip = $_SERVER['REMOTE_ADDR'];
    $user_agent = $_SERVER['HTTP_USER_AGENT'] ?? 'Desconocido';
    $referer = $_SERVER['HTTP_REFERER'] ?? 'Directo';

    // Registrar clic con referer
    $stmt = $pdo->prepare("INSERT INTO clicks (link_id, ip, user_agent, referer) VALUES (?, ?, ?, ?)");
    $stmt->execute([$link_id, $ip, $user_agent, $referer]);

    // Redireccionar
    $urlFinal = "https://wa.me/$numero";
    if ($mensaje) {
        $urlFinal .= "?text=" . urlencode($mensaje);
    }

    header("Location: $urlFinal");
    exit;
} else {
    http_response_code(404);
    echo "Enlace no encontrado.";
}

