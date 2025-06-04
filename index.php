<?php
require 'header.php';
require 'db.php'; 

?>

<div class="container mt-5">
    <h1 class="text-center">Bienvenido, <?= htmlspecialchars($_SESSION['user_name'] ?? 'Invitado') ?> ðŸ‘‹</h1>
    
    <h2 class="text-center mt-4">Asignaciones de Usuarios, Cuentas y Cursos</h2>
    <div id="cy" style="width: 100%; height: 600px; border: 1px solid #ddd;"></div>
</div>

