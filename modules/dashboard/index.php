<?php
require_once '../../config/db.php';
require_once '../../includes/functions.php';

$db = getDBConnection();
$year = isset($_GET['year']) ? (int)$_GET['year'] : (int)date('Y');
$month = (int)date('m');

// 0. Get available years for the filter
$stmt_years = $db->query("
    SELECT YEAR(fecha) as anio FROM movimientos 
    UNION 
    SELECT anio_correspondiente as anio FROM pro_luz
    GROUP BY anio
    ORDER BY anio DESC
");
$available_years = $stmt_years->fetchAll(PDO::FETCH_COLUMN);
if (!in_array($year, $available_years)) {
    $available_years[] = $year;
    rsort($available_years);
}

// 1. KPI Data (Filtered by Year)
$stmt_kpi = $db->prepare("SELECT SUM(debe) as total_ingresos, SUM(haber) as total_egresos FROM movimientos WHERE YEAR(fecha) = ?");
$stmt_kpi->execute([$year]);
$kpis = $stmt_kpi->fetch();

$ingresos_anio = $kpis['total_ingresos'] ?? 0;
$egresos_anio = $kpis['total_egresos'] ?? 0;
$saldo_anio = $ingresos_anio - $egresos_anio;

// 2. Pro-Luz Status (Selected Month/Year)
// Note: We use the current month if the selected year is the current year, otherwise we might want to sum up the year?
// The original code used (int)date('m') which is only valid for current year.
// If looking at a past year, maybe show the December status or an average?
// For now, let's keep it as $month, but if it's a past year, it might be better to show a summary.
// The user asked for "estadisticas de diferente año".
$stmt_pl = $db->prepare("
    SELECT 
        (SELECT COUNT(*) FROM personas WHERE activo = 1) as total_personas,
        (SELECT COUNT(*) FROM pro_luz WHERE mes_correspondiente = ? AND anio_correspondiente = ?) as pagados
");
$stmt_pl->execute([$month, $year]);
$pro_luz_status = $stmt_pl->fetch();

// 3. Monthly Trend (Selected Year)
$stmt_trend = $db->prepare("
    SELECT 
        m.mes,
        IFNULL(SUM(mov.debe), 0) as ingresos,
        IFNULL(SUM(mov.haber), 0) as egresos
    FROM (
        SELECT 1 as mes UNION SELECT 2 UNION SELECT 3 UNION SELECT 4 
        UNION SELECT 5 UNION SELECT 6 UNION SELECT 7 UNION SELECT 8 
        UNION SELECT 9 UNION SELECT 10 UNION SELECT 11 UNION SELECT 12
    ) m
    LEFT JOIN movimientos mov ON MONTH(mov.fecha) = m.mes AND YEAR(mov.fecha) = ?
    GROUP BY m.mes
    ORDER BY m.mes ASC
");
$stmt_trend->execute([$year]);
$chart_data = $stmt_trend->fetchAll();

// 4. Latest Movements (Filtered by Year)
$stmt_latest = $db->prepare("SELECT * FROM movimientos WHERE YEAR(fecha) = ? ORDER BY fecha DESC, created_at DESC LIMIT 5");
$stmt_latest->execute([$year]);
$latest_movs = $stmt_latest->fetchAll();

include '../../includes/header.php';
?>

<script>
    document.getElementById('module-title').innerText = 'Dashboard Principal';
</script>

<div class="d-flex justify-content-between align-items-center mb-4">
    <h2 class="h4 mb-0 text-gray-800">Dashboard - <?= $year ?></h2>
    <div class="d-flex align-items-center gap-2">
        <label for="yearFilter" class="form-label mb-0 small text-muted">Filtrar por año:</label>
        <select class="form-select form-select-sm" id="yearFilter" onchange="changeYear(this.value)" style="width: 120px;">
            <?php foreach ($available_years as $y): ?>
                <option value="<?= $y ?>" <?= $y == $year ? 'selected' : '' ?>><?= $y ?></option>
            <?php endforeach; ?>
        </select>
    </div>
</div>

<div class="row g-4 mb-4">
    <div class="col-md-3">
        <div class="card bg-primary text-white p-3 border-0">
            <div class="d-flex justify-content-between align-items-center">
                <div>
                    <small class="text-white-50">Saldo en Caja (<?= $year ?>)</small>
                    <h3 class="fw-bold mb-0"><?= formatCurrency($saldo_anio) ?></h3>
                </div>
                <div class="fs-1 opacity-25"><i class="fas fa-wallet"></i></div>
            </div>
        </div>
    </div>
    <div class="col-md-3">
        <div class="card bg-success text-white p-3 border-0">
            <div class="d-flex justify-content-between align-items-center">
                <div>
                    <small class="text-white-50">Ingresos (<?= $year ?>)</small>
                    <h3 class="fw-bold mb-0"><?= formatCurrency($kpis['total_ingresos'] ?? 0) ?></h3>
                </div>
                <div class="fs-1 opacity-25"><i class="fas fa-arrow-trend-up"></i></div>
            </div>
        </div>
    </div>
    <div class="col-md-3">
        <div class="card bg-danger text-white p-3 border-0">
            <div class="d-flex justify-content-between align-items-center">
                <div>
                    <small class="text-white-50">Egresos (<?= $year ?>)</small>
                    <h3 class="fw-bold mb-0"><?= formatCurrency($kpis['total_egresos'] ?? 0) ?></h3>
                </div>
                <div class="fs-1 opacity-25"><i class="fas fa-file-invoice"></i></div>
            </div>
        </div>
    </div>
    <div class="col-md-3">
        <div class="card bg-warning text-white p-3 border-0">
            <div class="d-flex justify-content-between align-items-center">
                <div>
                    <small class="text-white-50">Pro-Luz (<?= getMonthName($month) ?>)</small>
                    <h3 class="fw-bold mb-0"><?= $pro_luz_status['pagados'] ?> / <?= $pro_luz_status['total_personas'] ?></h3>
                </div>
                <div class="fs-1 opacity-25"><i class="fas fa-bolt"></i></div>
            </div>
        </div>
    </div>
</div>

<div class="row g-4">
    <!-- Chart: Trend -->
    <div class="col-lg-8">
        <div class="card border-0 h-100">
            <div class="card-header bg-transparent border-0 py-3">
                <h6 class="mb-0 fw-bold">Tendencia de Ingresos vs Egresos - <?= $year ?></h6>
            </div>
            <div class="card-body">
                <canvas id="trendChart" style="max-height: 350px;"></canvas>
            </div>
        </div>
    </div>

    <!-- Chart: Pro-Luz Doughnut -->
    <div class="col-lg-4">
        <div class="card border-0 h-100">
            <div class="card-header bg-transparent border-0 py-3">
                <h6 class="mb-0 fw-bold">Cobertura Pro-Luz Mes</h6>
            </div>
            <div class="card-body d-flex flex-column align-items-center justify-content-center">
                <canvas id="proLuzChart" style="max-height: 250px;"></canvas>
                <div class="mt-4 text-center">
                    <h4 class="mb-0 fw-bold text-success"><?= round(($pro_luz_status['pagados'] / max($pro_luz_status['total_personas'], 1)) * 100) ?>%</h4>
                    <small class="text-muted">Aportaciones completadas</small>
                </div>
            </div>
        </div>
    </div>

    <!-- Table: Recent Activity -->
    <div class="col-12">
        <div class="card border-0 shadow-sm">
            <div class="card-header bg-transparent border-0 py-3 d-flex justify-content-between align-items-center">
                <h6 class="mb-0 fw-bold">Últimos Movimientos (<?= $year ?>)</h6>
                <a href="../../index.php" class="btn btn-sm btn-outline-primary">Ver todos</a>
            </div>
            <div class="card-body p-0">
                <div class="table-responsive">
                    <table class="table table-hover align-middle mb-0">
                        <thead>
                            <tr class="table-light">
                                <th class="ps-4">Fecha</th>
                                <th>Factura</th>
                                <th>Detalle</th>
                                <th class="text-end pe-4">Monto</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($latest_movs as $m): ?>
                                <tr>
                                    <td class="ps-4 small"><?= date('d/m/Y', strtotime($m['fecha'])) ?></td>
                                    <td><span class="badge bg-light text-dark border"><?= htmlspecialchars($m['factura'] ?: '-') ?></span></td>
                                    <td><?= htmlspecialchars($m['detalle']) ?></td>
                                    <td class="text-end pe-4 fw-bold <?= $m['debe'] > 0 ? 'text-success' : 'text-danger' ?>">
                                        <?= formatCurrency($m['debe'] > 0 ? $m['debe'] : $m['haber']) ?>
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
</div>

<script>
    // Prepare Data for Charts
    const months = <?= json_encode(array_map(function($m) { return getMonthName($m['mes']); }, $chart_data)) ?>;
    const ingresos = <?= json_encode(array_column($chart_data, 'ingresos')) ?>;
    const egresos = <?= json_encode(array_column($chart_data, 'egresos')) ?>;

    // Trend Chart
    new Chart(document.getElementById('trendChart'), {
        type: 'line',
        data: {
            labels: months,
            datasets: [
                {
                    label: 'Ingresos',
                    data: ingresos,
                    borderColor: '#198754',
                    backgroundColor: 'rgba(25, 135, 84, 0.1)',
                    fill: true,
                    tension: 0.4
                },
                {
                    label: 'Egresos',
                    data: egresos,
                    borderColor: '#dc3545',
                    backgroundColor: 'rgba(220, 53, 69, 0.1)',
                    fill: true,
                    tension: 0.4
                }
            ]
        },
        options: {
            responsive: true,
            plugins: {
                legend: { position: 'top' }
            },
            scales: {
                y: { beginAtZero: true }
            }
        }
    });

    // Pro-Luz Doughnut Chart
    new Chart(document.getElementById('proLuzChart'), {
        type: 'doughnut',
        data: {
            labels: ['Pagado', 'Pendiente'],
            datasets: [{
                data: [<?= $pro_luz_status['pagados'] ?>, <?= max(0, $pro_luz_status['total_personas'] - $pro_luz_status['pagados']) ?>],
                backgroundColor: ['#198754', '#e9ecef'],
                borderWidth: 0,
                hoverOffset: 10
            }]
        },
        options: {
            cutout: '80%',
            plugins: {
                legend: { display: false }
            }
        }
    });

    function changeYear(y) {
        window.location.href = 'index.php?year=' + y;
    }
</script>

<?php include '../../includes/footer.php'; ?>
