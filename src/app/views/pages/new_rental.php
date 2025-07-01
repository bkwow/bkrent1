<?php
// En src/app/views/pages/new_rental.php
global $customerModel;

// ==========================================================
// CORRECCIÓN: Leemos los datos desde $_POST en lugar de $_GET
// ==========================================================
$client_id = $_POST['client_id'] ?? null;
$new_phone = $_POST['new_phone'] ?? null;
$vehicle_id = $_POST['vehicle_id'] ?? null;
$vehicle_desc = $_POST['vehicle_desc'] ?? 'No seleccionado';
$start_date = $_POST['start_date'] ?? null;
$end_date = $_POST['end_date'] ?? null;
$total_days = $_POST['total_days'] ?? 'N/A';
$total_cost = $_POST['total_cost'] ?? '0.00';

$customer_data = null;
if ($client_id) {
    $customer_data = $customerModel->getCustomerById((int)$client_id);
}

$cars = null;
if($vehicle_id){
    $cars = '';
}

?>


<main class="page-content p-4">
    <div class="container-fluid">
        <div class="row" style="background-color: #ffffff;">
            <div class="col-12">
                <h1 class="page-title mb-4"><i class="fas fa-car me-2 text-primary"></i>Formulario de Nueva Reserva</h1>
                
                <?php if ($customer_data): ?>
                    <div class="alert alert-success">Continuando con el cliente existente: <strong><?php echo htmlspecialchars($customer_data['drfirstname'] . ' ' . $customer_data['drlastname']); ?></strong></div>
                <?php elseif ($new_phone): ?>
                    <div class="alert alert-info">Creando reserva para un nuevo cliente. Teléfono: <strong><?php echo htmlspecialchars($new_phone); ?></strong></div>
                <?php endif; ?>

                <?php
// new_rental.php

// Asume que llegas con esto desde new_call_triage:
// $client_id, $vehicle_id, $vehicle_desc, $start_date, $end_date, $total_days, $total_cost
// Y que has cargado $customer = CustomerModel::getCustomerById($client_id);

// También puedes precargar permisos y detalles si existen:
$existingPerm = [];    // tbl_control_permisos para esta renta, o []


//PROCEDER A CARGAR DATOS 
$rentalCalc   = [];    // rentalcalc para esta renta, o []
?>
<form id="new-rental-form" method="POST" action="index.php?page=save_rental">
  <input type="hidden" name="client_id"    value="<?= htmlspecialchars($client_id) ?>">
  <input type="hidden" name="vehicle_id"   value="<?= htmlspecialchars($vehicle_id) ?>">
  <input type="hidden" name="vehicle_desc" value="<?= htmlspecialchars($vehicle_desc) ?>">
  <input type="hidden" name="start_date"   value="<?= htmlspecialchars($start_date) ?>">
  <input type="hidden" name="end_date"     value="<?= htmlspecialchars($end_date) ?>">
  <input type="hidden" name="total_days"   value="<?= htmlspecialchars($total_days) ?>">
  <input type="hidden" name="total_cost"   value="<?= htmlspecialchars($total_cost) ?>">

  <!-- Nav tabs -->
  <ul class="nav nav-tabs mb-3" role="tablist">
    <li class="nav-item" role="presentation">
      <button class="nav-link active" data-bs-toggle="tab" data-bs-target="#tab-personales" type="button">1. Datos Personales </button>
    </li>
    <li class="nav-item" role="presentation">
      <button class="nav-link" data-bs-toggle="tab" data-bs-target="#tab-detalle" type="button">2. Detalle de Reserva</button>
    </li>
    <li class="nav-item" role="presentation">
      <button class="nav-link" data-bs-toggle="tab" data-bs-target="#tab-conductores" type="button">3. Conductores Adicionales</button>
    </li>
    <li class="nav-item" role="presentation">
      <button class="nav-link" data-bs-toggle="tab" data-bs-target="#tab-permisos" type="button">4. Solicitud de Permisos</button>
    </li>
  </ul>

  <!-- Tab panes -->
  <div class="tab-content">
    <!-- 1. Datos Personales -->
    <div class="tab-pane fade show active" id="tab-personales" role="tabpanel">
      <div class="row g-3">
        <div class="col-md-6">
          <label class="form-label">Nombres</label>
          <input type="text" name="pafirstname" value="<?= htmlspecialchars($customer_data['pafirstname'] ?? '') ?>" class="form-control">
        </div>
        <div class="col-md-6">
          <label class="form-label">Apellidos</label>
          <input type="text" name="palastname" value="<?= htmlspecialchars($customer_data['palastname'] ?? '') ?>" class="form-control">
        </div>
        <div class="col-md-12">
          <label class="form-label">Dirección</label>
          <textarea name="draddress" class="form-control"><?= htmlspecialchars($customer_data['draddress'] ?? '') ?></textarea>
        </div>
        <div class="col-md-4">
          <label class="form-label">Ciudad</label>
          <input type="text" name="drcity" value="<?= htmlspecialchars($customer_data['drcity'] ?? '') ?>" class="form-control">
        </div>
        <div class="col-md-4">
          <label class="form-label">Departamento</label>
          <input type="text" name="drstate" value="<?= htmlspecialchars($customer_data['drstate'] ?? '') ?>" class="form-control">
        </div>
        <div class="col-md-4">
          <label class="form-label">País</label>
          <input type="text" name="drcountry" value="<?= htmlspecialchars($customer_data['drcountry'] ?? '') ?>" class="form-control">
        </div>
        <div class="col-md-4">
          <label class="form-label">Teléfono 1</label>
          <input type="text" name="drhphone" value="<?= htmlspecialchars($customer_data['drhphone'] ?? '') ?>" class="form-control">
        </div>
        <div class="col-md-4">
          <label class="form-label">Teléfono 2</label>
          <input type="text" name="drlphone" value="<?= htmlspecialchars($customer_data['drlphone'] ?? '') ?>" class="form-control">
        </div>
        <div class="col-md-4">
          <label class="form-label">Teléfono 3</label>
          <input type="text" name="workphone" value="<?= htmlspecialchars($customer_data['workphone'] ?? '') ?>" class="form-control">
        </div>
        <div class="col-md-4">
          <label class="form-label">Código Postal</label>
          <input type="text" name="drzip" value="<?= htmlspecialchars($customer_data['drzip'] ?? '') ?>" class="form-control">
        </div>
        <div class="col-md-8">
          <label class="form-label">Email</label>
          <input type="email" name="DREMAIL" value="<?= htmlspecialchars($customer_data['DREMAIL'] ?? '') ?>" class="form-control">
        </div>
        <div class="col-md-6">
          <label class="form-label">No. Pasaporte</label>
          <input type="text" name="drpasportid" value="<?= htmlspecialchars($customer_data['drpasportid'] ?? '') ?>" class="form-control">
        </div>

        <!-- Datos de Licencia -->
        <div class="col-md-4">
          <label class="form-label">Fecha Nacimiento</label>
          <input type="date" name="drbdate" value="<?= substr($customer_data['drbdate'] ?? '',0,10) ?>" class="form-control">
        </div>
        <div class="col-md-4">
          <label class="form-label">Licencia No.</label>
          <input type="text" name="drlicnr" value="<?= htmlspecialchars($customer_data['drlicnr'] ?? '') ?>" class="form-control">
        </div>
        <div class="col-md-4">
          <label class="form-label">Vence Licencia</label>
          <input type="date" name="drlexpdate" value="<?= substr($customer_data['drlexpdate'] ?? '',0,10) ?>" class="form-control">
        </div>
        <div class="col-md-4">
          <label class="form-label">Categoría Licencia</label>
          <input type="text" name="drliccat" value="<?= htmlspecialchars($customer_data['drliccat'] ?? '') ?>" class="form-control">
        </div>
      </div>
    </div>

    <!-- 2. Detalle de Reserva -->
    <div class="tab-pane fade" id="tab-detalle" role="tabpanel">
      <div class="row g-3">
        <div class="col-md-4">
          <label class="form-label">Total Días</label>
          <input type="text" name="total_days_display" value="<?= htmlspecialchars($total_days) ?>"  class="form-control">
        </div>
        <div class="col-md-4">
          <label class="form-label">Precio x Día</label>
          <input type="text" name="price_per_day" value="<?= htmlspecialchars(number_format($total_cost/$total_days,2)) ?>"  class="form-control">
        </div>
        <div class="col-md-4">
          <label class="form-label">Total Renta</label>
          <input type="text" name="total_rent" value="<?= htmlspecialchars(number_format($total_cost,2)) ?>"  class="form-control">
        </div>
        <div class="col-md-6">
          <label class="form-label">Depósito Reembolsable</label>
          <input type="number" step="0.01" name="deposit_reemb" value="<?= htmlspecialchars($rentalCalc['fixedamount'] ?? '') ?>" class="form-control">
        </div>
        <div class="col-md-6">
          <label class="form-label">Comentarios Adicionales</label>
          <textarea name="det_add" class="form-control"><?= htmlspecialchars($existingPerm['det_add'] ?? '') ?></textarea>
        </div>
      </div>
    </div>

    <!-- 3. Conductores Adicionales -->
    <div class="tab-pane fade" id="tab-conductores" role="tabpanel">
      <div class="row g-3">
        <div class="col-md-6">
          <label class="form-label">Nombre Conductor 2</label>
          <input type="text" name="dr2fname" value="<?= htmlspecialchars($customer_data['dr2fname'] ?? '') ?>" class="form-control">
        </div>
        <div class="col-md-6">
          <label class="form-label">Apellido Conductor 2</label>
          <input type="text" name="dr2lastname" value="<?= htmlspecialchars($customer_data['dr2lastname'] ?? '') ?>" class="form-control">
        </div>
        <div class="col-md-6">
          <label class="form-label">No. Licencia 2</label>
          <input type="text" name="dr2licnr" value="<?= htmlspecialchars($customer_data['dr2licnr'] ?? '') ?>" class="form-control">
        </div>
        <div class="col-md-6">
          <label class="form-label">Vence Licencia 2</label>
          <input type="date" name="dr2licexpdate" value="<?= substr($customer_data['dr2licexpdate'] ?? '',0,10) ?>" class="form-control">
        </div>
      </div>
    </div>

    <!-- 4. Solicitud de Permisos -->
    <div class="tab-pane fade" id="tab-permisos" role="tabpanel">
      <div class="row g-3">
        <div class="col-md-4">
          <label class="form-label">Fecha Entrega Permiso</label>
          <input type="date" name="fecha0p" value="<?= htmlspecialchars($existingPerm['fecha0p'] ?? '') ?>" class="form-control">
        </div>
        <div class="col-md-4">
          <label class="form-label">Fecha Salida Permiso</label>
          <input type="date" name="fecha1p" value="<?= htmlspecialchars($existingPerm['fecha1p'] ?? '') ?>" class="form-control">
        </div>
        <div class="col-md-4">
          <label class="form-label">Fecha Retorno Permiso</label>
          <input type="date" name="fecha2p" value="<?= htmlspecialchars($existingPerm['fecha2p'] ?? '') ?>" class="form-control">
        </div>
        <div class="col-md-4">
          <label class="form-label">Total Días Permiso</label>
          <input type="number" name="tdiasp" value="<?= htmlspecialchars($existingPerm['tdiasp'] ?? '') ?>" class="form-control">
        </div>
        <div class="col-md-8">
          <label class="form-label">Destino</label>
          <input type="text" name="destino" value="<?= htmlspecialchars($existingPerm['destino'] ?? '') ?>" class="form-control">
        </div>
        <div class="col-md-4">
          <label class="form-label">Costo Permiso</label>
          <input type="number" step="0.01" name="pptotal" value="<?= htmlspecialchars($existingPerm['pptotal'] ?? '') ?>" class="form-control">
        </div>
      </div>
    </div>
  </div>

  <div class="mt-4 text-end">
    <button type="submit" class="btn btn-primary">Guardar Reserva</button>
    <a href="index.php?page=reservations" class="btn btn-secondary">Cancelar</a>
  </div>
</form>

                <div id="form-rental-result" class="mt-4"><?php  //var_dump($customer_data); ?>
                    <?php var_dump($_POST);?> <?php var_dump($cars);?>
                </div>
            </div>
        </div>
    </div>
</main>