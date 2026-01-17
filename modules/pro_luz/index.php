<?php
require_once '../../config/db.php';
require_once '../../includes/functions.php';

$db = getDBConnection();

$month = isset($_GET['month']) ? (int)$_GET['month'] : (int)date('m');
$year = isset($_GET['year']) ? (int)$_GET['year'] : (int)date('Y');

// Fetch all people and their payment status for this month
$stmt = $db->prepare("
    SELECT 
        p.id, 
        p.nombre, 
        pl.monto, 
        pl.fecha, 
        pl.procesado,
        (SELECT SUM(monto) FROM pro_luz WHERE persona_id = p.id) as total_historico
    FROM personas p
    LEFT JOIN pro_luz pl ON p.id = pl.persona_id 
        AND pl.mes_correspondiente = ? 
        AND pl.anio_correspondiente = ?
    WHERE p.activo = 1
    ORDER BY p.nombre ASC
");
$stmt->execute([$month, $year]);
$people_status = $stmt->fetchAll();

// Total to sync (not yet processed)
$stmt_unsynced = $db->prepare("SELECT SUM(monto) FROM pro_luz WHERE mes_correspondiente = ? AND anio_correspondiente = ? AND procesado = 0");
$stmt_unsynced->execute([$month, $year]);
$total_unsynced = (float)$stmt_unsynced->fetchColumn();

include '../../includes/header.php';
?>

<script>
    document.getElementById('module-title').innerText = 'Aportaciones Pro-Luz - <?= getMonthName($month) ?> <?= $year ?>';
</script>

<div class="row align-items-center mb-4">
    <!-- Filters -->
    <div class="col-lg-5">
        <form class="row g-2" method="GET">
            <div class="col-auto">
                <select name="month" class="form-select form-select-sm">
                    <?php for($m=1; $m<=12; $m++): ?>
                        <option value="<?= $m ?>" <?= $m == $month ? 'selected' : '' ?>><?= getMonthName($m) ?></option>
                    <?php endfor; ?>
                </select>
            </div>
            <div class="col-auto">
                <select name="year" class="form-select form-select-sm">
                    <?php for($y=date('Y'); $y>=date('Y')-5; $y--): ?>
                        <option value="<?= $y ?>" <?= $y == $year ? 'selected' : '' ?>><?= $y ?></option>
                    <?php endfor; ?>
                </select>
            </div>
            <div class="col-auto">
                <button type="submit" class="btn btn-sm btn-secondary">Filtrar</button>
            </div>
        </form>
    </div>

    <!-- Actions -->
    <div class="col-lg-7 text-end">
        <div class="d-flex align-items-center justify-content-end gap-2 flex-wrap">
            <a href="personas.php" class="btn btn-sm btn-outline-primary">
                <i class="fas fa-users-cog me-2"></i> Administrar Personas
            </a>
            
            <?php if ($total_unsynced > 0): ?>
                <form action="sync.php" method="POST" class="d-inline">
                    <input type="hidden" name="month" value="<?= $month ?>">
                    <input type="hidden" name="year" value="<?= $year ?>">
                    <button type="submit" class="btn btn-sm btn-warning" onclick="return confirmAndSubmit(event, '¿Sincronizar Datos?', 'Se registrará el total de <?= formatCurrency($total_unsynced) ?> en el Registro General como aporte de Pro-Luz.')">
                        <i class="fas fa-sync me-2"></i> Sincronizar
                    </button>
                </form>
            <?php endif; ?>

            <div class="card bg-success text-white border-0 shadow-sm" style="min-width: 160px;">
                <div class="card-body p-2 d-flex flex-column align-items-center">
                    <small class="text-uppercase fw-bold opacity-75" style="font-size: 0.65rem;">Recaudado Mes</small>
                    <div class="fs-5 fw-bold leading-tight"><?= formatCurrency(array_sum(array_column($people_status, 'monto'))) ?></div>
                </div>
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
                        <th class="text-center">Nro.</th>
                        <th class="ps-4">Persona</th>
                        <th class="text-center">Estado</th>
                        <th class="text-end">Monto Mes</th>
                        <th class="text-end">Total Histórico</th>
                        <th class="text-center">Fecha Pago</th>
                        <th class="text-center pe-4">Acciones</th>
                    </tr>
                </thead>
                <tbody>
                    <?php $i = 1; foreach ($people_status as $row): ?>
                        <tr class="<?= is_null($row['monto']) ? 'row-pending' : '' ?>">
                            <td class="text-center text-muted small"><?= $i++ ?></td>
                            <td class="ps-4">
                                <span class="fw-bold"><?= htmlspecialchars($row['nombre']) ?></span>
                            </td>
                            <td class="text-center">
                                <?php if(is_null($row['monto'])): ?>
                                    <span class="status-badge status-pending"><i class="fas fa-clock"></i> PENDIENTE</span>
                                <?php elseif($row['procesado']): ?>
                                    <span class="status-badge status-synced"><i class="fas fa-sync-alt"></i> SINCRONIZADO</span>
                                <?php else: ?>
                                    <span class="status-badge status-paid"><i class="fas fa-check-circle"></i> PAGADO</span>
                                <?php endif; ?>
                            </td>
                            <td class="text-end fw-bold">
                                <?= !is_null($row['monto']) ? formatCurrency($row['monto']) : '-' ?>
                            </td>
                            <td class="text-end fw-bold text-primary">
                                <?= formatCurrency($row['total_historico'] ?: 0) ?>
                            </td>
                            <td class="text-center">
                                <?= !is_null($row['fecha']) ? date('d/m/Y', strtotime($row['fecha'])) : '-' ?>
                            </td>
                            <td class="text-center pe-4">
                                <div class="btn-group">
                                    <button class="btn btn-sm btn-outline-info me-1" data-bs-toggle="modal" data-bs-target="#historyModal<?= $row['id'] ?>" title="Ver Historial">
                                        <i class="fas fa-history"></i>
                                    </button>
                                    <?php if(is_null($row['monto'])): ?>
                                        <button class="btn btn-sm btn-success" data-bs-toggle="modal" data-bs-target="#payModal<?= $row['id'] ?>">
                                            <i class="fas fa-check me-1"></i> Registrar
                                        </button>
                                    <?php elseif(!$row['procesado']): ?>
                                        <button class="btn btn-sm btn-outline-danger" title="Anular Registro (No implementado)"><i class="fas fa-undo"></i></button>
                                    <?php endif; ?>
                                </div>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>
    </div>
</div>

<!-- Modals Section (Outside Table for proper layout) -->
<?php foreach ($people_status as $row): ?>
    <!-- Modal de Historial Individual -->
    <div class="modal fade" id="historyModal<?= $row['id'] ?>" tabindex="-1">
        <div class="modal-dialog modal-lg">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title">Historial de Aportaciones: <?= htmlspecialchars($row['nombre']) ?></h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body p-0">
                    <?php
                    $stmt_hist = $db->prepare("SELECT * FROM pro_luz WHERE persona_id = ? ORDER BY anio_correspondiente DESC, mes_correspondiente DESC");
                    $stmt_hist->execute([$row['id']]);
                    $historial = $stmt_hist->fetchAll();
                    ?>
                    <div class="table-responsive">
                        <table class="table table-sm table-hover mb-0">
                            <thead class="table-light">
                                <tr>
                                    <th class="ps-3">Fecha de Pago</th>
                                    <th>Periodo Corresponde</th>
                                    <th class="text-end">Monto</th>
                                    <th class="text-center">Estado</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php if (empty($historial)): ?>
                                    <tr><td colspan="4" class="text-center py-4 text-muted">Sin registros históricos</td></tr>
                                <?php else: ?>
                                    <?php foreach ($historial as $h): ?>
                                        <tr>
                                            <td class="ps-3 small"><?= date('d/m/Y', strtotime($h['fecha'])) ?></td>
                                            <td><strong><?= getMonthName($h['mes_correspondiente']) ?></strong> <?= $h['anio_correspondiente'] ?></td>
                                            <td class="text-end"><?= formatCurrency($h['monto']) ?></td>
                                            <td class="text-center">
                                                <span class="badge <?= $h['procesado'] ? 'bg-success' : 'bg-primary' ?> rounded-pill">
                                                    <?= $h['procesado'] ? 'Sincronizado' : 'Pend. Sinc' ?>
                                                </span>
                                            </td>
                                        </tr>
                                    <?php endforeach; ?>
                                <?php endif; ?>
                            </tbody>
                        </table>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cerrar</button>
                </div>
            </div>
        </div>
    </div>

    <!-- Modal de Pago Individual -->
    <?php if(is_null($row['monto'])): ?>
    <div class="modal fade" id="payModal<?= $row['id'] ?>" tabindex="-1">
        <div class="modal-dialog">
            <div class="modal-content">
                <form action="save.php" method="POST">
                    <input type="hidden" name="persona_id" value="<?= $row['id'] ?>">
                    <input type="hidden" name="mes_c" value="<?= $month ?>">
                    <input type="hidden" name="anio_c" value="<?= $year ?>">
                    <div class="modal-header">
                        <h5 class="modal-title">Registrar Pago: <?= htmlspecialchars($row['nombre']) ?></h5>
                        <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                    </div>
                    <div class="modal-body">
                        <div class="mb-3">
                            <label class="form-label">Fecha de Pago</label>
                            <input type="date" name="fecha" class="form-control" value="<?= date('Y-m-d') ?>" required>
                        </div>
                        <div class="mb-3">
                            <label class="form-label">Monto (Sugerido: $1.00)</label>
                            <input type="number" step="0.01" name="monto" class="form-control" value="1.00" required>
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="submit" class="btn btn-primary w-100">Confirmar Pago</button>
                    </div>
                </form>
            </div>
        </div>
    </div>
    <?php endif; ?>
<?php endforeach; ?>

<?php include '../../includes/footer.php'; ?>
