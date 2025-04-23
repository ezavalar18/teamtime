<div class="container mt-4">
    <h2 class="mb-4">Editar Marcaciones</h2>

    <div class="table-responsive">
        <table class="table table-bordered table-hover align-middle">
            <thead class="table-primary text-center">
                <tr>
                    <th>ID</th>
                    <th>Empleado</th>
                    <th>Fecha</th>
                    <th>Hora Entrada</th>
                    <th>Hora Salida</th>
                    <th>Acciones</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($asistencias as $asistencia): ?>
                    <tr>
                        <td><?= $asistencia['id'] ?></td>
                        <td><?= htmlspecialchars($asistencia['nombre_empleado']) ?></td>
                        <td><?= $asistencia['fecha'] ?></td>
                        <td><?= $asistencia['hora_entrada'] ?></td>
                        <td><?= $asistencia['hora_salida'] ?></td>
                        <td class="text-center">
                            <button class="btn btn-warning btn-sm" data-bs-toggle="modal" data-bs-target="#editarAsistenciaModal<?= $asistencia['id'] ?>">
                                <i class="fas fa-edit"></i>
                            </button>
                        </td>
                    </tr>

                    <!-- Modal editar asistencia -->
                    <div class="modal fade" id="editarAsistenciaModal<?= $asistencia['id'] ?>" tabindex="-1" aria-labelledby="editarAsistenciaLabel<?= $asistencia['id'] ?>" aria-hidden="true">
                        <div class="modal-dialog modal-dialog-centered">
                            <div class="modal-content">
                                <form action="/admin/actualizar_asistencia" method="POST">
                                    <div class="modal-header bg-primary text-white">
                                        <h5 class="modal-title">Editar Asistencia</h5>
                                        <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                                    </div>
                                    <div class="modal-body">
                                        <input type="hidden" name="id" value="<?= $asistencia['id'] ?>">
                                        <div class="mb-3">
                                            <label class="form-label">Fecha</label>
                                            <input type="date" class="form-control" name="fecha" value="<?= $asistencia['fecha'] ?>" required>
                                        </div>
                                        <div class="mb-3">
                                            <label class="form-label">Hora de Entrada</label>
                                            <input type="time" class="form-control" name="hora_entrada" value="<?= $asistencia['hora_entrada'] ?>" required>
                                        </div>
                                        <div class="mb-3">
                                            <label class="form-label">Hora de Salida</label>
                                            <input type="time" class="form-control" name="hora_salida" value="<?= $asistencia['hora_salida'] ?>" required>
                                        </div>
                                    </div>
                                    <div class="modal-footer">
                                        <button type="submit" class="btn btn-success">Guardar Cambios</button>
                                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancelar</button>
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

