<?php
require_once '../../config/db.php';
require_once '../../includes/functions.php';

$db = getDBConnection();
$year = isset($_GET['year']) ? (int)$_GET['year'] : (int)date('Y');

// Fetch monthly summaries for the year
$stmt = $db->prepare("
    SELECT 
        m.mes as numero_mes,
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
$stmt->execute([$year]);
$resumen_anual = $stmt->fetchAll();

include '../../includes/header.php';
?>

<script>
    document.getElementById('module-title').innerText = 'Resumen Anual de Movimientos - ' + <?= $year ?>;
</script>

<!-- Libraries for Export -->
<script src="../../public/vendor/js/xlsx.full.min.js"></script>
<script src="../../public/vendor/js/jspdf.umd.min.js"></script>
<script src="../../public/vendor/js/jspdf.plugin.autotable.min.js"></script>

<div class="row mb-4 align-items-center">
    <div class="col-md-6">
        <form class="row g-2" method="GET">
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
        </form>
    </div>
    <div class="col-md-6 text-end">
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
            <table class="table table-bordered table-hover align-middle mb-0" id="anualTable">
                <thead class="table-dark">
                    <tr>
                        <th class="ps-3">Mes</th>
                        <th class="text-end">Ingresos Totales</th>
                        <th class="text-end">Egresos Totales</th>
                        <th class="text-end">Balance Mensual</th>
                        <th class="text-end pe-3">Saldo Acumulado</th>
                    </tr>
                </thead>
                <tbody>
                    <?php 
                    $saldo_acumulado = 0;
                    $total_v_ingresos = 0;
                    $total_v_egresos = 0;
                    
                    foreach ($resumen_anual as $mes_data): 
                        $balance = $mes_data['ingresos'] - $mes_data['egresos'];
                        $saldo_acumulado += $balance;
                        $total_v_ingresos += $mes_data['ingresos'];
                        $total_v_egresos += $mes_data['egresos'];
                    ?>
                        <tr>
                            <td class="ps-3 fw-bold"><?= getMonthName($mes_data['numero_mes']) ?></td>
                            <td class="text-end text-success"><?= formatCurrency($mes_data['ingresos']) ?></td>
                            <td class="text-end text-danger"><?= formatCurrency($mes_data['egresos']) ?></td>
                            <td class="text-end fw-medium <?= $balance >= 0 ? 'text-primary' : 'text-danger' ?>">
                                <?= formatCurrency($balance) ?>
                            </td>
                            <td class="text-end fw-bold pe-3 bg-light">
                                <?= formatCurrency($saldo_acumulado) ?>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
                <tfoot class="table-dark fw-bold">
                    <tr>
                        <td class="ps-3">TOTALES DEL AÑO</td>
                        <td class="text-end"><?= formatCurrency($total_v_ingresos) ?></td>
                        <td class="text-end"><?= formatCurrency($total_v_egresos) ?></td>
                        <td class="text-end"><?= formatCurrency($total_v_ingresos - $total_v_egresos) ?></td>
                        <td class="text-end pe-3 bg-primary text-white"><?= formatCurrency($saldo_acumulado) ?></td>
                    </tr>
                </tfoot>
            </table>
        </div>
    </div>
</div>

<script>
function exportToExcel() {
    const table = document.getElementById('anualTable');
    const wb = XLSX.utils.table_to_book(table, { sheet: "Resumen Anual <?= $year ?>" });
    XLSX.writeFile(wb, "Resumen_Anual_General_<?= $year ?>.xlsx");
}

function exportToPDF() {
    const { jsPDF } = window.jspdf;
    const doc = new jsPDF('p', 'mm', 'a4');
    
    doc.setFontSize(16);
    doc.text("Resumen Anual de Movimientos - <?= $year ?>", 14, 15);
    doc.setFontSize(11);
    doc.text("Detalle mensual de Ingresos y Egresos", 14, 22);
    
    doc.autoTable({
        html: '#anualTable',
        startY: 30,
        theme: 'grid',
        styles: { fontSize: 9, cellPadding: 3 },
        headStyles: { fillColor: [33, 37, 41] },
        footStyles: { fillColor: [33, 37, 41], textColor: [255, 255, 255] }
    });
    
    doc.save("Resumen_Anual_General_<?= $year ?>.pdf");
}
</script>

<?php include '../../includes/footer.php'; ?>
