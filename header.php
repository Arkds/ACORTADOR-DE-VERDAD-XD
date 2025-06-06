<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

require 'db.php';

if (!isset($_SESSION['user_id']) && isset($_COOKIE['user_id'])) {
    $_SESSION['user_id'] = $_COOKIE['user_id'];
    $_SESSION['user_name'] = $_COOKIE['user_name'];
    $_SESSION['user_role'] = $_COOKIE['user_role'];
}

if (!isset($_SESSION['user_id'])) {
    // Limpiar buffer de salida antes de redireccionar
    if (ob_get_length())
        ob_clean();
    header("Location: login.php");
    exit();
}
// No cerrar la etiqueta PHP para evitar espacios después
?>

<!DOCTYPE html>
<html lang="es">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Panel</title>
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css">
    <link rel="stylesheet" href="main.css">

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons/font/bootstrap-icons.css">

    <!-- CSS de DataTables -->
    <link rel="stylesheet" type="text/css" href="https://cdn.datatables.net/1.11.5/css/jquery.dataTables.min.css">

    <!-- jQuery (necesario para DataTables) -->
    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>

    <!-- JS de DataTables -->
    <script type="text/javascript" charset="utf8"
        src="https://cdn.datatables.net/1.11.5/js/jquery.dataTables.min.js"></script>

</head>

<body>

    <nav class="navbar navbar-expand-lg navbar-dark bg-dark">
        <div class="container-fluid">
            <a href="javascript:history.back()" class="btn btn-outline-light me-2">
                <i class="bi bi-arrow-left"></i>
            </a>

            <a class="navbar-brand" href="index.php">
                <i class="bi bi-house-door-fill"></i>
            </a>

            <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navbarNav">
                <span class="navbar-toggler-icon"></span>
            </button>

            <div class="collapse navbar-collapse" id="navbarNav">
                <ul class="navbar-nav me-auto">
                    <li class="nav-item"><a class="nav-link" href="crear_enlace.php">Crear Enlace</a></li>
                    <li class="nav-item"><a class="nav-link" href="mis_enlaces.php">Mis Enlaces</a></li>
                    <li class="nav-item"><a class="nav-link" href="reportes.php">Reportes</a></li>

                    <?php if (isset($_SESSION['user_role']) && $_SESSION['user_role'] === 'admin'): ?>

                        <li class="nav-item"><a class="nav-link" href="usuarios.php">Usuarios</a></li>
                    <?php endif; ?>
                </ul>


                <a href="ayuda.php" class="btn btn-outline-light me-2">
                    <i class="bi bi-question-circle"></i>
                </a>

                <a href="logout.php" class="btn btn-danger">
                    <i class="bi bi-box-arrow-right"></i>
                </a>
            </div>
        </div>
    </nav>