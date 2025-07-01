<?php
// Este archivo se carga a través de index.php, por lo que no necesita header/footer aquí.
global $vehicleModel; // Usamos el modelo global creado en index.php

$vehicle_id = $_GET['id'] ?? null;
$vehicle_data = null;
if ($vehicle_id) {
    $vehicle_data = $vehicleModel->getVehicleById((int)$vehicle_id);
}
?>
<main class="page-content p-4">
    <?php if ($vehicle_data): ?>
        <h1 class="page-title mb-4">
            <i class="fas fa-history me-2 text-primary"></i>Historial del Vehículo: <?= htmlspecialchars($vehicle_data['Make'] . ' ' . $vehicle_data['Model']) ?>
        </h1>

        <!-- Ficha del Vehículo -->
        <div class="card mb-4">
            <div class="card-header"><h5 class="m-0">Ficha Técnica</h5></div>
            <div class="card-body">
                <div class="row">
                    <div class="col-md-3">
                        <img src="public/vehiculos_flota/thumb/<?= htmlspecialchars($vehicle_data['picloc'] ?? 'noimage.jpg') ?>" class="img-fluid rounded border">
                    </div>
                    <div class="col-md-9">
                        <div class="row">
                            <div class="col-md-4"><strong>Placa:</strong> <?= htmlspecialchars($vehicle_data['License']) ?></div>
                            <div class="col-md-4"><strong>Marca:</strong> <?= htmlspecialchars($vehicle_data['Make']) ?></div>
                            <div class="col-md-4"><strong>Modelo:</strong> <?= htmlspecialchars($vehicle_data['Model']) ?></div>
                            <div class="col-md-4"><strong>Año:</strong> <?= htmlspecialchars($vehicle_data['year']) ?></div>
                            <div class="col-md-4"><strong>Clase:</strong> <?= htmlspecialchars($vehicle_data['class']) ?></div>
                            <div class="col-md-4"><strong>VIN:</strong> <?= htmlspecialchars($vehicle_data['vin']) ?></div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Historial de Rentas -->
        <div class="card">
            <div class="card-header"><h5 class="m-0">Historial de Rentas</h5></div>
            <div class="card-body">
                <div class="table-responsive">
                    <table id="rental-history-table" class="table table-hover" style="width:100%">
                        <thead>
                            <tr>
                                <th>Resv #</th>
                                <th>Cliente</th>
                                <th>Teléfono</th>
                                <th>Desde</th>
                                <th>Hasta</th>
                                <th>Días</th>
                                <th>Total Renta</th>
                            </tr>
                        </thead>
                    </table>
                </div>
            </div>
        </div>

    <?php else: ?>
        <div class="alert alert-danger">No se encontró el vehículo especificado.</div>
    <?php endif; ?>
</main>
