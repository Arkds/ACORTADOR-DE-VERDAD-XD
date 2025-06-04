<?php
session_start();

// Eliminar cookies
setcookie("user_id", "", time() - 3600, "/");
setcookie("user_name", "", time() - 3600, "/");
setcookie("user_role", "", time() - 3600, "/");

// Destruir sesión
session_unset();
session_destroy();

// Redirigir a login
header("Location: login.php");
exit();
?>
