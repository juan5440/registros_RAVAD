<?php
require_once 'config/db.php';
require_once 'includes/functions.php';

$db = getDBConnection();

// Filters
$month = isset($_GET['month']) ? (int)$_GET['month'] : (int)date('m');
$year = isset($_GET['year']) ? (int)$_GET['year'] : (int)date('Y');

// Fetch records for the selected month/year
$stmt = $db->prepare("SELECT * FROM movimientos WHERE MONTH(fecha) = ? AND YEAR(fecha) = ? ORDER BY fecha DESC, id DESC");
$stmt->execute([$month, $year]);
$movimientos = $stmt->fetchAll();

// Calculate totals for the selected period
$stmt_totals = $db->prepare("SELECT SUM(debe) as total_debe, SUM(haber) as total_haber FROM movimientos WHERE MONTH(fecha) = ? AND YEAR(fecha) = ?");
$stmt_totals->execute([$month, $year]);
$totals = $stmt_totals->fetch();

include 'includes/header.php';
?>

<script>
    document.getElementById('module-title').innerText = 'Registro General - <?= getMonthName($month) ?> <?= $year ?>';
</script>

<div class="row mb-4">
    <div class="col-md-8">
        <form class="row g-3" method="GET">
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
    <div class="col-md-4 text-end">
        <div class="btn-group">
            <div class="card bg-primary text-white p-2 px-3 me-2">
                <small>Total Ingresos</small>
                <strong><?= formatCurrency($totals['total_debe'] ?? 0) ?></strong>
            </div>
            <div class="card bg-danger text-white p-2 px-3">
                <small>Total Egresos</small>
                <strong><?= formatCurrency($totals['total_haber'] ?? 0) ?></strong>
            </div>
        </div>
    </div>
</div>

<div class="card shadow-sm border-0">
    <div class="card-body p-0">
        <div class="table-responsive">
            <table class="table table-hover align-middle mb-0">
                <thead class="table-light">
                    <tr>
                        <th class="ps-4">Fecha</th>
                        <th>Factura</th>
                        <th>Detalle / Descripción</th>
                        <th class="text-end">Ingreso (Debe)</th>
                        <th class="text-end">Egreso (Haber)</th>
                        <th class="text-end">Saldo</th>
                        <th class="text-center pe-4">Acciones</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if (empty($movimientos)): ?>
                        <tr>
                            <td colspan="7" class="text-center py-5 text-muted">No hay registros para este período.</td>
                        </tr>
                    <?php else: ?>
                        <?php foreach ($movimientos as $row): ?>
                            <tr>
                                <td class="ps-4"><?= date('d/m/Y', strtotime($row['fecha'])) ?></td>
                                <td><span class="badge bg-secondary-subtle text-secondary px-2"><?= htmlspecialchars($row['factura'] ?: '-') ?></span></td>
                                <td>
                                    <?= htmlspecialchars($row['detalle']) ?>
                                    <br><small class="text-muted">Origen: <?= ucfirst($row['origen']) ?></small>
                                </td>
                                <td class="text-end text-success fw-medium"><?= formatCurrency($row['debe']) ?></td>
                                <td class="text-end text-danger fw-medium"><?= formatCurrency($row['haber']) ?></td>
                                <td class="text-end fw-bold"><?= formatCurrency($row['saldo']) ?></td>
                                <td class="text-center pe-4">
                                    <div class="btn-group">
                                        <?php if (!empty($row['factura'])): ?>
                                            <?php if ($row['foto_factura']): ?>
                                                <button class="btn btn-sm btn-info me-1" data-bs-toggle="modal" data-bs-target="#viewFactura<?= $row['id'] ?>" title="Ver Foto Factura">
                                                    <i class="fas fa-image"></i>
                                                </button>
                                            <?php else: ?>
                                                <button class="btn btn-sm btn-outline-warning me-1" data-bs-toggle="modal" data-bs-target="#uploadFactura<?= $row['id'] ?>" title="Subir Foto Factura">
                                                    <i class="fas fa-camera"></i>
                                                </button>
                                            <?php endif; ?>
                                        <?php endif; ?>
                                        <button class="btn btn-sm btn-outline-primary me-1"><i class="fas fa-edit"></i></button>
                                        <button class="btn btn-sm btn-outline-danger"><i class="fas fa-trash"></i></button>
                                    </div>
                                </td>
                            </tr>

                            <!-- Modal para Subir Factura -->
                            <?php if (!empty($row['factura']) && !$row['foto_factura']): ?>
                            <div class="modal fade" id="uploadFactura<?= $row['id'] ?>" tabindex="-1">
                                <div class="modal-dialog">
                                    <div class="modal-content">
                                        <form action="modules/general/upload_factura.php" method="POST" enctype="multipart/form-data">
                                            <input type="hidden" name="id" value="<?= $row['id'] ?>">
                                            <div class="modal-header">
                                                <h5 class="modal-title">Subir Foto de Factura: <?= htmlspecialchars($row['factura']) ?></h5>
                                                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                                            </div>
                                            <div class="modal-body">
                                                <div class="mb-3">
                                                    <label class="form-label">Seleccionar Imagen (JPG, PNG, WebP)</label>
                                                    <input type="file" name="foto" class="form-control" accept="image/*" required>
                                                </div>
                                                <p class="text-muted small">Carga una foto clara de la factura física para respaldo digital.</p>
                                            </div>
                                            <div class="modal-footer">
                                                <button type="submit" class="btn btn-warning w-100">Subir Imagen</button>
                                            </div>
                                        </form>
                                    </div>
                                </div>
                            </div>
                            <?php endif; ?>

                            <!-- Modal para Ver Factura -->
                            <?php if ($row['foto_factura']): ?>
                            <div class="modal fade" id="viewFactura<?= $row['id'] ?>" tabindex="-1">
                                <div class="modal-dialog modal-lg">
                                    <div class="modal-content">
                                        <div class="modal-header">
                                            <h5 class="modal-title">Factura: <?= htmlspecialchars($row['factura']) ?></h5>
                                            <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                                        </div>
                                        <div class="modal-body text-center p-0">
                                            <img src="public/uploads/facturas/<?= $row['foto_factura'] ?>" class="img-fluid rounded-bottom" alt="Factura">
                                        </div>
                                        <div class="modal-footer">
                                            <a href="public/uploads/facturas/<?= $row['foto_factura'] ?>" target="_blank" class="btn btn-info"><i class="fas fa-external-link-alt me-2"></i>Ver pantalla completa</a>
                                            <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cerrar</button>
                                        </div>
                                    </div>
                                </div>
                            </div>
                            <?php endif; ?>
                        <?php endforeach; ?>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
    </div>
</div>

<!-- Modal para Nuevo Registro -->
<div class="modal fade" id="addRecordModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">
            <form action="modules/general/save.php" method="POST">
                <div class="modal-header">
                    <h5 class="modal-title">Nuevo Registro General</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <div class="row">
                        <div class="col-md-6 mb-3">
                            <label class="form-label">Fecha</label>
                            <input type="date" name="fecha" class="form-control" value="<?= date('Y-m-d') ?>" required>
                        </div>
                        <div class="col-md-6 mb-3">
                            <label class="form-label">Factura #</label>
                            <input type="text" name="factura" class="form-control" placeholder="Ej: F-123">
                        </div>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Detalle / Concepto</label>
                        <input type="text" name="detalle" class="form-control" placeholder="Ej: Pago de servicios" required>
                    </div>
                    <div class="row">
                        <div class="col-md-6 mb-3">
                            <label class="form-label">Ingreso (Debe)</label>
                            <input type="number" step="0.01" name="debe" class="form-control" value="0.00">
                        </div>
                        <div class="col-md-6 mb-3">
                            <label class="form-label">Egreso (Haber)</label>
                            <input type="number" step="0.01" name="haber" class="form-control" value="0.00">
                        </div>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancelar</button>
                    <button type="submit" class="btn btn-primary">Guardar Registro</button>
                </div>
            </form>
        </div>
    </div>
</div>

<?php include 'includes/footer.php'; ?>
