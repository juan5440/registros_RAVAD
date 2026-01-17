<?php
require_once '../../config/db.php';
require_once '../../includes/functions.php';

$db = getDBConnection();
$year = isset($_GET['year']) ? (int)$_GET['year'] : (int)date('Y');

// Fetch all people and their payments for the entire year
$stmt = $db->prepare("
    SELECT 
        p.id, 
        p.nombre, 
        m.mes,
        pl.monto,
        (SELECT SUM(monto) FROM pro_luz WHERE persona_id = p.id) as total_historico
    FROM personas p
    CROSS JOIN (
        SELECT 1 as mes UNION SELECT 2 UNION SELECT 3 UNION SELECT 4 
        UNION SELECT 5 UNION SELECT 6 UNION SELECT 7 UNION SELECT 8 
        UNION SELECT 9 UNION SELECT 10 UNION SELECT 11 UNION SELECT 12
    ) m
    LEFT JOIN pro_luz pl ON p.id = pl.persona_id 
        AND pl.mes_correspondiente = m.mes 
        AND pl.anio_correspondiente = ?
    WHERE p.activo = 1
    ORDER BY p.nombre ASC, m.mes ASC
");
$stmt->execute([$year]);
$results = $stmt->fetchAll();

// Group data by person
$report_data = [];
foreach ($results as $row) {
    if (!isset($report_data[$row['id']])) {
        $report_data[$row['id']] = [
            'nombre' => $row['nombre'],
            'total_historico' => $row['total_historico'] ?: 0,
            'meses' => array_fill(1, 12, 0)
        ];
    }
    $report_data[$row['id']]['meses'][$row['mes']] = $row['monto'];
}

include '../../includes/header.php';
?>

<script>
    document.getElementById('module-title').innerText = 'Control Anual de Aportaciones - ' + <?= $year ?>;
</script>

<!-- Libraries for Export -->
<script src="../../public/vendor/js/xlsx.full.min.js"></script>
<script src="../../public/vendor/js/jspdf.umd.min.js"></script>
<script src="../../public/vendor/js/jspdf.plugin.autotable.min.js"></script>

<div class="row mb-4">
    <div class="col-md-6">
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

<div class="card border-0">
    <div class="card-body p-0">
        <div class="table-responsive">
            <table class="table table-bordered table-hover align-middle mb-0" id="reportTable">
                <thead class="table-light">
                    <tr class="text-center">
                        <th class="text-start ps-3" style="min-width: 250px;">Persona</th>
                        <?php for($m=1; $m<=12; $m++): ?>
                            <th style="font-size: 0.75rem;"><?= mb_substr(getMonthName($m), 0, 3, 'UTF-8') ?></th>
                        <?php endfor; ?>
                        <th>Total Histórico</th>
                    </tr>
                </thead>
                <tbody>
                    <?php 
                    $grand_total_hist = 0;
                    foreach ($report_data as $person): 
                        $grand_total_hist += $person['total_historico'];
                    ?>
                        <tr>
                            <td class="ps-3 fw-bold small"><?= htmlspecialchars($person['nombre']) ?></td>
                            <?php for($m=1; $m<=12; $m++): 
                                $monto = $person['meses'][$m];
                            ?>
                                <td class="text-center p-1">
                                    <?php if ($monto > 0): ?>
                                        <div class="bg-success text-white rounded-circle d-inline-block" style="width: 20px; height: 20px; line-height: 20px; font-size: 10px;">
                                            <i class="fas fa-check"></i>
                                        </div>
                                    <?php else: ?>
                                        <div class="bg-light text-muted rounded-circle d-inline-block border" style="width: 20px; height: 20px; line-height: 20px; font-size: 10px;">
                                            <i class="fas fa-times"></i>
                                        </div>
                                    <?php endif; ?>
                                </td>
                            <?php endfor; ?>
                            <td class="text-end pe-2 fw-bold bg-dark text-white">
                                <?= formatCurrency($person['total_historico']) ?>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
                <tfoot class="table-light fw-bold border-top border-2">
                    <tr>
                        <td class="ps-3" colspan="13">TOTAL HISTÓRICO RECAUDADO (TODOS)</td>
                        <td class="text-end pe-2 bg-primary text-white fs-5">
                            <?= formatCurrency($grand_total_hist) ?>
                        </td>
                    </tr>
                </tfoot>
            </table>
        </div>
    </div>
</div>

<script>
function exportToExcel() {
    const table = document.getElementById('reportTable');
    const wb = XLSX.utils.table_to_book(table, { sheet: "Aportaciones <?= $year ?>" });
    XLSX.writeFile(wb, "Reporte_Aportaciones_<?= $year ?>.xlsx");
}

function exportToPDF() {
    const { jsPDF } = window.jspdf;
    const doc = new jsPDF('l', 'mm', 'a4');
    
    doc.setFontSize(18);
    doc.text("Reporte Anual de Aportaciones Pro-Luz - <?= $year ?>", 14, 15);
    
    doc.autoTable({
        html: '#reportTable',
        startY: 25,
        theme: 'grid',
        styles: { fontSize: 8, cellPadding: 2 },
        headStyles: { fillColor: [33, 37, 41] },
        didParseCell: function(data) {
            // Reemplazar iconos con texto para el PDF
            if (data.section === 'body' && data.column.index >= 1 && data.column.index <= 12) {
                if (data.cell.raw && data.cell.raw.innerHTML && data.cell.raw.innerHTML.includes('fa-check')) {
                    data.cell.text = 'SÍ';
                } else {
                    data.cell.text = '-';
                }
            }
        }
    });
    
    doc.save("Reporte_Aportaciones_<?= $year ?>.pdf");
}
</script>

<?php include '../../includes/footer.php'; ?>
