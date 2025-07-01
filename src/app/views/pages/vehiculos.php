<main class="page-content p-4">
    <h1 class="page-title mb-4"><i class="fas fa-car me-2 text-primary"></i>Gestión de Vehículos</h1>
    <div class="card">
        <div class="card-header d-flex justify-content-between align-items-center">
            <h5 class="m-0">Listado de Flota</h5>
            <button class="btn btn-primary" id="btn-nuevo-vehiculo"><i class="fas fa-plus me-2"></i>Registrar Vehículo</button>
        </div>
        <div class="card-body">
            <div class="table-responsive"><table id="vehicles-table" class="table table-hover dt-responsive nowrap" style="width:100%"><thead><tr><th>ID</th><th>Imagen</th><th>Placa</th><th>Marca</th><th>Modelo</th><th>Año</th><th>Clase</th><th>Transmisión</th><th>Combustible</th><th>Estado</th><th>Acciones</th></tr></thead></table></div>
        </div>
    </div>
</main>

<!-- Modal para Crear/Editar Vehículo -->
<div class="modal fade" id="vehicle-modal" tabindex="-1" aria-labelledby="vehicleModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-xl">
        <div class="modal-content">
            <div class="modal-header"><h5 class="modal-title" id="vehicleModalLabel">Registrar Nuevo Vehículo</h5><button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button></div>
            <div class="modal-body">
                <form id="vehicle-form" enctype="multipart/form-data" method="post" >
                    <input type="hidden" id="vehicle-id" name="id">
                    <input type="hidden" id="existing_picloc" name="existing_picloc">
                    <div class="row">
                        <div class="col-lg-8">
                            <div class="row">
                                <div class="col-md-8 mb-3"><label for="description" class="form-label">Descripción Principal</label><input type="text" class="form-control" id="description" name="description" required></div>
                                <div class="col-md-4 mb-3">
                                    <label for="avb" class="form-label">Estado del Vehículo</label>
                                    <!-- CORRECCIÓN: Se cambia el id y name a 'avb' -->
                                    <select id="avb" name="avb" class="form-select">
                                        <option value="0">Disponible</option>
                                        <option value="1">Mantenimiento</option>
                                        <option value="2">Accidente</option>
                                        <option value="3">Vendido</option>
                                        <option value="4">Depósito</option>
                                    </select>
                                </div>
                                <div class="col-md-4 mb-3"><label for="license" class="form-label">Placa</label><input type="text" class="form-control" id="license" name="license" required></div>
                                <div class="col-md-4 mb-3"><label for="make" class="form-label">Marca</label><input type="text" class="form-control" id="make" name="make"></div>
                                <div class="col-md-4 mb-3"><label for="model" class="form-label">Modelo</label><input type="text" class="form-control" id="model" name="model"></div>
                                <div class="col-md-4 mb-3"><label for="year" class="form-label">Año</label><input type="number" class="form-control" id="year" name="year"></div>
                                <div class="col-md-4 mb-3"><label for="class" class="form-label">Clase</label><input type="text" class="form-control" id="class" name="class"></div>
                                <div class="col-md-4 mb-3"><label for="color" class="form-label">Color</label><input type="text" class="form-control" id="color" name="color"></div>
                                <div class="col-md-4 mb-3"><label for="trans" class="form-label">Transmisión</label><input type="text" class="form-control" id="trans" name="trans"></div>
                                <div class="col-md-4 mb-3"><label for="fueltype" class="form-label">Combustible</label><input type="text" class="form-control" id="fueltype" name="fueltype"></div>
                                <div class="col-md-4 mb-3"><label for="engine" class="form-label">Motor</label><input type="text" class="form-control" id="engine" name="engine"></div>
                                <div class="col-md-8 mb-3"><label for="vin" class="form-label">VIN / Chasis</label><input type="text" class="form-control" id="vin" name="vin"></div>
                                <div class="col-md-3 mb-3"><label for="precio" class="form-label">Precio/Día</label><input type="number" step="0.01" class="form-control" id="precio" name="precio"></div>
                                <div class="col-md-3 mb-3"><label for="capacidad" class="form-label">Capacidad</label><input type="number" class="form-control" id="capacidad" name="capacidad"></div>
                            </div>
                        </div>
                        <div class="col-lg-4">
                            <label class="form-label">Imagen del Vehículo</label>
                            <img id="image-preview" src="public/img/placeholder-image.jpg" class="img-fluid rounded border mb-2" alt="Vista previa">
                            <input type="file" class="form-control" id="picloc_file" name="picloc_file" accept="image/*">
                        </div>
                    </div>
                </form>
            </div>
            <div class="modal-footer"><button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cerrar</button><button type="submit" class="btn btn-primary" form="vehicle-form">Guardar Vehículo</button></div>
        </div>
    </div>
</div>