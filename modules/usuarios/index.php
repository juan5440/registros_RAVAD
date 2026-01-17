<?php
require_once '../../config/db.php';
$db = getDBConnection();

// Fetch all users
$stmt = $db->query("SELECT id, username, nombre_completo, ultimo_acceso FROM usuarios ORDER BY id DESC");
$usuarios = $stmt->fetchAll();

include '../../includes/header.php';
?>

<div class="d-flex justify-content-between align-items-center mb-4">
    <h2 class="m-0"><i class="fas fa-users-gear me-2"></i> Gestión de Usuarios</h2>
    <button class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#addUserModal">
        <i class="fas fa-user-plus me-2"></i> Nuevo Usuario
    </button>
</div>

<div class="card glass-card border-0 shadow-sm">
    <div class="card-body p-0">
        <div class="table-responsive">
            <table class="table table-hover align-middle mb-0">
                <thead style="background-color: var(--border-color); color: var(--text-main);">
                    <tr>
                        <th class="ps-4">Usuario</th>
                        <th>Nombre Completo</th>
                        <th>Último Acceso</th>
                        <th class="text-center pe-4">Acciones</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($usuarios as $u): ?>
                    <tr>
                        <td class="ps-4 fw-bold"><?= htmlspecialchars($u['username']) ?></td>
                        <td><?= htmlspecialchars($u['nombre_completo']) ?></td>
                        <td>
                            <small class="text-muted">
                                <?= $u['ultimo_acceso'] ? date('d/m/Y H:i', strtotime($u['ultimo_acceso'])) : 'Nunca' ?>
                            </small>
                        </td>
                        <td class="text-center pe-4">
                            <div class="btn-group">
                                <button class="btn btn-sm btn-outline-primary me-1" 
                                        data-bs-toggle="modal" 
                                        data-bs-target="#editUserModal<?= $u['id'] ?>">
                                    <i class="fas fa-edit"></i>
                                </button>
                                <?php if ($u['id'] != $_SESSION['user_id']): ?>
                                <button class="btn btn-sm btn-outline-danger" 
                                        onclick="swalConfirm('¿Eliminar usuario?', 'Esta acción no se puede deshacer.', 'warning', () => { window.location.href = 'delete_user.php?id=<?= $u['id'] ?>' })">
                                    <i class="fas fa-trash"></i>
                                </button>
                                <?php endif; ?>
                            </div>
                        </td>
                    </tr>

                    <!-- Edit User Modal -->
                    <div class="modal fade" id="editUserModal<?= $u['id'] ?>" tabindex="-1">
                        <div class="modal-dialog modal-dialog-centered">
                            <div class="modal-content border-0 shadow-lg rounded-4">
                                <div class="modal-header border-bottom-0 pb-0">
                                    <h5 class="modal-title fw-bold">Editar Usuario</h5>
                                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                                </div>
                                <form action="save_user.php" method="POST">
                                    <div class="modal-body p-4">
                                        <input type="hidden" name="id" value="<?= $u['id'] ?>">
                                        <div class="mb-3">
                                            <label class="form-label small fw-bold">Nombre Completo</label>
                                            <input type="text" name="nombre_completo" class="form-control" value="<?= htmlspecialchars($u['nombre_completo']) ?>" required>
                                        </div>
                                        <div class="mb-3">
                                            <label class="form-label small fw-bold">Usuario</label>
                                            <input type="text" name="username" class="form-control" value="<?= htmlspecialchars($u['username']) ?>" required>
                                        </div>
                                        <div class="mb-3">
                                            <label class="form-label small fw-bold">Nueva Contraseña (dejar en blanco para no cambiar)</label>
                                            <input type="password" name="password" class="form-control" autocomplete="new-password">
                                        </div>
                                    </div>
                                    <div class="modal-footer border-top-0 pt-0">
                                        <button type="button" class="btn btn-light" data-bs-dismiss="modal">Cancelar</button>
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

<!-- Add User Modal -->
<div class="modal fade" id="addUserModal" tabindex="-1">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content border-0 shadow-lg rounded-4">
            <div class="modal-header border-bottom-0 pb-0">
                <h5 class="modal-title fw-bold">Nuevo Usuario</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <form action="save_user.php" method="POST">
                <div class="modal-body p-4">
                    <div class="mb-3">
                        <label class="form-label small fw-bold">Nombre Completo</label>
                        <input type="text" name="nombre_completo" class="form-control" placeholder="Ejem: Juan Pérez" required>
                    </div>
                    <div class="mb-3">
                        <label class="form-label small fw-bold">Usuario</label>
                        <input type="text" name="username" class="form-control" placeholder="Ejem: jperez" required>
                    </div>
                    <div class="mb-3">
                        <label class="form-label small fw-bold">Contraseña</label>
                        <input type="password" name="password" class="form-control" required>
                    </div>
                </div>
                <div class="modal-footer border-top-0 pt-0">
                    <button type="button" class="btn btn-light" data-bs-dismiss="modal">Cancelar</button>
                    <button type="submit" class="btn btn-primary">Crear Usuario</button>
                </div>
            </form>
        </div>
    </div>
</div>

<script>
    document.getElementById('module-title').innerText = 'Gestión de Usuarios';
</script>

<?php include '../../includes/footer.php'; ?>
