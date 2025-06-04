<?php
require 'header.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $alias = trim($_POST['alias']);
    $numero = trim($_POST['numero']);
    $mensaje = trim($_POST['mensaje']);
    $creado_por = $_SESSION['user_id'];

    if ($alias && $numero) {
        $stmt = $pdo->prepare("SELECT COUNT(*) FROM links WHERE alias = ?");
        $stmt->execute([$alias]);
        if ($stmt->fetchColumn() > 0) {
            $error = "El alias '$alias' ya está en uso.";
        } else {
            $stmt = $pdo->prepare("INSERT INTO links (alias, numero, mensaje, creado_por) VALUES (?, ?, ?, ?)");
            $stmt->execute([$alias, $numero, $mensaje, $creado_por]);
            $success = "Enlace creado con éxito. <a href='/$alias' target='_blank'>Ver enlace</a>";
        }
    } else {
        $error = "El alias y el número son obligatorios.";
    }
}
?>

<div class="container my-5" style="max-width: 600px;">
    <h2 class="mb-4 text-center">Generador de enlace de WhatsApp con mensaje personalizado</h2>

    <?php if (isset($success)): ?>
        <div class="alert alert-success">
            <?= $success ?><br>
            <strong>Tu enlace:</strong><br>
            <div class="input-group mt-2">
                <input type="text" id="generatedLink" class="form-control" readonly
                    value="https://<?= $_SERVER['HTTP_HOST'] ?>/<?= htmlspecialchars($alias) ?>">
                <button class="btn btn-outline-secondary" type="button" onclick="copiarEnlace()">Copiar</button>
            </div>
            <div class="mt-3 text-center">
                <img src="https://api.qrserver.com/v1/create-qr-code/?size=150x150&data=https://<?= $_SERVER['HTTP_HOST'] ?>/<?= urlencode($alias) ?>"
                    alt="QR del enlace">
            </div>
        </div>
    <?php endif; ?>


    <?php if (isset($error)): ?>
        <div class="alert alert-danger"><?= $error ?></div>
    <?php endif; ?>

    <form method="POST" class="p-4 bg-light rounded shadow-sm">

        <div class="mb-3">
            <label class="form-label">Escribe tu número de WhatsApp</label>
            <div class="input-group">
                <span class="input-group-text">+51</span>
                <input type="text" class="form-control" name="numero" required placeholder="912345678">
            </div>
            <small class="text-muted">Recuerda confirmar el código de tu país</small>
        </div>

        <div class="mb-3">
            <label class="form-label">Mensaje</label>
            <textarea class="form-control" name="mensaje" rows="3"
                placeholder="Ejemplo: Hola, quiero más información sobre el producto"></textarea>
        </div>

        <div class="mb-3">
            <label class="form-label">Generador de enlace con alias personalizado</label>
            <div class="input-group">
                <span class="input-group-text">https://tusitio.com/</span>
                <input type="text" class="form-control" name="alias" placeholder="MI_NEGOCIO" required>
            </div>
            <small class="text-muted">Puedes usar un alias legible para humanos.</small>
        </div>

        <button type="submit" class="btn btn-success w-100">Generar mi enlace</button>
    </form>
</div>

<script>
    function copiarEnlace() {
        const input = document.getElementById("generatedLink");
        input.select();
        input.setSelectionRange(0, 99999);
        document.execCommand("copy");
        alert("Enlace copiado: " + input.value);
    }
</script>