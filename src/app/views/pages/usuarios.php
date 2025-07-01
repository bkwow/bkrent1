<main class="page-content p-4">
    <h1 class="page-title mb-4"><i class="fas fa-users-cog me-2 text-primary"></i>Gestión de Usuarios</h1>
    <div class="card">
        <div class="card-header d-flex justify-content-between align-items-center"><h5 class="m-0">Listado de Personal</h5><button class="btn btn-primary" id="btn-nuevo-usuario"><i class="fas fa-user-plus me-2"></i>Registrar Usuario</button></div>
        <div class="card-body"><div class="table-responsive"><table id="users-table" class="table table-hover dt-responsive nowrap" style="width:100%"><thead><tr><th>ID</th><th>Nombre Completo</th><th>Usuario</th><th>Email</th><th>Rol</th><th>Estado</th><th>Acciones</th></tr></thead></table></div></div>
    </div>
</main>
<div class="modal fade" id="user-modal" tabindex="-1"><div class="modal-dialog modal-lg"><div class="modal-content">
    <div class="modal-header"><h5 class="modal-title" id="userModalLabel">Registrar Usuario</h5><button type="button" class="btn-close" data-bs-dismiss="modal"></button></div>
    <div class="modal-body">
        <form id="user-form">
            <input type="hidden" id="user-id" name="id">
            <div class="row">
                <div class="col-md-6 mb-3"><label for="nombre" class="form-label">Nombre Completo</label><input type="text" class="form-control" id="nombre" name="nombre" required></div>
                <div class="col-md-6 mb-3"><label for="email" class="form-label">Correo Electrónico</label><input type="email" class="form-control" id="email" name="email" required></div>
            </div>
            <div class="row">
                <div class="col-md-6 mb-3"><label for="username" class="form-label">Nombre de Usuario</label><input type="text" class="form-control" id="username" name="username" required></div>
                <div class="col-md-6 mb-3"><label for="role" class="form-label">Rol</label><select id="role" name="role" class="form-select"><option value="operator">Operador</option><option value="admin">Administrador</option><option value="super_admin">Super Administrador</option></select></div>
            </div>
            <div class="row">
                <div class="col-md-6 mb-3"><label for="password" class="form-label">Contraseña</label><input type="password" class="form-control" id="password" name="password"><div class="form-text">Dejar en blanco para no cambiarla.</div></div>
                <div class="col-md-6 mb-3"><label for="activated" class="form-label">Estado</label><select id="activated" name="activated" class="form-select"><option value="1">Activo</option><option value="0">Inactivo</option></select></div>
            </div>
        </form>
    </div>
    <div class="modal-footer"><button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cerrar</button><button type="submit" class="btn btn-primary" form="user-form">Guardar</button></div>
</div></div></div>