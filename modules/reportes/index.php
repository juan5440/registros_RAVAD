<?php
require_once '../../config/db.php';
require_once '../../includes/functions.php';

$db = getDBConnection();

// Summary by year
$year = isset($_GET['year']) ? (int)$_GET['year'] : (int)date('Y');

$stmt = $db->prepare("
    SELECT 
        MONTH(fecha) as mes, 
        SUM(debe) as total_ingresos, 
        SUM(haber) as total_egresos
    FROM movimientos 
    WHERE YEAR(fecha) = ?
    GROUP BY MONTH(fecha)
    ORDER BY mes ASC
");
$stmt->execute([$year]);
$monthly_summary = $stmt->fetchAll();

include '../../includes/header.php';
?>

<script>
    document.getElementById('module-title').innerText = 'Reportes Financieros - Resumen ' + <?= $year ?>;
</script>

<div class="row mb-4">
    <div class="col-md-12">
        <form class="row g-3" method="GET">
            <div class="col-auto">
                <select name="year" class="form-select">
                    <?php for($y=date('Y'); $y>=date('Y')-5; $y--): ?>
                        <option value="<?= $y ?>" <?= $y == $year ? 'selected' : '' ?>><?= $y ?></option>
                    <?php endfor; ?>
                </select>
            </div>
            <div class="col-auto">
                <button type="submit" class="btn btn-secondary">Cambiar Año</button>
            </div>
            <div class="col-auto">
                <a href="aportaciones_anual.php?year=<?= $year ?>" class="btn btn-outline-primary">
                    <i class="fas fa-list-check me-2"></i> Reporte Anual Pro-Luz
                </a>
            </div>
            <div class="col-auto">
                <a href="movimientos_export.php" class="btn btn-primary">
                    <i class="fas fa-file-invoice-dollar me-2"></i> Reporte Detallado de Movimientos
                </a>
            </div>
            <div class="col-auto">
                <a href="movimientos_anual.php?year=<?= $year ?>" class="btn btn-outline-info">
                    <i class="fas fa-calendar-check me-2"></i> Reporte Anual General
                </a>
            </div>
        </form>
    </div>
</div>

<div class="row">
    <div class="col-md-9">
        <div class="card border-0 mb-4">
            <div class="card-header border-bottom py-3">
                <h6 class="mb-0 fw-bold"><i class="fas fa-table me-2"></i> Desglose Mensual <?= $year ?></h6>
            </div>
            <div class="card-body p-0">
                <div class="table-responsive">
                    <table class="table table-hover mb-0">
                        <thead class="table-light">
                            <tr>
                                <th class="ps-4">Mes</th>
                                <th class="text-end">Ingresos</th>
                                <th class="text-end">Egresos</th>
                                <th class="text-end pe-4">Balance Mensual</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php 
                            $grand_ingresos = 0;
                            $grand_egresos = 0;
                            foreach ($monthly_summary as $row): 
                                $grand_ingresos += $row['total_ingresos'];
                                $grand_egresos += $row['total_egresos'];
                                $balance = $row['total_ingresos'] - $row['total_egresos'];
                            ?>
                                <tr>
                                    <td class="ps-4 fw-medium"><?= getMonthName($row['mes']) ?></td>
                                    <td class="text-end text-success"><?= formatCurrency($row['total_ingresos']) ?></td>
                                    <td class="text-end text-danger"><?= formatCurrency($row['total_egresos']) ?></td>
                                    <td class="text-end fw-bold pe-4 <?= $balance >= 0 ? 'text-primary' : 'text-danger' ?>">
                                        <?= formatCurrency($balance) ?>
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                        </tbody>
                        <tfoot class="table-light fw-bold">
                            <tr>
                                <td class="ps-4">TOTAL ANUAL</td>
                                <td class="text-end text-success border-top-0"><?= formatCurrency($grand_ingresos) ?></td>
                                <td class="text-end text-danger border-top-0"><?= formatCurrency($grand_egresos) ?></td>
                                <td class="text-end pe-4 border-top-0"><?= formatCurrency($grand_ingresos - $grand_egresos) ?></td>
                            </tr>
                        </tfoot>
                    </table>
                </div>
            </div>
        </div>
    </div>
    
    <div class="col-md-3">
        <div class="card border-0 bg-primary text-white mb-4 text-center p-4">
            <small class="opacity-75 text-uppercase fw-bold letter-spacing-1">Saldo Actual en Caja</small>
            <?php 
                $stmt_saldo = $db->query("SELECT saldo FROM movimientos ORDER BY fecha DESC, id DESC LIMIT 1");
                $current_saldo = (float)$stmt_saldo->fetchColumn() ?: 0;
            ?>
            <h1 class="mb-0 mt-2 fw-bold text-white"><?= formatCurrency($current_saldo) ?></h1>
        </div>
        
        <div class="card border-0 p-4 mb-4">
            <h6 class="fw-bold mb-3">Distribución</h6>
            <div class="d-flex justify-content-between mb-2">
                <span>Ingresos</span>
                <span class="text-success"><?= number_format($grand_ingresos > 0 ? 100 : 0, 1) ?>%</span>
            </div>
            <div class="progress mb-3" style="height: 6px;">
              <div class="progress-bar bg-success" style="width: 100%"></div>
            </div>
            <div class="d-flex justify-content-between mb-2">
                <span>Gastos vs Ingresos</span>
                <?php $perc = $grand_ingresos > 0 ? ($grand_egresos / $grand_ingresos) * 100 : 0; ?>
                <span><?= number_format($perc, 1) ?>%</span>
            </div>
            <div class="progress" style="height: 6px;">
              <div class="progress-bar bg-danger" style="width: <?= min(100, $perc) ?>%"></div>
            </div>
        </div>
    </div>
</div>

<?php include '../../includes/footer.php'; ?>
