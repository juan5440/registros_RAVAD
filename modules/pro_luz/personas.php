<?php
require_once '../../config/db.php';
require_once '../../includes/functions.php';

$db = getDBConnection();

// Fetch all people
$stmt = $db->query("SELECT * FROM personas ORDER BY nombre ASC");
$personas = $stmt->fetchAll();

include '../../includes/header.php';
?>

<script>
    document.getElementById('module-title').innerText = 'Administración de Personas - Pro-Luz';
</script>

<div class="row mb-4">
    <div class="col-md-6">
        <a href="index.php" class="btn btn-outline-secondary">
            <i class="fas fa-arrow-left me-2"></i> Volver a Pagos
        </a>
    </div>
    <div class="col-md-6 text-end">
        <button class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#addPersonaModal">
            <i class="fas fa-user-plus me-2"></i> Nueva Persona
        </button>
    </div>
</div>

<div class="card border-0 shadow-sm">
    <div class="card-body p-0">
        <div class="table-responsive">
            <table class="table table-hover align-middle mb-0">
                <thead class="table-light">
                    <tr>
                        <th class="ps-4">ID</th>
                        <th>Nombre Completo</th>
                        <th class="text-center">Estado</th>
                        <th class="text-center">Fecha Registro</th>
                        <th class="text-center pe-4">Acciones</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($personas as $p): ?>
                        <tr>
                            <td class="ps-4 text-muted">#<?= $p['id'] ?></td>
                            <td class="fw-bold"><?= htmlspecialchars($p['nombre']) ?></td>
                            <td class="text-center">
                                <span class="badge rounded-pill <?= $p['activo'] ? 'bg-success' : 'bg-danger' ?>">
                                    <?= $p['activo'] ? 'Activo' : 'Inactivo' ?>
                                </span>
                            </td>
                            <td class="text-center small text-muted">
                                <?= date('d/m/Y', strtotime($p['created_at'])) ?>
                            </td>
                            <td class="text-center pe-4">
                                <div class="btn-group">
                                    <button class="btn btn-sm btn-outline-primary me-1" data-bs-toggle="modal" data-bs-target="#editModal<?= $p['id'] ?>"><i class="fas fa-edit"></i></button>
                                    <button class="btn btn-sm <?= $p['activo'] ? 'btn-outline-danger' : 'btn-outline-success' ?>"
                                            onclick="swalConfirm('¿Cambiar estado?', 'Deseas <?= $p['activo'] ? 'desactivar' : 'activar' ?> a <?= htmlspecialchars($p['nombre']) ?>?', 'question', () => { window.location.href = 'delete_persona.php?id=<?= $p['id'] ?>&status=<?= $p['activo'] ? 0 : 1 ?>'; })">
                                        <i class="fas <?= $p['activo'] ? 'fa-user-slash' : 'fa-user-check' ?>"></i>
                                    </button>
                                </div>
                            </td>
                        </tr>

                        <!-- Modal Editar -->
                        <div class="modal fade" id="editModal<?= $p['id'] ?>" tabindex="-1">
                            <div class="modal-dialog">
                                <div class="modal-content">
                                    <form action="save_persona.php" method="POST">
                                        <input type="hidden" name="id" value="<?= $p['id'] ?>">
                                        <div class="modal-header">
                                            <h5 class="modal-title">Editar Persona</h5>
                                            <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                                        </div>
                                        <div class="modal-body">
                                            <div class="mb-3">
                                                <label class="form-label">Nombre Completo</label>
                                                <input type="text" name="nombre" class="form-control" value="<?= htmlspecialchars($p['nombre']) ?>" required>
                                            </div>
                                        </div>
                                        <div class="modal-footer">
                                            <button type="submit" class="btn btn-primary">Guardar Cambios</button>
                                        </div>
                                    </form>
                                </div>
                            </div>
                        </div>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>
    </div>
</div>

<!-- Modal Agregar -->
<div class="modal fade" id="addPersonaModal" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <form action="save_persona.php" method="POST">
                <div class="modal-header">
                    <h5 class="modal-title">Registrar Nueva Persona</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body">
                    <div class="mb-3">
                        <label class="form-label">Nombre Completo</label>
                        <input type="text" name="nombre" class="form-control" placeholder="Nombre y Apellido" required>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="submit" class="btn btn-primary w-100">Registrar Persona</button>
                </div>
            </form>
        </div>
    </div>
</div>

<?php include '../../includes/footer.php'; ?>
