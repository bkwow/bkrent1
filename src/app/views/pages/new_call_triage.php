<?php
global $customerModel;
?>
<main class="page-content p-4">
    <h1 class="page-title mb-4"><i class="fas fa-headset me-2 text-primary"></i>Centro de Operaciones de Llamadas</h1>
    <div id="triage-action-message" class="mb-3"></div>
    <div class="row">
        <div class="col-12 mb-4">
            <div class="card">
                <div class="card-header">
                    <h5 class="m-0"><i class="fas fa-phone-alt me-2"></i>1. Buscar Cliente</h5>
                </div>
                <div class="card-body">
                    <form id="triage-form" onsubmit="return false;">
                        <label for="customer-phone" class="form-label fw-bold">Teléfono del Cliente:</label>
                        <input
                            type="tel"
                            class="form-control form-control-lg"
                            id="customer-phone"
                            name="phone"
                            placeholder="Ingresar número y buscar..."
                            required
                        >
                        <input type="hidden" id="selected-client-id">
                    </form>
                    <div id="client-search-results"></div>

                    <!-- RENOMBRADO Y OCULTO POR DEFECTO -->
                    <div id="inquiry-history-results" class="mt-4" style="display: none;">
                        <h6 class="mb-3">Historial de Cotizaciones Recientes:</h6>
                        <div class="table-responsive">
                            <table
                                id="inquiry-history-table"
                                class="table table-sm table-hover"
                                style="width:100%;"
                            >
                                <thead>
                                    <tr>
                                        <th>Fecha Consulta</th>
                                        <th>Periodo Solicitado</th>
                                        <th>Notas</th>
                                        <th>Acción</th>
                                    </tr>
                                </thead>
                                <tbody></tbody>
                            </table>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <div class="col-12">
            <div class="card">
                <div class="card-header">
                    <h5 class="m-0"><i class="fas fa-file-alt me-2"></i>2. Registrar/Editar Consulta</h5>
                </div>
                <div class="card-body">
                    <form id="call-details-form">
                        <input type="hidden" id="selected-inquiry-id" name="inquiry_id">

                        <ul class="nav nav-pills mb-3" id="pills-tab" role="tablist">
                            <li class="nav-item" role="presentation">
                                <button
                                    class="nav-link active"
                                    data-bs-toggle="pill"
                                    data-bs-target="#pills-vehicle"
                                    type="button"
                                >Disponibilidad y Vehículo</button>
                            </li>
                            <li class="nav-item" role="presentation">
                                <button
                                    class="nav-link"
                                    data-bs-toggle="pill"
                                    data-bs-target="#pills-source"
                                    type="button"
                                >Fuente y Motivo</button>
                            </li>
                        </ul>

                        <div class="tab-content" id="pills-tabContent">
                            <!-- PESTAÑA VEHÍCULO -->
                            <div class="tab-pane fade show active" id="pills-vehicle" role="tabpanel">
                                <div class="row g-3 mb-3">
                                    <div class="col-md-6 col-lg-3">
                                        <label class="form-label">Desde:</label>
                                        <input
                                            type="datetime-local"
                                            class="form-control"
                                            id="pickup-date"
                                            name="start_date"
                                        >
                                    </div>
                                    <div class="col-md-6 col-lg-3">
                                        <label class="form-label">Hasta:</label>
                                        <input
                                            type="datetime-local"
                                            class="form-control"
                                            id="return-date"
                                            name="end_date"
                                        >
                                    </div>
                                    <div class="col-md-4 col-lg-2">
                                        <label class="form-label">Tipo:</label>
                                        <select class="form-select" id="vehicle-type" name="vehicle_type">
                                            <option value="Cualquiera" selected>Cualquiera</option>
                                            <option value="Sport Utility">SUV</option>
                                            <option value="Pickup">Pickup</option>
                                            <option value="Economy">Sedan</option>
                                            <option value="Jeep">Jeep</option>
                                            <option value="Microbus">Microbus</option>
                                        </select>
                                    </div>
                                    <div class="col-md-4 col-lg-2">
                                        <label class="form-label">Transmisión:</label>
                                        <select class="form-select" id="transmission-type" name="transmission">
                                            <option value="Cualquiera" selected>Cualquiera</option>
                                            <option value="Automatic">Automático</option>
                                            <option value="Standard">Standard</option>
                                        </select>
                                    </div>
                                    <div class="col-md-4 col-lg-2">
                                        <label class="form-label">Combustible:</label>
                                        <select class="form-select" id="fuel-type" name="fuel">
                                            <option value="Cualquiera" selected>Cualquiera</option>
                                            <option value="Gasoline">Gasolina</option>
                                            <option value="Diesel">Diesel</option>
                                        </select>
                                    </div>
                                </div>
                                <div class="d-grid">
                                    <button
                                        type="button"
                                        class="btn btn-info"
                                        id="check-availability-btn"
                                    ><i class="fas fa-search me-2"></i>Verificar Disponibilidad</button>
                                </div>
                                <div id="availability-results" class="mt-3">
                                    <div class="table-responsive">
                                        <table
                                            id="availability-table"
                                            class="table table-sm table-hover"
                                            style="width:100%"
                                        >
                                            <thead>
                                                <tr>
                                                    <th>Seleccionar</th>
                                                    <th>Vehículo</th>
                                                    <th>Precio x Día</th>
                                                    <th>Total Días (Cálculo)</th>
                                                    <th>Costo Total</th>
                                                    <th>Detalles</th>
                                                </tr>
                                            </thead>
                                            <tbody></tbody>
                                        </table>
                                    </div>
                                </div>
                            </div>

                            <!-- PESTAÑA FUENTE Y MOTIVO -->
                            <div class="tab-pane fade" id="pills-source" role="tabpanel">
                                <div class="mb-3">
                                    <label class="form-label fw-bold">Fuente del Contacto</label>
                                    <div>
                                        <div class="form-check form-check-inline">
                                            <input class="form-check-input source-checkbox" type="checkbox" id="source_new" name="source[]" value="Cliente Nuevo">
                                            <label class="form-check-label" for="source_new">Cliente Nuevo</label>
                                        </div>
                                        <div class="form-check form-check-inline">
                                            <input class="form-check-input source-checkbox" type="checkbox" id="source_freq" name="source[]" value="Cliente Frecuente">
                                            <label class="form-check-label" for="source_freq">Cliente Frecuente</label>
                                        </div>
                                        <div class="form-check form-check-inline">
                                            <input class="form-check-input source-checkbox" type="checkbox" id="source_fb" name="source[]" value="Facebook">
                                            <label class="form-check-label" for="source_fb">Facebook</label>
                                        </div>
                                        <div class="form-check form-check-inline">
                                            <input class="form-check-input source-checkbox" type="checkbox" id="source_whatsapp" name="source[]" value="WhatsApp">
                                            <label class="form-check-label" for="source_whatsapp">WhatsApp</label>
                                        </div>
                                        <div class="form-check form-check-inline">
                                            <input class="form-check-input source-checkbox" type="checkbox" id="source_ig" name="source[]" value="Instagram">
                                            <label class="form-check-label" for="source_ig">Instagram</label>
                                        </div>
                                        <div class="form-check form-check-inline">
                                            <input class="form-check-input source-checkbox" type="checkbox" id="source_web" name="source[]" value="Página Web">
                                            <label class="form-check-label" for="source_web">Página Web</label>
                                        </div>
                                        <div class="form-check form-check-inline">
                                            <input class="form-check-input source-checkbox" type="checkbox" id="source_google" name="source[]" value="Google">
                                            <label class="form-check-label" for="source_google">Google</label>
                                        </div>
                                        <div class="form-check form-check-inline">
                                            <input class="form-check-input source-checkbox" type="checkbox" id="source_yt" name="source[]" value="Youtuber">
                                            <label class="form-check-label" for="source_yt">Youtuber</label>
                                        </div>
                                        <div class="form-check form-check-inline">
                                            <input class="form-check-input source-checkbox" type="checkbox" id="source_tt" name="source[]" value="Tiktoker">
                                            <label class="form-check-label" for="source_tt">Tiktoker</label>
                                        </div>
                                        <div class="form-check form-check-inline">
                                            <input class="form-check-input source-checkbox" type="checkbox" id="source_ot" name="source[]" value="Otras Redes">
                                            <label class="form-check-label" for="source_ot">Otras Redes</label>
                                        </div>
                                        <div class="form-check form-check-inline">
                                            <input class="form-check-input source-checkbox" type="checkbox" id="source_ref" name="source[]" value="Referido">
                                            <label class="form-check-label" for="source_ref">Referido</label>
                                        </div>
                                    </div>
                                </div>
                                <div class="mb-3">
                                    <label for="observations" class="form-label fw-bold">Observaciones (Nota):</label>
                                    <textarea class="form-control" id="observations" name="observacion" rows="4" placeholder="Anotar detalles..."></textarea>
                                </div>
                            </div>
                        </div>

                        <hr>
                        <div class="d-flex justify-content-end">
                            <button type="button" class="btn btn-secondary me-2" id="save-inquiry-btn" disabled>
                                <i class="fas fa-save me-2"></i>Guardar/Actualizar Consulta
                            </button>
                            <button type="button" class="btn btn-success" id="proceed-to-booking-btn" disabled>
                                <i class="fas fa-arrow-right me-2"></i>Proceder a Reservar
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
</main>

<!-- Validación de selección de fuentes y motivos -->
<script>
    function updateSourceButtons() {
        const anyChecked = document.querySelectorAll('.source-checkbox:checked').length > 0;
        document.getElementById('save-inquiry-btn').disabled = !anyChecked;
        document.getElementById('proceed-to-booking-btn').disabled = !anyChecked;
    }
    document.querySelectorAll('.source-checkbox').forEach(el => {
        el.addEventListener('change', updateSourceButtons);
    });
</script>
