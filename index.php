<?php
require 'header.php';
require 'db.php';

// KPIs
$user_id = $_SESSION['user_id'];

// Enlaces totales
$stmt = $pdo->prepare("SELECT COUNT(*) FROM links WHERE creado_por = ?");
$stmt->execute([$user_id]);
$total_enlaces = $stmt->fetchColumn();

// Clics de hoy
$stmt = $pdo->prepare("
    SELECT COUNT(*) FROM clicks 
    JOIN links ON clicks.link_id = links.id 
    WHERE links.creado_por = ? AND DATE(clicks.fecha_click) = CURDATE()
");
$stmt->execute([$user_id]);
$clicks_hoy = $stmt->fetchColumn();

// Clics de ayer
$stmt = $pdo->prepare("
    SELECT COUNT(*) FROM clicks 
    JOIN links ON clicks.link_id = links.id 
    WHERE links.creado_por = ? AND DATE(clicks.fecha_click) = DATE_SUB(CURDATE(), INTERVAL 1 DAY)
");
$stmt->execute([$user_id]);
$clicks_ayer = $stmt->fetchColumn();

// Clics últimos 7 días
$stmt = $pdo->prepare("
    SELECT COUNT(*) FROM clicks 
    JOIN links ON clicks.link_id = links.id 
    WHERE links.creado_por = ? AND clicks.fecha_click >= DATE_SUB(CURDATE(), INTERVAL 7 DAY)
");
$stmt->execute([$user_id]);
$clicks_7dias = $stmt->fetchColumn();

// Top 5 enlaces
$stmt = $pdo->prepare("
    SELECT l.alias, COUNT(c.id) AS total_clicks, l.fecha_creacion
    FROM links l
    LEFT JOIN clicks c ON l.id = c.link_id
    WHERE l.creado_por = ?
    GROUP BY l.id
    ORDER BY total_clicks DESC
    LIMIT 5
");
$stmt->execute([$user_id]);
$top_links = $stmt->fetchAll(PDO::FETCH_ASSOC);
// Datos de los últimos 7 días
$clicks_por_dia = [];
for ($i = 6; $i >= 0; $i--) {
    $fecha = date('Y-m-d', strtotime("-$i days"));
    $stmt = $pdo->prepare("
        SELECT COUNT(*) FROM clicks 
        JOIN links ON clicks.link_id = links.id 
        WHERE links.creado_por = ? AND DATE(clicks.fecha_click) = ?
    ");
    $stmt->execute([$user_id, $fecha]);
    $clicks_por_dia[$fecha] = $stmt->fetchColumn();
}

?>

<div class="container mt-4">
    <div class="row g-3 mb-4">
        <div class="col-md-3">
            <div class="card shadow-sm border-0 text-center">
                <div class="card-body">
                    <h1><i class="bi bi-link-45deg"></i></h1>
                    <p class="mb-1 text-muted">Enlaces totales</p>
                    <h4><?= $total_enlaces ?></h4>
                </div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="card shadow-sm border-0 text-center">
                <div class="card-body">
                    <h1><i class="bi bi-graph-up-arrow text-danger"></i></h1>
                    <p class="mb-1 text-muted">Visitas de hoy</p>
                    <h4><?= $clicks_hoy ?></h4>
                </div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="card shadow-sm border-0 text-center">
                <div class="card-body">
                    <h1><i class="bi bi-graph-up text-warning"></i></h1>
                    <p class="mb-1 text-muted">Visitas de ayer</p>
                    <h4><?= $clicks_ayer ?></h4>
                </div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="card shadow-sm border-0 text-center">
                <div class="card-body">
                    <h1><i class="bi bi-bar-chart-line text-success"></i></h1>
                    <p class="mb-1 text-muted">Visitas 7 días</p>
                    <h4><?= $clicks_7dias ?></h4>
                </div>
            </div>
        </div>
    </div>

    <div class="row g-3">
        <!-- Top 5 enlaces -->
        <div class="col-md-4">
            <div class="card shadow-sm border-0">
                <div class="card-header bg-white fw-bold">Top 5 enlaces</div>
                <ul class="list-group list-group-flush">
                    <?php if (count($top_links) > 0): ?>
                        <?php foreach ($top_links as $i => $row): ?>
                            <li class="list-group-item d-flex justify-content-between align-items-center">
                                <div>
                                    <span class="badge bg-dark me-2"><?= $i + 1 ?></span>
                                    <?= htmlspecialchars($row['alias']) ?>
                                    <div class="text-muted small">
                                        <?= strtoupper(time_elapsed_string($row['fecha_creacion'])) ?>
                                    </div>
                                </div>
                                <span class="fw-bold"><?= $row['total_clicks'] ?> clicks</span>
                            </li>
                        <?php endforeach; ?>
                    <?php else: ?>
                        <li class="list-group-item text-muted">No hay enlaces aún.</li>
                    <?php endif; ?>
                </ul>
            </div>
        </div>

        <!-- Placeholder de gráfica futura -->
        <div class="col-md-8">
            <div class="card shadow-sm border-0 p-4">
                <h5 class="text-center">Clics en los últimos 7 días</h5>
                <canvas id="clicksChart" height="200"></canvas>
            </div>
        </div>

    </div>
</div>

<?php
function time_elapsed_string($datetime, $full = false)
{
    $now = new DateTime;
    $ago = new DateTime($datetime);
    $diff = $now->diff($ago);

    $diff->w = floor($diff->d / 7);
    $diff->d -= $diff->w * 7;

    $string = [
        'y' => 'año',
        'm' => 'mes',
        'w' => 'semana',
        'd' => 'día',
        'h' => 'hora',
        'i' => 'minuto',
        's' => 'segundo',
    ];
    foreach ($string as $k => &$v) {
        if ($diff->$k) {
            $v = $diff->$k . ' ' . $v . ($diff->$k > 1 ? 's' : '');
        } else {
            unset($string[$k]);
        }
    }

    if (!$full)
        $string = array_slice($string, 0, 1);
    return $string ? 'hace ' . implode(', ', $string) : 'justo ahora';
}
?>

<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
<script>
    const ctx = document.getElementById('clicksChart').getContext('2d');
    const chart = new Chart(ctx, {
        type: 'bar',
        data: {
            labels: <?= json_encode(array_keys($clicks_por_dia)) ?>,
            datasets: [{
                label: 'Clics',
                data: <?= json_encode(array_values($clicks_por_dia)) ?>,
                borderWidth: 1,
                backgroundColor: 'rgba(75, 192, 192, 0.6)',
                borderColor: 'rgba(75, 192, 192, 1)',
            }]
        },
        options: {
            scales: {
                y: { beginAtZero: true }
            }
        }
    });
</script>