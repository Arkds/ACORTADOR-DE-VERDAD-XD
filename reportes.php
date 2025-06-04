<?php
require 'header.php';

// Obtener enlaces del usuario logueado
$stmt = $pdo->prepare("SELECT id, alias FROM links WHERE creado_por = ?");
$stmt->execute([$_SESSION['user_id']]);
$enlaces = $stmt->fetchAll(PDO::FETCH_ASSOC);

// Valores por defecto
$hoy = date('Y-m-d');
$default_start = $_GET['start'] ?? $hoy;
$default_end = $_GET['end'] ?? $hoy;
$selected_link = $_GET['link'] ?? '';

// 🔽 Aquí se debe mover el bloque de lógica de clics
$clicks_por_dia = [];
$devices = [];
$platforms = [];
$browsers = [];

$device_data = $platform_data = $browser_data = [];

if ($default_start && $default_end && ($selected_link || $selected_link === 'all')) {

    $start_datetime = $default_start . " 00:00:00";
    $end_datetime = $default_end . " 23:59:59";

    if ($selected_link === 'all') {
        $stmt = $pdo->prepare("
        SELECT c.fecha_click, c.user_agent, l.alias
        FROM clicks c
        JOIN links l ON c.link_id = l.id
        WHERE l.creado_por = ?
        AND c.fecha_click BETWEEN ? AND ?
    ");
        $stmt->execute([$_SESSION['user_id'], $start_datetime, $end_datetime]);
    } else {
        $stmt = $pdo->prepare("
        SELECT fecha_click, user_agent
        FROM clicks
        WHERE link_id = ?
        AND fecha_click BETWEEN ? AND ?
    ");
        $stmt->execute([$selected_link, $start_datetime, $end_datetime]);
    }
    $clicks = $stmt->fetchAll(PDO::FETCH_ASSOC);

    $devices_por_alias = [];
    $platforms_por_alias = [];
    $browsers_por_alias = [];

    foreach ($clicks as $click) {
        $fecha = date("Y-m-d", strtotime($click['fecha_click']));
        $clicks_por_dia[$fecha] = ($clicks_por_dia[$fecha] ?? 0) + 1;

        $agent = strtolower($click['user_agent']);
        $alias = $click['alias'] ?? 'este'; // Solo existe si se eligió "all"

        $device = (strpos($agent, 'mobile') !== false || strpos($agent, 'android') !== false) ? 'Móvil' : 'Escritorio';
        $platform = (strpos($agent, 'windows') !== false) ? 'Windows' :
            ((strpos($agent, 'android') !== false) ? 'Android' :
                ((strpos($agent, 'mac') !== false) ? 'Mac' :
                    ((strpos($agent, 'linux') !== false) ? 'Linux' : 'Otro')));
        $browser = (strpos($agent, 'chrome') !== false) ? 'Chrome' :
            ((strpos($agent, 'firefox') !== false) ? 'Firefox' :
                ((strpos($agent, 'safari') !== false) ? 'Safari' :
                    ((strpos($agent, 'edge') !== false) ? 'Edge' : 'Otro')));

        $devices_por_alias[$alias][] = $device;
        $platforms_por_alias[$alias][] = $platform;
        $browsers_por_alias[$alias][] = $browser;
    }

    // Función para contar
    function contar_grupo($array)
    {
        return array_count_values($array);
    }

    // Agrupar
    $device_data = [];
    $platform_data = [];
    $browser_data = [];

    foreach ($devices_por_alias as $alias => $arr) {
        $device_data[$alias] = contar_grupo($arr);
    }
    foreach ($platforms_por_alias as $alias => $arr) {
        $platform_data[$alias] = contar_grupo($arr);
    }
    foreach ($browsers_por_alias as $alias => $arr) {
        $browser_data[$alias] = contar_grupo($arr);
    }




   
}

?>

<div class="container mt-4">
    <div class="row">
        <!-- Sidebar de filtros -->
        <div class="col-md-3">
            <div class="card p-3 shadow-sm">
                <form method="GET">


                    <div class="mb-3">
                        <label class="form-label">Choose Date</label>
                        <input type="date" name="start" class="form-control" value="<?= $default_start ?>">
                        <input type="date" name="end" class="form-control mt-2" value="<?= $default_end ?>">
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Link</label>
                        <select name="link" class="form-select" required>
                            <option value="all" <?= ($selected_link === 'all') ? 'selected' : '' ?>>Todos los enlaces
                            </option>

                            <?php foreach ($enlaces as $e): ?>
                                <option value="<?= $e['id'] ?>" <?= ($selected_link == $e['id']) ? 'selected' : '' ?>>
                                    https://<?= $_SERVER['HTTP_HOST'] ?>/<?= htmlspecialchars($e['alias']) ?>
                                </option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                    <button type="submit" class="btn btn-success w-100">Filtrar</button>
                </form>
            </div>
        </div>

        <!-- Panel principal con pestañas -->
        <div class="col-md-9">
            <div class="card p-4 shadow-sm">
                <ul class="nav nav-tabs mb-3" id="reportTabs" role="tablist">
                    <li class="nav-item" role="presentation">
                        <button class="nav-link active" data-bs-toggle="tab" data-bs-target="#chart"
                            type="button">Chart</button>
                    </li>
                    <li class="nav-item" role="presentation">
                        <button class="nav-link" data-bs-toggle="tab" data-bs-target="#referer"
                            type="button">Referer</button>
                    </li>
                    <li class="nav-item" role="presentation">
                        <button class="nav-link" data-bs-toggle="tab" data-bs-target="#device"
                            type="button">Device</button>
                    </li>
                    <li class="nav-item" role="presentation">
                        <button class="nav-link" data-bs-toggle="tab" data-bs-target="#platform"
                            type="button">Platform</button>
                    </li>
                    <li class="nav-item" role="presentation">
                        <button class="nav-link" data-bs-toggle="tab" data-bs-target="#browser"
                            type="button">Browser</button>
                    </li>
                </ul>

                <div class="tab-content" id="reportTabsContent">
                    <div class="tab-pane fade show active" id="chart">
                        <h5 class="text-center">Clics por día</h5>
                        <canvas id="chartCanvas" height="200"></canvas>
                    </div>
                    <div class="tab-pane fade" id="referer">
                        <h5>Referers (por ahora no disponible)</h5>
                    </div>
                    <div class="tab-pane fade" id="device">
                        <h5>Resumen por tipo de dispositivo</h5>
                        <div id="deviceData"></div>
                    </div>
                    <div class="tab-pane fade" id="platform">
                        <h5>Resumen por plataforma</h5>
                        <div id="platformData"></div>
                    </div>
                    <div class="tab-pane fade" id="browser">
                        <h5>Resumen por navegador</h5>
                        <div id="browserData"></div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
<script>
    const clicksData = <?= json_encode($clicks_por_dia) ?>;
    const labels = Object.keys(clicksData);
    const data = Object.values(clicksData);

    new Chart(document.getElementById('chartCanvas'), {
        type: 'line',
        data: {
            labels: labels,
            datasets: [{
                label: 'Clics por día',
                data: data,
                borderWidth: 2,
                fill: true,
            }]
        },
        options: {
            responsive: true,
            scales: {
                y: { beginAtZero: true }
            }
        }
    });

    document.getElementById("deviceData").innerHTML = `<?= json_encode($device_data, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE) ?>`.replaceAll("\n", "<br>").replaceAll(" ", "&nbsp;");
    document.getElementById("platformData").innerHTML = `<?= json_encode($platform_data, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE) ?>`.replaceAll("\n", "<br>").replaceAll(" ", "&nbsp;");
    document.getElementById("browserData").innerHTML = `<?= json_encode($browser_data, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE) ?>`.replaceAll("\n", "<br>").replaceAll(" ", "&nbsp;");
</script>
<script>
    function generarTablaResumen(id, datosPorAlias) {
        const contenedor = document.getElementById(id);
        contenedor.innerHTML = "";

        if (!datosPorAlias || Object.keys(datosPorAlias).length === 0) {
            contenedor.innerHTML = "<p class='text-muted'>Sin datos.</p>";
            return;
        }

        for (const alias in datosPorAlias) {
            const tabla = document.createElement('table');
            tabla.className = "table table-bordered table-sm mb-4";
            tabla.innerHTML = `
                <thead class="table-light">
                    <tr>
                        <th colspan="3">https://${location.host}/${alias}</th>
                    </tr>
                    <tr>
                        <th>#</th>
                        <th>Valor</th>
                        <th>Cantidad</th>
                    </tr>
                </thead>
                <tbody>
                    ${Object.entries(datosPorAlias[alias]).map(([valor, count], i) =>
                `<tr><td>${i + 1}</td><td>${valor}</td><td>${count}</td></tr>`
            ).join('')
                }
                </tbody>
            `;
            contenedor.appendChild(tabla);
        }
    }

    // Datos desde PHP
    const deviceData = <?= json_encode($device_data ?? []) ?>;
    const platformData = <?= json_encode($platform_data ?? []) ?>;
    const browserData = <?= json_encode($browser_data ?? []) ?>;

    generarTablaResumen("deviceData", deviceData);
    generarTablaResumen("platformData", platformData);
    generarTablaResumen("browserData", browserData);
</script>