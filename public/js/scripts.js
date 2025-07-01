//================================================================================================
// ARCHIVO COMPLETO Y DEFINITIVO: public/js/scripts.js
// Versión final verificada que incluye toda la funcionalidad sin omisiones.
//================================================================================================

$(document).ready(function() {

    // --- LÓGICA GENERAL DEL SIDEBAR (Menú Lateral) ---
  // --- LÓGICA GENERAL DEL SIDEBAR (Funcional) ---
    const sidebarToggle = document.getElementById('sidebarToggle');
    if (sidebarToggle) {
        const sidebar = document.getElementById('sidebar');
        const applySidebarState = () => {
            const isSmallScreen = window.innerWidth < 992;
            if (isSmallScreen) {
                document.body.classList.remove('sidebar-mobile-visible');
                document.body.classList.add('sidebar-toggled-mobile');
            } else {
                document.body.classList.remove('sidebar-toggled-mobile');
                const savedState = localStorage.getItem('sidebarState');
                document.body.classList.toggle('sidebar-toggled', savedState === 'collapsed');
            }
        };
        sidebarToggle.addEventListener('click', function(event) {
            event.preventDefault();
            if (window.innerWidth < 992) {
                document.body.classList.toggle('sidebar-mobile-visible');
            } else {
                document.body.classList.toggle('sidebar-toggled');
                localStorage.setItem('sidebarState', document.body.classList.contains('sidebar-toggled') ? 'collapsed' : 'visible');
            }
        });
        document.addEventListener('click', function(event) {
            if (window.innerWidth < 992 && document.body.classList.contains('sidebar-mobile-visible')) {
                if (!sidebar.contains(event.target) && !sidebarToggle.contains(event.target)) {
                    document.body.classList.remove('sidebar-mobile-visible');
                }
            }
        });
        applySidebarState();
        window.addEventListener('resize', applySidebarState);
    }

    // --- LÓGICA PARA LA PÁGINA DE RESERVAS (reservas.php) ---
    if ($('#reservas-tabs').length) {
        const initializeDataTable = (tableId) => {
            if (!tableId || $.fn.DataTable.isDataTable('#' + tableId)) { return; }
            const filterType = tableId.replace('table-reservas-', '').replace(/-/g, '_');
            $('#' + tableId).DataTable({
                responsive: true,
                processing: true,
                serverSide: true,
                ajax: { url: "ajax_handler.php", type: "POST", data: d => { d.action = 'get_rentals'; d.filter_type = filterType; } },
                columns: [
                    { className: 'dtr-control', orderable: false, data: null, defaultContent: '' },
                    { data: "confnr" }, { data: "car_image", orderable: false },
                    { data: "invoice" }, { data: "car_plate" }, { data: "car_make" },
                    { data: "car_model" }, { data: "car_year" },
                    { data: "pafirstname" }, { data: "palastname" },
                    { data: "drhphone" }, { data: "start_date" },
                    { data: "end_date" }, { data: "total" },
                    { data: "actions", orderable: false }
                ],
                order: [[1, 'desc']],
                language: { url: "https://cdn.datatables.net/plug-ins/1.13.6/i18n/es-ES.json" }
            });
        };
        $('button[data-bs-toggle="tab"]').on('shown.bs.tab', (event) => {
            const targetPaneId = $(event.target).data('bs-target');
            $(targetPaneId).find('table.dt-responsive').each((i, table) => initializeDataTable(table.id));
            $.fn.dataTable.tables({ visible: true, api: true }).columns.adjust().responsive.recalc();
        });
        const activeTab = document.querySelector('#reservas-tabs .nav-link.active');
        if (activeTab) {
            const targetPaneId = activeTab.getAttribute('data-bs-target');
            $(targetPaneId).find('table.dt-responsive').each((i, table) => initializeDataTable(table.id));
        }
    }

    // --- LÓGICA COMPLETA Y VERIFICADA PARA new_call_triage.php ---
    if ($('#triage-form').length) {

        let debounceTimer;
        let currentSearchRequest = null;
        const phoneInput = $('#customer-phone');
        const searchResultsDiv = $('#client-search-results');
        const inquiryHistoryContainer = $('#inquiry-history-results');

        const clientIdInput = $('#selected-client-id');
        const inquiryIdInput = $('#selected-inquiry-id');
        const proceedBtn = $('#proceed-to-booking-btn');
        const sourceCheckboxes = $('.source-checkbox');         // <-- DECLARAMOS aquí

        let availabilityTable, inquiryHistoryTable;

        const updateProceedButtonState = () => {
            const customerIdentified = clientIdInput.val() || phoneInput.val().trim().length > 5;
            const vehicleSelected = $('#availability-table tbody input[type="radio"]:checked').length > 0;
            const sourceSelected = $('.source-checkbox:checked').length > 0;
           /*proceedBtn.prop('disabled', !(customerIdentified && vehicleSelected && sourceSelected));*/
            // El botón "Proceder" necesita que se cumplan las 3 condiciones.
             proceedBtn.prop('disabled', !(customerIdentified && vehicleSelected && sourceSelected));
            //$('#save-inquiry-btn').prop('disabled', !customerIdentified);
 
        
        };
 
        phoneInput.on('keyup', function() {
            clearTimeout(debounceTimer);
            if (currentSearchRequest) { currentSearchRequest.abort(); }

            debounceTimer = setTimeout(() => {
                const phoneNumber = phoneInput.val();
                clientIdInput.val('');
                inquiryIdInput.val('');
                updateProceedButtonState();

                if (phoneNumber.length === 0) {
                    searchResultsDiv.html('');
                    inquiryHistoryContainer.hide();
                    if ($.fn.DataTable.isDataTable('#inquiry-history-table')) { inquiryHistoryTable.clear().draw(); }
                    return;
                }

                if (phoneNumber.length > 5) {
                    searchResultsDiv.html('<div class="text-center p-3"><div class="spinner-border spinner-border-sm"></div></div>');
                    inquiryHistoryContainer.hide();

                    currentSearchRequest = $.post('ajax_handler.php',
                        { action: 'search_customer_by_phone', phone: phoneNumber },
                        function(data) {
                            let clientHtml = '<div class="card mt-3"><div class="card-body">';
                            if (data.customer_found) {
                                clientHtml += `<h5 class="card-title text-success"><i class="fas fa-check-circle me-2"></i>Cliente Encontrado</h5>` +
                                              `<p class="card-text mb-1"><strong>${data.customer_data.drfirstname} ${data.customer_data.drlastname}</strong></p>` +
                                              `<p class="card-text text-muted small">Llamadas hoy: <strong>${data.calls_today}</strong> | Historial: <strong>${data.rental_history_count}</strong> reservas</p>`;
                                clientIdInput.val(data.customer_data.Client);
                            } else {
                                clientHtml += `<h5 class="card-title text-warning"><i class="fas fa-user-plus me-2"></i>Cliente Nuevo</h5>` +
                                              `<p class="card-text text-muted small">Llamadas hoy: <strong>${data.calls_today}</strong></p>`;
                            }
                            clientHtml += '</div></div>';
                            searchResultsDiv.html(clientHtml);

                            if (data.inquiries && data.inquiries.length > 0) {
                                if ($.fn.DataTable.isDataTable('#inquiry-history-table')) { inquiryHistoryTable.destroy(); }
                                $('#inquiry-history-table tbody').empty();

                                inquiryHistoryTable = $('#inquiry-history-table').DataTable({
                                    data: data.inquiries,
                                    columns: [
                                        { data: 'inquiry_date', render: d => new Date(d).toLocaleDateString() },
                                        { data: 'requested_start_date', render: (d, type, row) => `De ${new Date(row.requested_start_date).toLocaleDateString()} a ${new Date(row.requested_end_date).toLocaleDateString()}` },
                                        { data: 'notes' },
                                        { data: null, defaultContent: '<button class="btn btn-sm btn-outline-primary select-inquiry-btn"><i class="fas fa-check"></i> Cargar</button>', orderable: false }
                                    ],
                                    searching: false, paging: false, info: false, destroy: true,
                                    language: { "emptyTable": "No hay cotizaciones recientes para este cliente." }
                                });
                                inquiryHistoryContainer.show();
                            } else {
                                inquiryHistoryContainer.hide();
                            }
                            updateProceedButtonState();
                        }, 'json'
                    ).fail((jqXHR, textStatus) => {
                        if (textStatus !== 'abort') { searchResultsDiv.html('<div class="alert alert-danger">Error.</div>'); }
                    });
                }
            }, 500);
        });

        // **AHÍ** va el bloque de limpieza/preselección de checkboxes
        $('#inquiry-history-table tbody').on('click', '.select-inquiry-btn', function(e) {
            e.stopPropagation();
            const rowData = inquiryHistoryTable.row($(this).parents('tr')).data();
            inquiryIdInput.val(rowData.id);
            $('#pickup-date').val(rowData.requested_start_date ? rowData.requested_start_date.replace(' ', 'T') : '');
            $('#return-date').val(rowData.requested_end_date ? rowData.requested_end_date.replace(' ', 'T') : '');
            $('#vehicle-type').val(rowData.vehicle_type || 'Cualquiera');
            $('#transmission-type').val(rowData.transmission || 'Cualquiera');
            $('#fuel-type').val(rowData.fuel || 'Cualquiera');
            $('#observations').val(rowData.notes || '');
            $('#pills-vehicle-tab').tab('show');
            if ($.fn.DataTable.isDataTable('#availability-table')) { availabilityTable.clear().draw(); }

            // **LIMPIAR Y MARCAR** las fuentes guardadas
            sourceCheckboxes.prop('checked', false);
            if (rowData.sources && Array.isArray(rowData.sources)) {
                rowData.sources.forEach(src => {
                    sourceCheckboxes.filter(`[value="${src}"]`).prop('checked', true);
                });
            }

            Swal.fire({ icon: 'info', title: 'Cotización Cargada', text: 'Se han rellenado los campos.', showConfirmButton: false, timer: 1500 });
        });

 // Añadir un listener a los checkboxes para que actualicen el estado del botón
    $('.source-checkbox, #availability-table tbody').on('change', 'input[type="radio"]', updateProceedButtonState);
    phoneInput.on('keyup', updateProceedButtonState);


        function calculateRentalDuration(start, end) {
            const startDate = new Date(start); const endDate = new Date(end);
            if (isNaN(startDate) || isNaN(endDate) || endDate <= startDate) return { daysBilled: 0, summary: 'N/A' };
            const diffMs = endDate - startDate;
            const totalHours = diffMs / 36e5; const fullDays = Math.floor(totalHours / 24);
            const remainingHours = Math.round((totalHours % 24) * 10) / 10;
            let extraDay = 0; let summary = `<strong>${fullDays}</strong> día(s)`;
            if (remainingHours > 0.1) {
                summary += ` + <strong>${remainingHours.toFixed(1)}</strong> hr(s)`;
                if (remainingHours > 4) { extraDay = 1; summary += ' <span class="badge bg-warning text-dark">(+1 día)</span>'; }
            }
            const finalDays = fullDays + extraDay;
            summary += `<br>Total a facturar: <strong class="text-primary">${finalDays} día(s)</strong>`;
            return { daysBilled: finalDays, summary: summary };
        }

        $('#check-availability-btn').on('click', function() {
            inquiryIdInput.val('');
            const startDate = $('#pickup-date').val(); const endDate = $('#return-date').val();
            if (!startDate || !endDate) { Swal.fire('Atención', 'Por favor, seleccione una fecha de inicio y una de fin.', 'warning'); return; }
            if (new Date(endDate) <= new Date(startDate)) { Swal.fire('Atención', 'La fecha de fin debe ser posterior a la fecha de inicio.', 'warning'); return; }
            if ($.fn.DataTable.isDataTable('#availability-table')) { availabilityTable.destroy(); }
            $('#availability-table tbody').empty().html('<tr><td colspan="6" class="text-center"><div class="spinner-border text-info"></div><p>Buscando...</p></td></tr>');
            proceedBtn.prop('disabled', true);
            $.post('ajax_handler.php', {
                action: 'check_availability_with_filters', start_date: startDate, end_date: endDate,
                vehicle_type: $('#vehicle-type').val(), transmission: $('#transmission-type').val(), fuel: $('#fuel-type').val()
            }, function(response) {
                $('#availability-table tbody').empty();
                if (response.success && Array.isArray(response.data) && response.data.length > 0) {
                    const tableData = response.data.map(car => {
                        const duration = calculateRentalDuration(startDate, endDate);
                        const totalCost = duration.daysBilled * (parseFloat(car.precio_dia) || 0);
                        return { 
                            selector: `<div class="form-check d-flex justify-content-center"><input type="radio" name="selected_vehicle" class="form-check-input" value="${car.id}" data-total-days="${duration.daysBilled}" data-total-cost="${totalCost.toFixed(2)}" data-vehicle-desc="${car.marca} ${car.modelo} (${car.anio})"></div>`,
                            vehiculo: `<img src="public/vehiculos_flota/thumb/${car.car_image || 'noimage.jpg'}" class="rounded me-2" style="width:60px;height:45px;object-fit:cover;"> <strong>${car.marca} ${car.modelo} (${car.anio})</strong>`,
                            precio_dia: `$${(parseFloat(car.precio_dia) || 0).toFixed(2)}`,
                            total_dias: duration.summary, 
                            costo_total: `<strong class="text-success" style="font-size: 1.1em;">$${totalCost.toFixed(2)}</strong>`,
                            detalles: `Cap: ${car.capacidad||'N/A'}p, Trans: ${car.trans}, Comb: ${car.tipo_combustible}`
                        };
                    });
                    availabilityTable = $('#availability-table').DataTable({
                        data: tableData,
                        columns: [ { data: 'selector' }, { data: 'vehiculo' }, { data: 'precio_dia' }, { data: 'total_dias' }, { data: 'costo_total' }, { data: 'detalles' } ],
                        columnDefs: [ { "className": "d-none", "targets": 0 }, { "width": "30%", "targets": 1 }, { "width": "30%", "targets": 5 } ],
                        destroy: true, paging: false, searching: true, info: false, lengthChange: false,
                        language: { url: "//cdn.datatables.net/plug-ins/1.13.6/i18n/es-ES.json" }
                    });
                } else {
                    $('#availability-table tbody').html('<tr><td colspan="6" class="text-center">No se encontraron vehículos disponibles.</td></tr>');
                }
            }, 'json').fail(() => $('#availability-table tbody').html('<tr><td colspan="6" class="text-center text-danger">Error de servidor.</td></tr>'));
        });
        
        $('#availability-table tbody').on('click', 'tr', function() {
            const radio = $(this).find('input[type="radio"]');
            if (radio.length) {
                radio.prop('checked', true);
                $(this).siblings().removeClass('table-primary');
                $(this).addClass('table-primary');
                updateProceedButtonState();
            }
        });
       
        $('#save-inquiry-btn').on('click', function() {
            // 1) Crea el FormData a partir del form
            const formElement = document.getElementById('call-details-form');
            const formData = new FormData(formElement);

            // 2) Ajusta los campos que necesitas
            formData.set('action', 'save_call_inquiry');
            formData.set('customer_id', clientIdInput.val());
            formData.set('phone', phoneInput.val().trim());

            // 3) Elimina cualquier sources[] preexistente y vuelve a anexarlos
            formData.delete('sources[]');
            $('.source-checkbox:checked').each(function() {
                formData.append('sources[]', $(this).val());
            });

            // 4) Envío AJAX
            Swal.fire({ title: 'Guardando...', allowOutsideClick: false, didOpen: () => Swal.showLoading() });
            $.ajax({
                url: 'ajax_handler.php',
                type: 'POST',
                data: formData,
                processData: false,
                contentType: false,
                dataType: 'json'
            }).done(res => {
                if (res.success) {
                    Swal.fire({ icon: 'success', title: '¡Guardado!', timer: 1000, showConfirmButton: false });
                    // recarga para ver la tabla y preseleccionar
                    phoneInput.trigger('keyup');
                } else {
                    Swal.fire('Error', res.message, 'error');
                }
            }).fail(() => {
                Swal.fire('Error', 'Falló la conexión', 'error');
            });
        });
    // Función reutilizable para guardar la consulta
    /*const saveInquiry = (callback) => {
        const formData = new FormData($('#call-details-form')[0]);
        let sources = [];
        $('.source-checkbox:checked').each(function() { sources.push($(this).val()); });
        let currentNotes = formData.get('observacion') || '';
        if (sources.length > 0) {
            formData.set('observacion', currentNotes + "\n\nFuente de contacto: " + sources.join(', '));
        }
        formData.append('action', 'save_call_inquiry');
        formData.append('customer_id', clientIdInput.val());
        formData.append('phone', phoneInput.val());
        
        $.ajax({
            url: 'ajax_handler.php', type: 'POST', data: formData,
            processData: false, contentType: false, dataType: 'json',
            success: callback,
            error: () => Swal.fire({ icon: 'error', title: 'Error de Conexión', text: 'No se pudo comunicar con el servidor.' })
        });
    };*/
    const saveInquiry = (callback) => {
        const formData = new FormData($('#call-details-form')[0]);
        let sources = [];
        $('.source-checkbox:checked').each(function() { sources.push($(this).val()); });
        let currentNotes = formData.get('observacion') || '';
        if (sources.length > 0) {
            // Evitar duplicar el texto si ya existe
            const sourcePrefix = "\n\nFuente de contacto: ";
            const noteParts = currentNotes.split(sourcePrefix);
            currentNotes = noteParts[0].trim();
            formData.set('observacion', currentNotes + sourcePrefix + sources.join(', '));
        }

        formData.append('action', 'save_call_inquiry');
        formData.append('customer_id', clientIdInput.val());
        formData.append('phone', phoneInput.val());
        
        $.ajax({
            url: 'ajax_handler.php', type: 'POST', data: formData,
            processData: false, contentType: false, dataType: 'json',
            success: callback,
            error: () => Swal.fire({ icon: 'error', title: 'Error de Conexión', text: 'No se pudo comunicar con el servidor.' })
        });
    };
    // Botón "Solo Guardar Consulta"
  /*  $('#save-inquiry-btn').on('click', function() {
        if ($(this).is(':disabled')) return;
        Swal.fire({ title: 'Guardando...', allowOutsideClick: false, didOpen: () => Swal.showLoading() });
        saveInquiry(function(response) {
            if (response.success) {
                Swal.fire({ icon: 'success', title: '¡Operación Exitosa!', text: `La consulta ha sido guardada/actualizada. ID: ${response.id}` });
                phoneInput.trigger('keyup'); // Refrescar el historial de consultas
            } else {
                Swal.fire({ icon: 'error', title: 'Error', text: response.message });
            }
        });
    });*/
    
    // Botón "Proceder a Reservar"
  /*  $('#proceed-to-booking-btn').on('click', function() {
        if ($(this).is(':disabled')) {
             Swal.fire('Atención', 'Debe identificar un cliente, seleccionar un vehículo y marcar una fuente de contacto para proceder.', 'warning');
            return;
        }
        
        Swal.fire({ title: 'Guardando y Procediendo...', allowOutsideClick: false, didOpen: () => Swal.showLoading() });

        // Paso 1: Siempre guardamos la consulta primero
        saveInquiry(function(response) {
            if (response.success) {
                // Paso 2: Si se guardó bien, procedemos a la redirección
                const inquiryId = response.id;
                const selectedRadio = $('#availability-table tbody input[type="radio"]:checked');
                const params = {
                    client_id: clientIdInput.val() || '', new_phone: phoneInput.val(),
                    vehicle_id: selectedRadio.val(), vehicle_desc: selectedRadio.data('vehicle-desc'),
                    start_date: $('#pickup-date').val(), end_date: $('#return-date').val(),
                    total_days: selectedRadio.data('total-days'), total_cost: selectedRadio.data('total-cost'),
                    inquiry_id: inquiryId
                };

                const form = document.createElement('form');
                form.method = 'POST';
                form.action = 'index.php?page=new_rental';
                for (const key in params) {
                    const hiddenField = document.createElement('input');
                    hiddenField.type = 'hidden';
                    hiddenField.name = key;
                    hiddenField.value = params[key];
                    form.appendChild(hiddenField);
                }
                document.body.appendChild(form);
                form.submit();
            } else {
                Swal.fire({ icon: 'error', title: 'Error', text: 'No se pudo guardar la consulta antes de proceder. ' + response.message });
            }
        });
    });*/
        // Función reutilizable para guardar la consulta
 
      // Botón "Proceder a Reservar"
    $('#proceed-to-booking-btn').on('click', function() {
        if ($(this).is(':disabled')) {
             Swal.fire('Atención', 'Debe identificar un cliente, seleccionar un vehículo y marcar una fuente de contacto para proceder.', 'warning');
            return;
        }
        
        Swal.fire({ title: 'Guardando y Procediendo...', allowOutsideClick: false, didOpen: () => Swal.showLoading() });

        // Paso 1: Siempre guardamos la consulta primero
        saveInquiry(function(response) {
            if (response.success) {
                // Paso 2: Si se guardó bien, procedemos a la redirección
                const inquiryId = response.id;
                const selectedRadio = $('#availability-table tbody input[type="radio"]:checked');
                const params = {
                    client_id: clientIdInput.val() || '', new_phone: phoneInput.val(),
                    vehicle_id: selectedRadio.val(), vehicle_desc: selectedRadio.data('vehicle-desc'),
                    start_date: $('#pickup-date').val(), end_date: $('#return-date').val(),
                    total_days: selectedRadio.data('total-days'), total_cost: selectedRadio.data('total-cost'),
                    inquiry_id: inquiryId
                };

                const form = document.createElement('form');
                form.method = 'POST';
                form.action = 'index.php?page=new_rental';
                for (const key in params) {
                    const hiddenField = document.createElement('input');
                    hiddenField.type = 'hidden';
                    hiddenField.name = key;
                    hiddenField.value = params[key];
                    form.appendChild(hiddenField);
                }
                document.body.appendChild(form);
                form.submit();
            } else {
                Swal.fire({ icon: 'error', title: 'Error', text: 'No se pudo guardar la consulta antes de proceder. ' + response.message });
            }
        });
    });

        updateProceedButtonState();
    }


/* =======
| CONTROL VEHICULOS - NUEVO SISTEMA - GEMINI
============*/
// AÑADIR este bloque completo al final de public/js/scripts.js

// --- LÓGICA PARA LA PÁGINA DE GESTIÓN DE VEHÍCULOS (vehiculos.php) ---
if ($('#vehicles-table').length) {
    const vehicleModal = new bootstrap.Modal(document.getElementById('vehicle-modal'));
    
    const vehiclesTable = $('#vehicles-table').DataTable({
        responsive: true, processing: true, serverSide: true,
        ajax: { url: "ajax_handler.php", type: "POST", data: { action: 'get_vehicles' } },
        columns: [
            { data: 'id' },
            { data: 'picloc', render: data => `<img src="public/vehiculos_flota/thumb/${data || 'noimage.jpg'}" class="car-thumbnail">` },
           { data: 'License', render: function(data, type, row) {
                return `<a href="index.php?page=vehicle_history&id=${row.id}" target="_blank">${data}</a>`;
            }}, { data: 'Make' }, { data: 'Model' }, { data: 'year' },
            { data: 'class' }, { data: 'trans' }, { data: 'fueltype' },
         
            { data: 'avb', render: function(data, type, row) {
                let status = { text: 'Desconocido', class: 'bg-secondary', icon: 'fa-question-circle' };
                switch(parseInt(data)) {
                    case 0: status = { text: 'Disponible', class: 'bg-success', icon: 'fa-check-circle' }; break;
                    case 1: status = { text: 'Mantenimiento', class: 'bg-warning', icon: 'fa-tools' }; break;
                    case 2: status = { text: 'Accidente', class: 'bg-danger', icon: 'fa-car-crash' }; break;
                    case 3: status = { text: 'Vendido', class: 'bg-dark', icon: 'fa-hand-holding-usd' }; break;
                    case 4: status = { text: 'Depósito', class: 'bg-info', icon: 'fa-warehouse' }; break;
                }
                return `<span class="badge ${status.class}"><i class="fas ${status.icon} me-1"></i>${status.text}</span>`;
            }},
            { data: null, defaultContent: '', orderable: false, render: (data, type, row) => `<div class="btn-group btn-group-sm"><button class="btn btn-primary edit-btn" data-id="${row.id}" title="Editar"><i class="fas fa-pencil-alt"></i></button><button class="btn btn-danger delete-btn" data-id="${row.id}" title="Eliminar"><i class="fas fa-trash-alt"></i></button></div>` }
        ],
        language: { url: "//cdn.datatables.net/plug-ins/1.13.6/i18n/es-ES.json" }
    });

    $('#btn-nuevo-vehiculo').on('click', function() {
        $('#vehicle-form')[0].reset();
        $('#vehicle-id').val('');
        $('#image-preview').attr('src', 'public/img/placeholder-image.jpg');
        $('#vehicleModalLabel').text('Registrar Nuevo Vehículo');
        vehicleModal.show();
    });

    $('#vehicles-table tbody').on('click', '.edit-btn', function() {
        const id = $(this).data('id');
        $.post('ajax_handler.php', { action: 'get_vehicle', id: id }, function(response) {
            if (response.success && response.data) {
                const vehicle = response.data;
                for (const key in vehicle) { if (vehicle.hasOwnProperty(key)) { $('#' + key.toLowerCase()).val(vehicle[key]); } }
                $('#vehicle-id').val(vehicle.id);
                // CORRECCIÓN: Seleccionamos el campo #avb y le asignamos el valor de vehicle.avb
                $('#avb').val(vehicle.avb);
                $('#existing_picloc').val(vehicle.picloc);
                $('#image-preview').attr('src', `public/vehiculos_flota/thumb/${vehicle.picloc || 'placeholder-image.jpg'}`);
                $('#vehicleModalLabel').text('Editar Vehículo #' + vehicle.id);
                vehicleModal.show();
            }
        }, 'json');
    });

    $('#picloc_file').on('change', function(event) {
        const file = event.target.files[0];
        if (file) {
            $('#image-preview').attr('src', URL.createObjectURL(file));
        }
    });

    // El resto del código para los botones de submit y delete no necesita cambios.
$('#vehicle-form').on('submit', function(e) {
        e.preventDefault();
        const formData = new FormData(this);
        formData.append('action', 'save_vehicle');
        $.ajax({
            url: 'ajax_handler.php', type: 'POST', data: formData, processData: false, contentType: false, dataType: 'json',
            success: function(response) {
                if(response.success) {
                    vehicleModal.hide();
                    Swal.fire({ icon: 'success', title: '¡Éxito!', text: response.message, timer: 2000, showConfirmButton: false });
                    vehiclesTable.ajax.reload(null, false); // Recargar la tabla sin resetear la paginación
                } else { Swal.fire({ icon: 'error', title: 'Error', text: response.message }); }
            }
        });
    });

    $('#vehicles-table tbody').on('click', '.delete-btn', function() {
        const id = $(this).data('id');
        Swal.fire({
            title: '¿Estás seguro?', text: "El vehículo se marcará como eliminado.", icon: 'warning',
            showCancelButton: true, confirmButtonColor: '#d33', cancelButtonColor: '#3085d6',
            confirmButtonText: 'Sí, eliminar', cancelButtonText: 'Cancelar'
        }).then((result) => {
            if (result.isConfirmed) {
                $.post('ajax_handler.php', { action: 'delete_vehicle', id: id }, function(response) {
                    if(response.success) {
                        Swal.fire('¡Eliminado!', response.message, 'success');
                        vehiclesTable.ajax.reload(null, false);
                    } else { Swal.fire('Error', response.message, 'error'); }
                }, 'json');
            }
        });
    });
}

// AÑADIR este bloque completo al final de public/js/scripts.js

// --- LÓGICA PARA LA PÁGINA DE GESTIÓN DE USUARIOS (usuarios.php) ---
if ($('#users-table').length) {
    const userModal = new bootstrap.Modal(document.getElementById('user-modal'));
    const usersTable = $('#users-table').DataTable({
        responsive: true, processing: true, serverSide: true,
        ajax: { url: "ajax_handler.php", type: "POST", data: { action: 'get_users' } },
        columns: [
            { data: 'id' },
            { data: 'nombre' }, // <-- Columna 'nombre' de user_profiles
            { data: 'username' },
            { data: 'email' },
            { data: 'role' },
            { data: 'activated', render: data => data == 1 ? '<span class="badge bg-success">Activo</span>' : '<span class="badge bg-secondary">Inactivo</span>' },
            { data: null, defaultContent: '', orderable: false, render: (data, type, row) => `<div class="btn-group btn-group-sm"><button class="btn btn-primary edit-btn" data-id="${row.id}"><i class="fas fa-pencil-alt"></i></button><button class="btn btn-danger delete-btn" data-id="${row.id}"><i class="fas fa-user-slash"></i></button></div>` }
        ],
        language: { url: "//cdn.datatables.net/plug-ins/1.13.6/i18n/es-ES.json" }
    });
    $('#btn-nuevo-usuario').on('click', function() {
        $('#user-form')[0].reset();
        $('#user-id').val('');
        $('#password').prop('required', true);
        $('#userModalLabel').text('Registrar Nuevo Usuario');
        userModal.show();
    });
    $('#users-table tbody').on('click', '.edit-btn', function() {
        const id = $(this).data('id');
        $.post('ajax_handler.php', { action: 'get_user', id: id }, function(response) {
            if (response.success && response.data) {
                const user = response.data;
                $('#user-id').val(user.id);
                $('#nombre').val(user.nombre); // <-- Campo 'nombre'
                $('#username').val(user.username);
                $('#email').val(user.email);
                $('#role').val(user.role);
                $('#activated').val(user.activated); // <-- Campo 'activated'
                $('#password').val('').prop('required', false);
                $('#userModalLabel').text('Editar Usuario #' + user.id);
                userModal.show();
            }
        }, 'json');
    });

    $('#user-form').on('submit', function(e) {
        e.preventDefault();
        const formData = new FormData(this);
        formData.append('action', 'save_user');
        $.ajax({
            url: 'ajax_handler.php', type: 'POST', data: formData, processData: false, contentType: false, dataType: 'json',
            success: function(response) {
                if(response.success) {
                    userModal.hide();
                    Swal.fire({ icon: 'success', title: '¡Éxito!', text: response.message });
                    usersTable.ajax.reload(null, false);
                } else {
                    Swal.fire({ icon: 'error', title: 'Error', text: response.message });
                }
            }
        });
    });

    $('#users-table tbody').on('click', '.delete-btn', function() {
        const id = $(this).data('id');
        Swal.fire({
            title: '¿Estás seguro?', text: "El usuario será marcado como inactivo.", icon: 'warning',
            showCancelButton: true, confirmButtonColor: '#d33', cancelButtonText: 'Cancelar', confirmButtonText: 'Sí, desactivar'
        }).then((result) => {
            if (result.isConfirmed) {
                $.post('ajax_handler.php', { action: 'delete_user', id: id }, function(response) {
                    if(response.success) {
                        Swal.fire('¡Desactivado!', response.message, 'success');
                        usersTable.ajax.reload(null, false);
                    } else { Swal.fire('Error', response.message, 'error'); }
                }, 'json');
            }
        });
    });
}

function reinicializarTabla() {

  if ( $.fn.dataTable.isDataTable('#report-results-table') ) {
    reportTable.destroy();            // 1) destruye la instancia
    $('#report-results-table').empty(); // 2) limpia el DOM por si quedaron filas
  }

  // 3) la creas de nuevo con tus opciones
  
}
 
 // --- LÓGICA COMPLETA Y CORREGIDA PARA LA PÁGINA DE REPORTES (reportes.php) ---
if ($('#report-main-tab').length) {

        // --- Lógica para la Pestaña 1: Reporteador Personalizado ---
        if ($('#report-filters-form').length) {
            
            let reportTable; // Definir la variable aquí para que sea accesible

            const initializeReportTable = () => {
                if ($.fn.DataTable.isDataTable('#report-results-table')) { return; }
                reportTable = $('#report-results-table').DataTable({
                    responsive: true, dom: 'lBfrtip', lengthChange: true, paging: true,
                    lengthMenu: [ [10, 25, 50, -1], [10, 25, 50, "Todos"] ],
                    buttons: [
                        { extend: 'excelHtml5', text: '<i class="fas fa-file-excel"></i> Excel', className: 'btn btn-success' },
                        { extend: 'pdfHtml5', text: '<i class="fas fa-file-pdf"></i> PDF', className: 'btn btn-danger' },
                        { extend: 'print', text: '<i class="fas fa-print"></i> Imprimir', className: 'btn btn-secondary' }
                    ],
                    data: [], 
                    columns: [
                        { data: 'confnr' }, { data: null, render: (d,t,r) => `${r.drfirstname||''} ${r.drlastname||''}`.trim() },
                        { data: null, render: (d,t,r) => `${r.Make||''} ${r.Model||''}`.trim() }, { data: 'year' },
                        { data: 'STARTTIME', render: d => d ? new Date(d).toLocaleDateString() : '' },
                        { data: 'ENDTIME', render: d => d ? new Date(d).toLocaleDateString() : '' }, { data: 'fueltype' },
                        { data: 'amount', render: d => `$${parseFloat(d||0).toFixed(2)}` }
                    ],
                    language: { url: "//cdn.datatables.net/plug-ins/1.13.6/i18n/es-ES.json", emptyTable: "Use los filtros para generar un reporte." }
                });
            };
            
            if ($('#tab-reporteador').hasClass('active')) {
                initializeReportTable();
            }
            $('#tab-reporteador').one('shown.bs.tab', initializeReportTable);

            const populateSelect = (selector, action, valueField, textField) => {
                $.post('ajax_handler.php', { action: action }, function(response) {
                    if (response.success && response.data) {
                        const select = $(selector);
                        response.data.forEach(item => {
                            select.append(`<option value="${item[valueField]}">${item[textField] || item[valueField]}</option>`);
                        });
                    }
                });
            };

            populateSelect('#make', 'get_all_makes', 'nombre', 'nombre');
            populateSelect('#class', 'get_all_classes', 'class', 'descr');
            populateSelect('#year', 'get_distinct_vehicle_years', 'year', 'year');

            $('#make').on('change', function() {
                const makeName = $(this).val();
                const modelSelect = $('#model');
                modelSelect.html('<option value="">Todos</option>').prop('disabled', !makeName);
                if (makeName) {
                    $.post('ajax_handler.php', { action: 'get_models_by_make', make: makeName }, function(response) {
                        if (response.success) {
                            response.data.forEach(model => modelSelect.append(`<option value="${model.nombre}">${model.nombre}</option>`));
                        }
                    });
                }
            });

            $('#report-filters-form').on('submit', function(e) {
                e.preventDefault();
                const formData = $(this).serialize() + '&action=get_report_data';
                
                if (!reportTable) { initializeReportTable(); }

                reportTable.clear().draw();
                $('#report-results-table tbody').html('<tr><td colspan="8" class="text-center"><div class="spinner-border text-primary"></div></td></tr>');

                $.post('ajax_handler.php', formData, function(response) {
                    reportTable.rows.add(response.data || []).draw();
                }).fail(function() {
                     $('#report-results-table tbody').html('<tr><td colspan="8" class="text-center text-danger">Error al cargar datos.</td></tr>');
                });
            });
        }

        // --- Lógica para la Pestaña 2: Estadísticas Gráficas ---
        if ($('#report-stats-form').length) {
            let salesChart, mostRentedChart, mostRentedByClassChart, rentalsByDeptChart, rentalsByMonthCountryChart;
            const salesYearInput = $('#comparison_year');
            const rentedYearSelect = $('#rented_year');
            const rentedDeptYearInput = $('#rented_year_department'); // Selector para el nuevo gráfico
            const rentedClassYearInput = $('#rented_year_class'); // Selector para el nuevo gráfico
            const mostRentedChartContainer = $('#mostRentedChart').parent();
            const yearSelect = $('#comparison_year');
             // --- Contenedores de Gráficos ---
            //const mostRentedChartContainer = $('#mostRentedChart').parent();
            const rentalsByDeptChartContainer = $('#rentalsByDepartmentChart').parent();
            const rentedMonthCountryYearInput = $('#rented_year_month_country');
            
            $.post('ajax_handler.php', { action: 'get_distinct_rental_years' }, function(response) {
                if (response.success) {
                    response.data.forEach(item => {
                        yearSelect.append(`<option value="${item.year}">${item.year}</option>`);
                    });
                    $('#report-stats-form').trigger('submit');
                }
            });

            $('#report-stats-form').on('submit', function(e) {
                e.preventDefault();
                const selectedYear = yearSelect.val();
                if (!selectedYear) return;

                $.post('ajax_handler.php', { action: 'get_sales_comparison', year: selectedYear }, function(response) {
                    if (response.success) {
                        const data = response.data;
                        const chartData = {
                            labels: data.labels,
                            datasets: [
                                { label: `Ventas ${data.previousYearLabel}`, data: data.previousYear, backgroundColor: 'rgba(108, 117, 125, 0.5)' },
                                { label: `Ventas ${data.currentYearLabel}`, data: data.currentYear, backgroundColor: 'rgba(13, 110, 253, 0.5)' }
                            ]
                        };
                        const ctx = document.getElementById('salesComparisonChart').getContext('2d');
                        if (salesChart) { salesChart.destroy(); }

                        salesChart = new Chart(ctx, {
                            type: 'bar',
                            data: chartData,
                            options: {
                                responsive: true,
                                maintainAspectRatio: false,
                                scales: {
                                    y: {
                                        beginAtZero: true,
                                        ticks: {
                                            // Formatear los números del eje Y
                                            callback: function(value) {
                                                return '$' + value.toLocaleString('en-US', { minimumFractionDigits: 2, maximumFractionDigits: 2 });
                                            }
                                        }
                                    }
                                },
                                plugins: {
                                    tooltip: {
                                        callbacks: {
                                            label: function(context) {
                                                let label = context.dataset.label || '';
                                                if (label) {
                                                    label += ': ';
                                                }
                                                if (context.parsed.y !== null) {
                                                    // Formatear los números del tooltip
                                                    label += '$' + context.parsed.y.toLocaleString('en-US', { minimumFractionDigits: 2, maximumFractionDigits: 2 });
                                                }
                                                return label;
                                            }
                                        }
                                    },
                                    legend: { position: 'top' },
                                    title: { display: true, text: `Comparativo de Ventas Mensuales (${data.previousYearLabel} vs ${data.currentYearLabel})` }
                                }
                            }
                        });
                    }
                });
            });

            $('#report-vehicles-form').on('submit', function(e) {
            e.preventDefault();
            const selectedYear = rentedYearSelect.val();
            if (!selectedYear) return;

            $.post('ajax_handler.php', { action: 'get_most_rented_vehicles', year: selectedYear }, function(response) {
                if (response.success && response.data.length > 0) {
                    // ===============================================
                    // NUEVO: Lógica para calcular la altura dinámica
                    // ===============================================
                    const vehicleCount = response.data.length;
                    // Calculamos una altura base + píxeles adicionales por cada vehículo
                    const dynamicHeight = 150 + (vehicleCount * 25); // Ej: 25px por barra
                    mostRentedChartContainer.css('height', `${dynamicHeight}px`);

                    const labels = response.data.map(item => item.vehicle_name);
                    const rentalCounts = response.data.map(item => item.rental_count);
                    
                    const chartData = {
                        labels: labels,
                        datasets: [{
                            label: `Cantidad de Rentas (${selectedYear})`,
                            data: rentalCounts,
                            backgroundColor: 'rgba(25, 135, 84, 0.6)',
                            borderColor: 'rgba(25, 135, 84, 1)',
                            borderWidth: 1
                        }]
                    };

                    const ctx = document.getElementById('mostRentedChart').getContext('2d');
                    
                    mostRentedChart = new Chart(ctx, {
                        type: 'bar',
                        data: chartData,
                        options: {
                            indexAxis: 'y',
                            responsive: true,
                            maintainAspectRatio: false, // ¡Muy importante para que la altura dinámica funcione!
                            scales: { x: { beginAtZero: true } },
                            plugins: {
                                legend: { display: true, position: 'top' },
                                title: { display: true, text: `Vehículos Más Rentados en ${selectedYear}` }
                            }
                        }
                    });
                } else {
                    // Si no hay datos, ocultamos el canvas
                    mostRentedChartContainer.css('height', '0px');
                }
            }, 'json');
        });


            // ==========================================================
        // NUEVO: Lógica para Gráfico 3: Vehículos Más Rentados por Clase
        // ==========================================================
        $('#report-class-form').on('submit', function(e) {
            e.preventDefault();
            const selectedYear = rentedClassYearInput.val(); 
            if (!selectedYear) return;

            $.post('ajax_handler.php', { action: 'get_most_rented_by_class', year: selectedYear }, function(response) {
                if (mostRentedByClassChart) { mostRentedByClassChart.destroy(); }

                if (response.success && response.data.length > 0) {
                    const labels = response.data.map(item => item.vehicle_class);
                    const rentalCounts = response.data.map(item => item.rental_count);
                    
                    const chartData = {
                        labels: labels,
                        datasets: [{
                            label: `Cantidad de Rentas por Clase (${selectedYear})`,
                            data: rentalCounts,
                            backgroundColor: [
                                'rgba(255, 99, 132, 0.6)', 'rgba(54, 162, 235, 0.6)',
                                'rgba(255, 206, 86, 0.6)', 'rgba(75, 192, 192, 0.6)',
                                'rgba(153, 102, 255, 0.6)', 'rgba(255, 159, 64, 0.6)'
                            ],
                            borderColor: [
                                'rgba(255, 99, 132, 1)', 'rgba(54, 162, 235, 1)',
                                'rgba(255, 206, 86, 1)', 'rgba(75, 192, 192, 1)',
                                'rgba(153, 102, 255, 1)', 'rgba(255, 159, 64, 1)'
                            ],
                            borderWidth: 1
                        }]
                    };

                    const ctx = document.getElementById('mostRentedByClassChart').getContext('2d');
                    
                    mostRentedByClassChart = new Chart(ctx, {
                        type: 'bar', // Puede ser 'pie' o 'doughnut' para variedad
                        data: chartData,
                        options: {
                            responsive: true,
                            maintainAspectRatio: false,
                            plugins: {
                                legend: { position: 'top' },
                                title: { display: true, text: `Rentas por Clase de Vehículo en ${selectedYear}` }
                            }
                        }
                    });
                }
            }, 'json');
        });

        // ==========================================================
        // NUEVO: Lógica para Gráfico 4: Rentas por Departamento
        // ==========================================================
        $('#report-department-form').on('submit', function(e) {
            e.preventDefault();
            const selectedYear = rentedDeptYearInput.val(); 
            if (!selectedYear) return;

            $.post('ajax_handler.php', { action: 'get_rentals_by_state', year: selectedYear }, function(response) {
                if (rentalsByDeptChart) { rentalsByDeptChart.destroy(); }

                if (response.success && response.data.length > 0) {
                    // --- ALTURA DINÁMICA ---
                    const deptCount = response.data.length;
                    const dynamicHeight = 120 + (deptCount * 30); // 120px base + 30px por cada barra
                    rentalsByDeptChartContainer.css('height', `${dynamicHeight}px`);

                    const labels = response.data.map(item => item.state_name);
                    const rentalCounts = response.data.map(item => item.rental_count);
                    
                    const chartData = {
                        labels: labels,
                        datasets: [{
                            label: `Cantidad de Rentas (${selectedYear})`,
                            data: rentalCounts,
                            backgroundColor: 'rgba(255, 159, 64, 0.6)', // Naranja
                            borderColor: 'rgba(255, 159, 64, 1)',
                            borderWidth: 1
                        }]
                    };

                    const ctx = document.getElementById('rentalsByDepartmentChart').getContext('2d');
                    
                    rentalsByDeptChart = new Chart(ctx, {
                        type: 'bar',
                        data: chartData,
                        options: {
                            indexAxis: 'y',
                            responsive: true,
                            maintainAspectRatio: false, // ¡Importante para la altura dinámica!
                            scales: { x: { beginAtZero: true } },
                            plugins: {
                                legend: { display: true, position: 'top' },
                                title: { display: true, text: `Rentas por Departamento en ${selectedYear}` },
                                // --- ETIQUETAS DE DATOS ---
                                datalabels: {
                                    anchor: 'end',
                                    align: 'right',
                                    offset: 8,
                                    color: '#333',
                                    font: { weight: 'bold' },
                                    formatter: (value) => value
                                }
                            }
                        }
                    });
                } else {
                    rentalsByDeptChartContainer.css('height', '0px');
                }
            }, 'json');
        });

        $('#report-month-country-form').on('submit', function(e) {
            e.preventDefault();
            const selectedYear = rentedMonthCountryYearInput.val(); 
            if (!selectedYear) return;

            $.post('ajax_handler.php', { action: 'get_rentals_by_month_state', year: selectedYear }, function(response) {
                if (rentalsByMonthCountryChart) { rentalsByMonthCountryChart.destroy(); }

                if (response.success && response.data.length > 0) {
                    
                    const months = ['Ene', 'Feb', 'Mar', 'Abr', 'May', 'Jun', 'Jul', 'Ago', 'Sep', 'Oct', 'Nov', 'Dic'];
                    const countries = [...new Set(response.data.map(item => item.country_name))];
                    const colorPalette = ['rgba(255, 99, 132, 0.7)', 'rgba(54, 162, 235, 0.7)', 'rgba(255, 206, 86, 0.7)', 'rgba(75, 192, 192, 0.7)', 'rgba(153, 102, 255, 0.7)', 'rgba(255, 159, 64, 0.7)'];

                    const datasets = countries.map((country, index) => {
                        const dataForCountry = new Array(12).fill(0);
                        response.data.forEach(item => {
                            if (item.country_name === country) {
                                dataForCountry[item.sale_month - 1] = item.rental_count;
                            }
                        });
                        return {
                            label: country,
                            data: dataForCountry,
                            backgroundColor: colorPalette[index % colorPalette.length],
                        };
                    });
                    
                    const chartData = {
                        labels: months,
                        datasets: datasets
                    };

                    const ctx = document.getElementById('rentalsByMonthCountryChart').getContext('2d');
                    
                    rentalsByMonthCountryChart = new Chart(ctx, {
                        type: 'bar',
                        data: chartData,
                        options: {
                            responsive: true,
                            maintainAspectRatio: false,
                            scales: { 
                                x: { stacked: true }, // Apilar las barras en el eje X
                                y: { stacked: true, beginAtZero: true } // Apilar las barras en el eje Y
                            },
                            plugins: {
                                title: { display: true, text: `Rentas por País/Departamento en ${selectedYear}` }
                            }
                        }
                    });
                }
            }, 'json');
        });

        if ($('#report-geo-form').length) {
            let countryChart, stateChart;
            const geoYearInput = $('#geo_year');
            const drilldownContainer = $('#geo-drilldown-container');
            const drilldownTitle = $('#drilldown-title');
            const topVehiclesTbody = $('#top-vehicles-table-body');
            const stateChartCanvas = document.getElementById('rentalsByStateChart').getContext('2d');

            const generateCountryChart = (year) => {
            $.post('ajax_handler.php', { action: 'get_rentals_by_country', year: year }, function(response) {
                if (countryChart) { countryChart.destroy(); }
                drilldownContainer.hide();
                
                if (response.success && response.data.length > 0) {
                    const labels = response.data.map(item => item.country);
                    const rentalCounts = response.data.map(item => item.rental_count);
                    const chartData = {
                        labels: labels,
                        datasets: [{ label: `Rentas por País (${year})`, data: rentalCounts, backgroundColor: ['#FF6384', '#36A2EB', '#FFCE56', '#4BC0C0', '#9966FF', '#FF9F40'] }]
                    };
                    const ctx = document.getElementById('rentalsByCountryChart').getContext('2d');
                    countryChart = new Chart(ctx, {
                        type: 'doughnut', data: chartData,
                        options: {
                            responsive: true, maintainAspectRatio: false,
                            onClick: (event, elements) => {
                                if (elements.length > 0) {
                                    const clickedIndex = elements[0].index;
                                    const selectedCountry = countryChart.data.labels[clickedIndex];
                                    generateDrilldown(year, selectedCountry);
                                }
                            },
                            plugins: { title: { display: true, text: `Distribución de Rentas por País en ${year}` } }
                        }
                    });
                }
            });
        };

        const generateDrilldown = (year, country) => {
            drilldownTitle.text(`Detalle para: ${country}`);
            topVehiclesTbody.html('<tr><td colspan="2" class="text-center"><div class="spinner-border spinner-border-sm"></div></td></tr>');
            if (stateChart) { stateChart.destroy(); }

            $.post('ajax_handler.php', { action: 'get_geo_drilldown', year: year, country: country }, function(response) {
                if (response.success) {
                    // Llenar tabla de Top 20 Vehículos
                    topVehiclesTbody.empty();
                    response.data.top_vehicles.forEach(v => {
                        topVehiclesTbody.append(`<tr><td>${v.vehicle_name}</td><td>${v.rental_count}</td></tr>`);
                    });

                    // Generar gráfico de Departamentos
                    const stateLabels = response.data.by_state.map(s => s.state);
                    const stateCounts = response.data.by_state.map(s => s.rental_count);
                    stateChart = new Chart(stateChartCanvas, {
                        type: 'bar',
                        data: { labels: stateLabels, datasets: [{ label: 'Rentas', data: stateCounts, backgroundColor: '#36A2EB' }] },
                        options: { indexAxis: 'y', responsive: true, maintainAspectRatio: false, plugins: { legend: { display: false } } }
                    });
                    drilldownContainer.show();
                }
            });
        };

        $('#report-geo-form').on('submit', function(e) {
            e.preventDefault();
            generateCountryChart(geoYearInput.val());
        }).trigger('submit');
    }
        // Cargar los gráficos al inicio
        $('#report-stats-form').trigger('submit');
        $('#report-vehicles-form').trigger('submit');
        $('#report-class-form').trigger('submit');
        $('#report-department-form').trigger('submit'); 
        $('#report-month-country-form').trigger('submit');

     
        
        // Cargar el gráfico de ventas al inicio
       /* if (salesYearInput.val()) {
            $('#report-stats-form').trigger('submit');
        }*/
         // Cargar los gráficos al inicio
        /*if (rentedYearInput.val()) {
            $('#report-vehicles-form').trigger('submit');
        }*/
  

        }
    }


// ==========================================================
// NUEVO: Lógica para la página de Historial de Vehículo
// ==========================================================
if ($('#rental-history-table').length) {
    const urlParams = new URLSearchParams(window.location.search);
    const vehicleId = urlParams.get('id');

    if (vehicleId) {
        $('#rental-history-table').DataTable({
            responsive: true,
            processing: true,
            serverSide: false, // Usamos procesamiento del lado del cliente aquí
            ajax: {
                url: 'ajax_handler.php',
                type: 'POST',
                data: {
                    action: 'get_vehicle_rental_history',
                    id: vehicleId
                }
            },
            columns: [
                { data: 'confnr' },
                { data: null, render: (data, type, row) => `${row.drfirstname} ${row.drlastname}` },
                { data: 'drhphone' },
                { data: 'STARTTIME', render: data => new Date(data).toLocaleDateString() },
                { data: 'ENDTIME', render: data => new Date(data).toLocaleDateString() },
                { data: 'rentdays' },
                { data: 'invoicetotal', render: data => `$${parseFloat(data).toFixed(2)}` }
            ],
            order: [[0, 'desc']],
            // CORRECCIÓN: Se añade el selector de paginación
            dom: 'lBfrtip',
            lengthChange: true, // Nos aseguramos que el control esté visible
            lengthMenu: [ [10, 25, 50, -1], [10, 25, 50, "Todos"] ],
         
            buttons: ['excel', 'pdf', 'print'],
            language: { url: "//cdn.datatables.net/plug-ins/1.13.6/i18n/es-ES.json" }
        });
    }
}


if ($('#text-report-container').length) {
    
    // Función para formatear las entradas de ENTREGAS
    const formatEntrega = (entry) => {
        const phone = entry.drhphone || 'N/A';
        const client = `${entry.drfirstname || ''} ${entry.drlastname || ''}`.trim();
        
        // Formateo de fechas y horas
        const deliveryDate = new Date(entry.STARTTIME).toLocaleDateString('es-SV', { day: '2-digit', month: '2-digit', year: 'numeric' });
        const deliveryTime = new Date(entry.STARTTIME).toLocaleTimeString('es-SV', { hour: '2-digit', minute: '2-digit', hour12: true });
        const returnDate = new Date(entry.ENDTIME).toLocaleDateString('es-SV', { day: '2-digit', month: '2-digit', year: 'numeric' });
        const returnTime = new Date(entry.ENDTIME).toLocaleTimeString('es-SV', { hour: '2-digit', minute: '2-digit', hour12: true });

        // Detalles del vehículo
        const vehicle = `${entry.Make || ''} ${entry.Model || ''} ${entry.year || ''}`.trim();
        const license = entry.License || 'N/A';
        const fueltype = entry.fueltype || '';
        const amount = parseFloat(entry.amount || 0).toFixed(2);
        
        // Lógica para el nivel de combustible (puedes ajustar los textos)
        const fuelLevels = { 1: '1/8', 2: '1/4', 3: '3/8', 4: '1/2', 5: '5/8', 6: '3/4', 7: '7/8', 8: 'Lleno' };
        const fuelLevelText = "con medio Tanque de Combustible" || 'Nivel no especificado';//fuelLevels[entry.fuellevel]
        
        const pickupInfo = entry.picinfo || 'Lugar no especificado';

        return `${phone}\n${client}, ${deliveryDate} llevar ${license} ${vehicle},${fueltype}, ${fuelLevelText}, al ${pickupInfo} a las ${deliveryTime} y lo va a regresar el ${returnDate} ${returnTime} y va a pagar $${amount}\n\n`;
    };

    // Función para formatear las entradas de RETORNOS
    const formatRetorno = (entry) => {
        const phone = entry.drhphone || 'N/A';
        const client = `${entry.drfirstname || ''} ${entry.drlastname || ''}`.trim();
        const returnTime = new Date(entry.ENDTIME).toLocaleTimeString('es-SV', { hour: '2-digit', minute: '2-digit', hour12: true });
        const vehicle = `${entry.Make || ''} ${entry.Model || ''} ${entry.year || ''}`.trim();
        const license = entry.License || 'N/A';
        const fueltype = entry.fueltype || '';
        const trans = entry.trans || '';

        return `${phone}\n${client}, retorna ${license} ${vehicle},${fueltype},${trans}, a las ${returnTime}\n\n`;
    };

    const renderReport = (data) => {
        let entregasHoyText = data.entregas_hoy.map(e => formatEntrega(e)).join('');
        $('#entregas-hoy').text(entregasHoyText || 'No hay entregas programadas para hoy.');

        let entregasMananaText = data.entregas_manana.map(e => formatEntrega(e)).join('');
        $('#entregas-manana').text(entregasMananaText || 'No hay entregas programadas para mañana.');

        let retornosHoyText = data.retornos_hoy.map(e => formatRetorno(e)).join('');
        $('#retornos-hoy').text(retornosHoyText || 'No hay retornos programados para hoy.');

        let retornosMananaText = data.retornos_manana.map(e => formatRetorno(e)).join('');
        $('#retornos-manana').text(retornosMananaText || 'No hay retornos programados para mañana.');
    };

    // Cargar los datos al iniciar la página
    $.post('ajax_handler.php', { action: 'get_text_report_data' }, function(response) {
        if (response.success) {
            renderReport(response.data);
        }
    }, 'json');
}








});
