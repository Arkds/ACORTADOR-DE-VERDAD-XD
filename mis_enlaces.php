<?php
require 'header.php';
$usuario_id = $_SESSION['user_id'];

// Procesar nuevo enlace
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['agregar_enlace'])) {
    $stmt = $pdo->prepare("INSERT INTO links (alias, numero, mensaje, creado_por) VALUES (?, ?, ?, ?)");
    $stmt->execute([
        $_POST['alias'],
        $_POST['numero'],
        $_POST['mensaje'],
        $usuario_id
    ]);
    header("Location: mis_enlaces.php");
    exit;
}

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
        <div class="table-responsive-sm">


            <div class="d-flex justify-content-between align-items-center mb-3">

                <button id="loadAllQrBtn" class="btn btn-outline-primary btn-sm mb-3">
                    <i class="bi bi-qr-code-scan"></i> Cargar todos los QR
                </button>
                <button class="btn btn-accent" data-bs-toggle="modal" data-bs-target="#agregarModal">+ Agregar
                    Enlace</button>
            </div>

            <table id="tablaEnlaces" class="table table-striped table-sm table-bordered align-middle text-center compact">


                <thead class="table-dark">
                    <tr>
                        <th>ID</th>
                        <th>Enlace</th>
                        <th>Número</th>
                        <th>QR</th>
                        <th>Clics</th>
                        <th>Fecha</th>
                        <th>Acciones</th>
                    </tr>
                </thead>

                <tbody>
                    <?php foreach ($enlaces as $e): ?>
                        <tr>
                            <td><?= $e['id'] ?></td>
                            <td>
                                <a href="/<?= urlencode($e['alias']) ?>"
                                    target="_blank"><?= $_SERVER['HTTP_HOST'] . '/' . $e['alias'] ?></a><br>
                                <button class="btn btn-sm btn-outline-secondary mt-1"
                                    onclick="copiarTexto('https://<?= $_SERVER['HTTP_HOST'] ?>/<?= $e['alias'] ?>')">Copiar</button>
                            </td>
                            <td><?= htmlspecialchars($e['numero']) ?></td>
                            <td>
                                <button class="btn btn-sm btn-light border show-qr-btn"
                                    data-alias="<?= htmlspecialchars($e['alias']) ?>">
                                    <i class="bi bi-qr-code"></i>
                                </button>
                                <div class="qr-container mt-1" style="display: none;"></div>
                            </td>
                            <td><?= $e['total_clicks'] ?></td>
                            <td><?= date("d/m/Y H:i", strtotime($e['fecha_creacion'])) ?></td>
                            <td>
                                <button class="btn btn-sm btn-info" data-bs-toggle="modal"
                                    data-bs-target="#verModal<?= $e['id'] ?>">Ver</button>
                                <button class="btn btn-sm btn-warning" data-bs-toggle="modal"
                                    data-bs-target="#editarModal<?= $e['id'] ?>">Editar</button>
                                <button class="btn btn-sm btn-danger" data-bs-toggle="modal"
                                    data-bs-target="#eliminarModal<?= $e['id'] ?>">Eliminar</button>
                            </td>
                        </tr>

                        <div class="modal fade" id="verModal<?= $e['id'] ?>" tabindex="-1">
                            <div class="modal-dialog">

                                <div class="modal-content rounded shadow-sm">
                                    <div class="modal-header border-0 pt-3 pb-2">
                                        <h5 class="modal-title text-success">
                                            <i class="bi bi-check-circle-fill me-2"></i>Su enlace generado con éxito
                                        </h5>
                                        <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                                    </div>

                                    <div class="modal-body">
                                        <!-- Nav Tabs estilo moderno -->
                                        <ul class="nav nav-tabs nav-justified border-0 mb-3" id="tab<?= $e['id'] ?>"
                                            role="tablist">
                                            <li class="nav-item" role="presentation">
                                                <button class="nav-link" data-bs-toggle="tab"
                                                    data-bs-target="#tab1<?= $e['id'] ?>" type="button">Compartir</button>
                                            </li>
                                            <li class="nav-item" role="presentation">
                                                <button class="nav-link" data-bs-toggle="tab"
                                                    data-bs-target="#tab2<?= $e['id'] ?>" type="button">Detalles</button>
                                            </li>
                                            <li class="nav-item" role="presentation">
                                                <button class="nav-link active" data-bs-toggle="tab"
                                                    data-bs-target="#tab3<?= $e['id'] ?>" type="button">Código QR</button>
                                            </li>
                                        </ul>

                                        <!-- Contenido de pestañas -->
                                        <div class="tab-content text-center">
                                            <!-- Compartir -->
                                            <div class="tab-pane fade" id="tab1<?= $e['id'] ?>">
                                                <div class="mb-3">
                                                    <input class="form-control text-center" readonly
                                                        value="https://<?= $_SERVER['HTTP_HOST'] . '/' . $e['alias'] ?>">
                                                </div>
                                                <button class="btn btn-outline-primary btn-sm"
                                                    onclick="copiarTexto('https://<?= $_SERVER['HTTP_HOST'] ?>/<?= $e['alias'] ?>')">
                                                    Copiar enlace
                                                </button>

                                            </div>

                                            <!-- Detalles -->
                                            <div class="tab-pane fade" id="tab2<?= $e['id'] ?>">
                                                <ul class="list-group text-start small">
                                                    <li class="list-group-item"><strong>ID:</strong> <?= $e['id'] ?></li>
                                                    <li class="list-group-item"><strong>Alias:</strong>
                                                        <?= htmlspecialchars($e['alias']) ?></li>
                                                    <li class="list-group-item"><strong>Número:</strong>
                                                        <?= htmlspecialchars($e['numero']) ?></li>
                                                    <li class="list-group-item"><strong>Mensaje:</strong>
                                                        <?= htmlspecialchars($e['mensaje']) ?></li>
                                                    <li class="list-group-item"><strong>Clicks:</strong>
                                                        <?= $e['total_clicks'] ?></li>
                                                    <li class="list-group-item"><strong>Fecha:</strong>
                                                        <?= date("d/m/Y H:i", strtotime($e['fecha_creacion'])) ?></li>
                                                </ul>
                                            </div>

                                            <!-- Código QR -->
                                            <!-- Código QR -->
                                            <div class="tab-pane fade show active" id="tab3<?= $e['id'] ?>">
                                                <p class="mb-2">Descarga la siguiente imagen del Código QR.</p>
                                                <img src="https://api.qrserver.com/v1/create-qr-code/?size=200x200&data=https://<?= $_SERVER['HTTP_HOST'] ?>/<?= urlencode($e['alias']) ?>"
                                                    class="mb-2 d-block mx-auto" alt="QR">
                                                <button class="btn btn-success btn-sm"
                                                    onclick="descargarQR('<?= $e['alias'] ?>')">
                                                    Descargar imagen
                                                </button>
                                            </div>

                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>



                        <!-- Modal Editar -->
                        <div class="modal fade" id="editarModal<?= $e['id'] ?>" tabindex="-1">
                            <div class="modal-dialog modal-dialog-centered">
                                <div class="modal-content p-4">
                                    <form method="POST">
                                        <input type="hidden" name="id" value="<?= $e['id'] ?>">
                                        <input type="hidden" name="editar_enlace" value="1">
                                        <div class="modal-header border-0 pb-1">
                                            <h5 class="modal-title">Editar Enlace</h5>
                                        </div>
                                        <div class="modal-body">
                                            <div class="mb-3">
                                                <label class="form-label">Alias</label>
                                                <div class="input-group">
                                                    <span class="input-group-text">https://<?= $_SERVER['HTTP_HOST'] ?>/</span>
                                                    <input type="text" name="alias" class="form-control"
                                                        value="<?= htmlspecialchars($e['alias']) ?>" required>
                                                </div>
                                            </div>

                                            <div class="mb-3">
                                                <label class="form-label">Número</label>
                                                <div class="input-group">
                                                    <span class="input-group-text">+51</span>
                                                    <input type="text" name="numero" class="form-control"
                                                        value="<?= htmlspecialchars($e['numero']) ?>" required>
                                                </div>
                                            </div>

                                            <div class="mb-3">
                                                <label class="form-label">Mensaje</label>
                                                <textarea name="mensaje" class="form-control" rows="3"
                                                    required><?= htmlspecialchars($e['mensaje']) ?></textarea>
                                            </div>
                                        </div>
                                        <div class="modal-footer border-0 pt-1">
                                            <button type="submit" class="btn btn-success w-100">Guardar cambios</button>
                                        </div>
                                    </form>
                                </div>
                            </div>
                        </div>


                        <!-- Modal Eliminar -->
                        <div class="modal fade" id="eliminarModal<?= $e['id'] ?>" tabindex="-1">
                            <div class="modal-dialog">
                                <div class="modal-content p-3">
                                    <form method="POST">
                                        <input type="hidden" name="id" value="<?= $e['id'] ?>">
                                        <input type="hidden" name="eliminar_enlace" value="1">
                                        <div class="modal-header">
                                            <h5 class="modal-title text-danger">Eliminar Enlace</h5>
                                        </div>
                                        <div class="modal-body">
                                            ¿Estás seguro de eliminar el enlace
                                            <strong><?= htmlspecialchars($e['alias']) ?></strong>?
                                        </div>
                                        <div class="modal-footer">
                                            <button type="submit" class="btn btn-danger">Eliminar</button>
                                        </div>
                                    </form>
                                </div>
                            </div>
                        </div>

                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>
    <?php endif; ?>
</div>
<!-- Modal Agregar -->
<div class="modal fade" id="agregarModal" tabindex="-1">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content p-4">
            <form method="POST">
                <input type="hidden" name="agregar_enlace" value="1">
                <div class="modal-header border-0 pb-1">
                    <h5 class="modal-title">Generar enlace personalizado</h5>
                </div>
                <div class="modal-body">
                    <div class="mb-3">
                        <label class="form-label">Escribe tu número de WhatsApp</label>
                        <div class="input-group">
                            <span class="input-group-text">+51</span>
                            <input type="text" name="numero" class="form-control" placeholder="912345678" required>
                        </div>
                        <small class="text-muted">Recuerda confirmar el código de tu país</small>
                    </div>

                    <div class="mb-3">
                        <label class="form-label">Mensaje</label>
                        <textarea name="mensaje" class="form-control" rows="3"
                            placeholder="Ejemplo: Hola, quiero más información sobre el producto" required></textarea>
                    </div>

                    <div class="mb-3">
                        <label class="form-label">Generador de enlace con alias personalizado</label>
                        <div class="input-group">
                            <span class="input-group-text">https://<?= $_SERVER['HTTP_HOST'] ?>/</span>
                            <input type="text" name="alias" class="form-control" placeholder="MI_NEGOCIO" required>
                        </div>
                        <small class="text-muted">Puedes usar un alias legible para humanos.</small>
                    </div>
                </div>
                <div class="modal-footer border-0 pt-1">
                    <button type="submit" class="btn btn-success w-100">Generar mi enlace</button>
                </div>
            </form>
        </div>
    </div>
</div>


<script>
    function copiarTexto(url) {
        if (navigator.clipboard) {
            navigator.clipboard.writeText(url).catch(err => console.error("Error al copiar:", err));
        } else {
            // Fallback para navegadores antiguos
            const temp = document.createElement("textarea");
            temp.value = url;
            document.body.appendChild(temp);
            temp.select();
            document.execCommand("copy");
            document.body.removeChild(temp);
        }
    }
</script>

<script>
    function descargarQR(alias) {
        const url = `https://api.qrserver.com/v1/create-qr-code/?size=300x300&data=https://${location.host}/${alias}`;
        fetch(url)
            .then(resp => resp.blob())
            .then(blob => {
                const a = document.createElement('a');
                a.href = window.URL.createObjectURL(blob);
                a.download = `qr_${alias}.png`;
                a.click();
            });
    }
</script>

<script>
    document.querySelectorAll('.show-qr-btn').forEach(btn => {
        btn.addEventListener('click', function () {
            const alias = this.dataset.alias;
            const container = this.nextElementSibling;

            if (container.style.display === 'none') {
                container.innerHTML = `<img src="https://api.qrserver.com/v1/create-qr-code/?size=100x100&data=https://${location.host}/${alias}" alt="QR de ${alias}">`;
                container.style.display = 'block';
            } else {
                container.style.display = 'none';
            }
        });
    });

    document.getElementById('loadAllQrBtn').addEventListener('click', () => {
        document.querySelectorAll('.show-qr-btn').forEach(btn => btn.click());
    });
</script>

<script>
    $(document).ready(function () {
        $('#tablaEnlaces').DataTable({
            responsive: true,
            pageLength: 10,
            lengthMenu: [5, 10, 25, 50],
            dom: '<"row mb-2"<"col-sm-6"l><"col-sm-6 text-end"f>>' + // top
                'rt' +
                '<"row mt-2"<"col-sm-6 text-start"p><"col-sm-6 text-end"i>>', // bottom
            language: {
                url: "//cdn.datatables.net/plug-ins/1.13.6/i18n/es-ES.json"
            },
            columnDefs: [
                { orderable: false, targets: -1 }
            ]
        });

    });
</script>

<!-- jQuery (requerido por DataTables) -->
<script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>

<!-- DataTables JS -->
<script src="https://cdn.datatables.net/1.13.6/js/jquery.dataTables.min.js"></script>
<script src="https://cdn.datatables.net/1.13.6/js/dataTables.bootstrap5.min.js"></script>