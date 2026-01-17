<?php
require_once '../../config/db.php';
require_once '../../includes/functions.php';

$db = getDBConnection();
$month = isset($_GET['month']) ? (int)$_GET['month'] : (int)date('m');
$year = isset($_GET['year']) ? (int)$_GET['year'] : (int)date('Y');

// Fetch detailed movements
$stmt = $db->prepare("SELECT * FROM movimientos WHERE MONTH(fecha) = ? AND YEAR(fecha) = ? ORDER BY fecha ASC, id ASC");
$stmt->execute([$month, $year]);
$movimientos = $stmt->fetchAll();

// Calculate totals
$total_ingresos = 0;
$total_egresos = 0;
foreach ($movimientos as $m) {
    $total_ingresos += $m['debe'];
    $total_egresos += $m['haber'];
}

include '../../includes/header.php';
?>

<script>
    document.getElementById('module-title').innerText = 'Reporte Detallado de Movimientos - ' + '<?= getMonthName($month) ?>' + ' <?= $year ?>';
</script>

<!-- Libraries for Export -->
<script src="../../public/vendor/js/xlsx.full.min.js"></script>
<script src="../../public/vendor/js/jspdf.umd.min.js"></script>
<script src="../../public/vendor/js/jspdf.plugin.autotable.min.js"></script>

<div class="row mb-4 align-items-center">
    <div class="col-md-7">
        <form class="row g-2" method="GET">
            <div class="col-auto">
                <select name="month" class="form-select">
                    <?php for($m=1; $m<=12; $m++): ?>
                        <option value="<?= $m ?>" <?= $m == $month ? 'selected' : '' ?>><?= getMonthName($m) ?></option>
                    <?php endfor; ?>
                </select>
            </div>
            <div class="col-auto">
                <select name="year" class="form-select">
                    <?php for($y=date('Y'); $y>=date('Y')-5; $y--): ?>
                        <option value="<?= $y ?>" <?= $y == $year ? 'selected' : '' ?>><?= $y ?></option>
                    <?php endfor; ?>
                </select>
            </div>
            <div class="col-auto">
                <button type="submit" class="btn btn-secondary">Filtrar</button>
            </div>
        </form>
    </div>
    <div class="col-md-5 text-end">
        <button onclick="exportToExcel()" class="btn btn-success me-2">
            <i class="fas fa-file-excel me-2"></i> Excel
        </button>
        <button onclick="exportToPDF()" class="btn btn-danger">
            <i class="fas fa-file-pdf me-2"></i> PDF
        </button>
    </div>
</div>

<div class="card border-0 shadow-sm">
    <div class="card-body p-0">
        <div class="table-responsive">
            <table class="table table-bordered table-hover align-middle mb-0" id="movimientosTable">
                <thead class="table-dark">
                    <tr>
                        <th class="ps-3">Fecha</th>
                        <th>Factura</th>
                        <th>Detalle / Concepto</th>
                        <th class="text-end">Ingreso</th>
                        <th class="text-end">Egreso</th>
                        <th class="text-end pe-3">Saldo</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if (empty($movimientos)): ?>
                        <tr><td colspan="6" class="text-center py-5 text-muted">No hay movimientos registrados para este período.</td></tr>
                    <?php else: ?>
                        <?php foreach ($movimientos as $row): ?>
                            <tr>
                                <td class="ps-3 small"><?= date('d/m/Y', strtotime($row['fecha'])) ?></td>
                                <td><span class="badge bg-light text-dark border"><?= htmlspecialchars($row['factura'] ?: '-') ?></span></td>
                                <td class="small"><?= htmlspecialchars($row['detalle']) ?></td>
                                <td class="text-end text-success"><?= $row['debe'] > 0 ? formatCurrency($row['debe']) : '-' ?></td>
                                <td class="text-end text-danger"><?= $row['haber'] > 0 ? formatCurrency($row['haber']) : '-' ?></td>
                                <td class="text-end fw-bold pe-3"><?= formatCurrency($row['saldo']) ?></td>
                            </tr>
                        <?php endforeach; ?>
                    <?php endif; ?>
                </tbody>
                <tfoot class="table-light fw-bold border-top border-2">
                    <tr>
                        <td colspan="3" class="ps-3 text-uppercase">Totales del Período</td>
                        <td class="text-end text-success"><?= formatCurrency($total_ingresos) ?></td>
                        <td class="text-end text-danger"><?= formatCurrency($total_egresos) ?></td>
                        <td class="text-end pe-3 bg-primary text-white">
                            <?= !empty($movimientos) ? formatCurrency(end($movimientos)['saldo']) : '$0.00' ?>
                        </td>
                    </tr>
                </tfoot>
            </table>
        </div>
    </div>
</div>

<script>
function exportToExcel() {
    const table = document.getElementById('movimientosTable');
    const wb = XLSX.utils.table_to_book(table, { sheet: "Movimientos <?= getMonthName($month) ?> <?= $year ?>" });
    XLSX.writeFile(wb, "Reporte_Movimientos_<?= getMonthName($month) ?>_<?= $year ?>.xlsx");
}

function exportToPDF() {
    const { jsPDF } = window.jspdf;
    const doc = new jsPDF('p', 'mm', 'a4');
    
    doc.setFontSize(16);
    doc.text("Reporte Mensual de Movimientos - RAVAD", 14, 15);
    doc.setFontSize(11);
    doc.text("Período: <?= getMonthName($month) ?> <?= $year ?>", 14, 22);
    
    doc.autoTable({
        html: '#movimientosTable',
        startY: 30,
        theme: 'grid',
        styles: { fontSize: 8, cellPadding: 2 },
        headStyles: { fillColor: [33, 37, 41] },
        footStyles: { fillColor: [248, 249, 250], textColor: [33, 37, 41], fontStyle: 'bold' }
    });
    
    doc.save("Reporte_Movimientos_<?= getMonthName($month) ?>_<?= $year ?>.pdf");
}
</script>

<?php include '../../includes/footer.php'; ?>
