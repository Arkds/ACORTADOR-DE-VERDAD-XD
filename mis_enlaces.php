<?php
require 'header.php';
$usuario_id = $_SESSION['user_id'];

// Procesar edición
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['editar_enlace'])) {
    $stmt = $pdo->prepare("UPDATE links SET alias = ?, numero = ?, mensaje = ? WHERE id = ? AND creado_por = ?");
    $stmt->execute([
        $_POST['alias'],
        $_POST['numero'],
        $_POST['mensaje'],
        $_POST['id'],
        $usuario_id
    ]);
    header("Location: mis_enlaces.php");
    exit;
}

// Procesar eliminación
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['eliminar_enlace'])) {
    $stmt = $pdo->prepare("DELETE FROM links WHERE id = ? AND creado_por = ?");
    $stmt->execute([
        $_POST['id'],
        $usuario_id
    ]);
    header("Location: mis_enlaces.php");
    exit;
}

// Obtener enlaces
$stmt = $pdo->prepare("
    SELECT l.*, COUNT(c.id) AS total_clicks
    FROM links l
    LEFT JOIN clicks c ON l.id = c.link_id
    WHERE l.creado_por = ?
    GROUP BY l.id
    ORDER BY l.fecha_creacion DESC
");
$stmt->execute([$usuario_id]);
$enlaces = $stmt->fetchAll(PDO::FETCH_ASSOC);
?>

<div class="container mt-5">
    <h2 class="mb-4 text-center">Mis Enlaces Generados</h2>

    <?php if (empty($enlaces)): ?>
        <div class="alert alert-info">Aún no has creado enlaces.</div>
    <?php else: ?>
        <div class="table-responsive">
            <table class="table table-bordered table-hover align-middle text-center">
                <thead class="table-dark">
                    <tr>
                        <th>Alias</th>
                        <th>Enlace</th>
                        <th>Número</th>
                        <th>Mensaje</th>
                        <th>QR</th>
                        <th>Clics</th>
                        <th>Fecha</th>
                        <th>Acciones</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($enlaces as $e): ?>
                        <tr>
                            <td><strong><?= htmlspecialchars($e['alias']) ?></strong></td>
                            <td>
                                <a href="/<?= urlencode($e['alias']) ?>" target="_blank">
                                    <?= $_SERVER['HTTP_HOST'] . '/' . $e['alias'] ?>
                                </a><br>
                                <button class="btn btn-sm btn-outline-secondary mt-1"
                                    onclick="copiarTexto('<?= $_SERVER['HTTP_HOST'] . '/' . $e['alias'] ?>')">Copiar</button>
                            </td>
                            <td><?= htmlspecialchars($e['numero']) ?></td>
                            <td><?= htmlspecialchars($e['mensaje']) ?></td>
                            <td><img src="https://api.qrserver.com/v1/create-qr-code/?size=100x100&data=https://<?= $_SERVER['HTTP_HOST'] ?>/<?= urlencode($e['alias']) ?>" alt="QR"></td>
                            <td><?= $e['total_clicks'] ?></td>
                            <td><?= date("d/m/Y H:i", strtotime($e['fecha_creacion'])) ?></td>
                            <td>
                                <button class="btn btn-sm btn-info" data-bs-toggle="modal" data-bs-target="#verModal<?= $e['id'] ?>">Ver</button>
                                <button class="btn btn-sm btn-warning" data-bs-toggle="modal" data-bs-target="#editarModal<?= $e['id'] ?>">Editar</button>
                                <button class="btn btn-sm btn-danger" data-bs-toggle="modal" data-bs-target="#eliminarModal<?= $e['id'] ?>">Eliminar</button>
                            </td>
                        </tr>

                        <!-- Modal Ver -->
                        <div class="modal fade" id="verModal<?= $e['id'] ?>" tabindex="-1">
                            <div class="modal-dialog"><div class="modal-content p-3">
                                <div class="modal-header"><h5 class="modal-title">Detalles del Enlace</h5></div>
                                <div class="modal-body">
                                    <p><strong>Alias:</strong> <?= htmlspecialchars($e['alias']) ?></p>
                                    <p><strong>Enlace:</strong> https://<?= $_SERVER['HTTP_HOST'] . '/' . $e['alias'] ?></p>
                                    <p><strong>Número:</strong> <?= htmlspecialchars($e['numero']) ?></p>
                                    <p><strong>Mensaje:</strong> <?= htmlspecialchars($e['mensaje']) ?></p>
                                    <p><strong>Clicks:</strong> <?= $e['total_clicks'] ?></p>
                                </div>
                            </div></div>
                        </div>

                        <!-- Modal Editar -->
                        <div class="modal fade" id="editarModal<?= $e['id'] ?>" tabindex="-1">
                            <div class="modal-dialog"><div class="modal-content p-3">
                                <form method="POST">
                                    <input type="hidden" name="id" value="<?= $e['id'] ?>">
                                    <input type="hidden" name="editar_enlace" value="1">
                                    <div class="modal-header"><h5 class="modal-title">Editar Enlace</h5></div>
                                    <div class="modal-body">
                                        <div class="mb-2">
                                            <label>Alias</label>
                                            <input class="form-control" name="alias" value="<?= htmlspecialchars($e['alias']) ?>">
                                        </div>
                                        <div class="mb-2">
                                            <label>Número</label>
                                            <input class="form-control" name="numero" value="<?= htmlspecialchars($e['numero']) ?>">
                                        </div>
                                        <div class="mb-2">
                                            <label>Mensaje</label>
                                            <textarea class="form-control" name="mensaje"><?= htmlspecialchars($e['mensaje']) ?></textarea>
                                        </div>
                                    </div>
                                    <div class="modal-footer">
                                        <button class="btn btn-primary" type="submit">Guardar cambios</button>
                                    </div>
                                </form>
                            </div></div>
                        </div>

                        <!-- Modal Eliminar -->
                        <div class="modal fade" id="eliminarModal<?= $e['id'] ?>" tabindex="-1">
                            <div class="modal-dialog"><div class="modal-content p-3">
                                <form method="POST">
                                    <input type="hidden" name="id" value="<?= $e['id'] ?>">
                                    <input type="hidden" name="eliminar_enlace" value="1">
                                    <div class="modal-header"><h5 class="modal-title text-danger">Eliminar Enlace</h5></div>
                                    <div class="modal-body">
                                        ¿Estás seguro de eliminar el enlace <strong><?= htmlspecialchars($e['alias']) ?></strong>?
                                    </div>
                                    <div class="modal-footer">
                                        <button type="submit" class="btn btn-danger">Eliminar</button>
                                    </div>
                                </form>
                            </div></div>
                        </div>

                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>
    <?php endif; ?>
</div>

<script>
function copiarTexto(texto) {
    const temp = document.createElement("input");
    temp.value = "https://" + texto;
    document.body.appendChild(temp);
    temp.select();
    document.execCommand("copy");
    document.body.removeChild(temp);
    alert("Enlace copiado al portapapeles");
}
</script>
