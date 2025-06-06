<?php
require 'header.php';

if (!isset($_SESSION['user_role']) || $_SESSION['user_role'] !== 'admin') {

    http_response_code(403);
    echo "Acceso denegado.";
    exit;
}

// Crear usuario
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['crear_usuario'])) {
    $nombre = $_POST['nombre'];
    $username = $_POST['username'];
    $password = password_hash($_POST['password'], PASSWORD_BCRYPT);
    $rol = $_POST['rol'];

    $stmt = $pdo->prepare("INSERT INTO users (nombre, username, password, rol) VALUES (?, ?, ?, ?)");
    $stmt->execute([$nombre, $username, $password, $rol]);
    header("Location: usuarios.php");
    exit;
}

// Eliminar usuario
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['eliminar_usuario'])) {
    $id = $_POST['id'];
    $stmt = $pdo->prepare("DELETE FROM users WHERE id = ?");
    $stmt->execute([$id]);
    header("Location: usuarios.php");
    exit;
}
// Editar usuario
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['editar_usuario'])) {
    $id = $_POST['id'];
    $nombre = $_POST['nombre'];
    $username = $_POST['username'];
    $rol = $_POST['rol'];

    // Solo actualizar contraseña si se proporciona
    if (!empty($_POST['password'])) {
        $password = password_hash($_POST['password'], PASSWORD_BCRYPT);
        $stmt = $pdo->prepare("UPDATE users SET nombre = ?, username = ?, password = ?, rol = ? WHERE id = ?");
        $stmt->execute([$nombre, $username, $password, $rol, $id]);
    } else {
        $stmt = $pdo->prepare("UPDATE users SET nombre = ?, username = ?, rol = ? WHERE id = ?");
        $stmt->execute([$nombre, $username, $rol, $id]);
    }

    header("Location: usuarios.php");
    exit;
}


// Obtener usuarios
$stmt = $pdo->query("SELECT id, nombre, username, rol, creado_en FROM users ORDER BY id ASC");
$usuarios = $stmt->fetchAll(PDO::FETCH_ASSOC);
?>

<div class="container mt-4">
    <h2 class="mb-3">Gestión de Usuarios</h2>

    <button class="btn btn-success mb-3" data-bs-toggle="modal" data-bs-target="#crearModal">+ Nuevo Usuario</button>

    <table class="table table-bordered table-sm text-center">
        <thead class="table-dark">
            <tr>
                <th>ID</th>
                <th>Nombre</th>
                <th>Usuario</th>
                <th>Rol</th>
                <th>Creado en</th>
                <th>Acciones</th>
            </tr>
        </thead>
        <tbody>
            <?php foreach ($usuarios as $u): ?>
                <tr>
                    <td><?= $u['id'] ?></td>
                    <td><?= htmlspecialchars($u['nombre']) ?></td>
                    <td><?= htmlspecialchars($u['username']) ?></td>
                    <td><?= $u['rol'] ?></td>
                    <td><?= $u['creado_en'] ?></td>
                    <td>
                        <?php if ($u['id'] != $_SESSION['user_id']): ?>
                            <!-- Botón Editar -->
                            <button class="btn btn-sm btn-primary" data-bs-toggle="modal"
                                data-bs-target="#editarModal<?= $u['id'] ?>">Editar</button>

                            <!-- Botón Eliminar -->
                            <form method="POST" onsubmit="return confirm('¿Eliminar usuario?')" style="display:inline;">
                                <input type="hidden" name="id" value="<?= $u['id'] ?>">
                                <button class="btn btn-sm btn-danger" name="eliminar_usuario">Eliminar</button>
                            </form>
                        <?php else: ?>
                            <span class="text-muted">Tú</span>
                        <?php endif; ?>
                    </td>

                </tr>
            <?php endforeach ?>
        </tbody>
    </table>
</div>

<!-- Modal Crear Usuario -->
<div class="modal fade" id="crearModal" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content p-3">
            <form method="POST">
                <input type="hidden" name="crear_usuario" value="1">
                <div class="modal-header">
                    <h5 class="modal-title">Crear nuevo usuario</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body">
                    <input name="nombre" class="form-control mb-2" placeholder="Nombre completo" required>
                    <input name="username" class="form-control mb-2" placeholder="Usuario" required>
                    <input name="password" type="password" class="form-control mb-2" placeholder="Contraseña" required>
                    <select name="rol" class="form-select mb-2" required>
                        <option value="usuario">Usuario</option>
                        <option value="admin">Administrador</option>
                    </select>
                </div>
                <div class="modal-footer">
                    <button class="btn btn-primary w-100" type="submit">Crear</button>
                </div>
            </form>
        </div>
    </div>
</div>
<?php foreach ($usuarios as $u): ?>
    <div class="modal fade" id="editarModal<?= $u['id'] ?>" tabindex="-1">
        <div class="modal-dialog">
            <div class="modal-content p-3">
                <form method="POST">
                    <input type="hidden" name="editar_usuario" value="1">
                    <input type="hidden" name="id" value="<?= $u['id'] ?>">
                    <div class="modal-header">
                        <h5 class="modal-title">Editar usuario</h5>
                        <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                    </div>
                    <div class="modal-body">
                        <input name="nombre" class="form-control mb-2" value="<?= htmlspecialchars($u['nombre']) ?>"
                            required>
                        <input name="username" class="form-control mb-2" value="<?= htmlspecialchars($u['username']) ?>"
                            required>
                        <input name="password" type="password" class="form-control mb-2"
                            placeholder="Nueva contraseña (opcional)">
                        <select name="rol" class="form-select mb-2" required>
                            <option value="usuario" <?= $u['rol'] === 'usuario' ? 'selected' : '' ?>>Usuario</option>
                            <option value="admin" <?= $u['rol'] === 'admin' ? 'selected' : '' ?>>Administrador</option>
                        </select>
                    </div>
                    <div class="modal-footer">
                        <button class="btn btn-primary w-100" type="submit">Guardar cambios</button>
                    </div>
                </form>
            </div>
        </div>
    </div>
<?php endforeach; ?>