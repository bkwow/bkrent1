<main class="page-content p-4">
    <?php display_flash_messages(); ?>

    <div class="d-flex justify-content-between align-items-center mb-4">
        <h1 class="page-title m-0"><i class="fas fa-calendar-alt me-2 text-primary"></i>Gestión de Reservas</h1>
        <!-- CORRECCIÓN: Este es ahora un enlace a la nueva página -->
        <a href="index.php?page=new_call_triage" class="btn btn-primary fw-bold">
            <i class="fas fa-plus me-2"></i>Nueva Reservación
        </a>
    </div>

    <!-- PESTAÑAS PRINCIPALES -->
    <ul class="nav nav-tabs" id="reservas-tabs" role="tablist">
        <li class="nav-item" role="presentation"><button class="nav-link active" data-bs-toggle="tab" data-bs-target="#panel-hoy" type="button">Reservas de Hoy</button></li>
        <li class="nav-item" role="presentation"><button class="nav-link" data-bs-toggle="tab" data-bs-target="#panel-en_renta" type="button">En Renta</button></li>
        <li class="nav-item" role="presentation"><button class="nav-link" data-bs-toggle="tab" data-bs-target="#panel-entregas" type="button">Entregas</button></li>
        <li class="nav-item" role="presentation"><button class="nav-link" data-bs-toggle="tab" data-bs-target="#panel-retornos" type="button">Retornos</button></li>
        <li class="nav-item" role="presentation"><button class="nav-link" data-bs-toggle="tab" data-bs-target="#panel-proximas" type="button">Próximas Salidas</button></li>
        <li class="nav-item" role="presentation"><button class="nav-link" data-bs-toggle="tab" data-bs-target="#panel-todas" type="button">Historial Completo</button></li>
    </ul>

    <!-- CONTENIDO DE LAS PESTAÑAS -->
    <div class="tab-content pt-3"> <!-- Añadido un padding-top para separar -->
        <?php
        $tableHeaders = '<thead><tr><th></th><th>Resv #</th><th>Img</th><th>Cont #</th><th>Placa</th><th>Marca</th><th>Modelo</th><th>Año</th><th>Nombre</th><th>Apellido</th><th>Teléfono</th><th>Desde</th><th>Hasta</th><th>Total</th><th>Acciones</th></tr></thead>';
        function render_table($table_id) {
            global $tableHeaders;
            echo '<div class="card"><div class="card-body"><div class="table-responsive"><table id="' . $table_id . '" class="table table-hover dt-responsive nowrap" style="width:100%">' . $tableHeaders . '</table></div></div></div>';
        }
        ?>

        <div class="tab-pane fade show active" id="panel-hoy" role="tabpanel"><?php render_table('table-reservas-hoy'); ?></div>
        <div class="tab-pane fade" id="panel-en_renta" role="tabpanel"><?php render_table('table-reservas-en_renta'); ?></div>
        
        <div class="tab-pane fade" id="panel-entregas" role="tabpanel">
            <ul class="nav nav-pills mb-3"><li class="nav-item"><button class="nav-link active" data-bs-toggle="tab" data-bs-target="#panel-entregas-hoy">Hoy</button></li><li class="nav-item"><button class="nav-link" data-bs-toggle="tab" data-bs-target="#panel-entregas-manana">Mañana</button></li></ul>
            <div class="tab-content">
                <div class="tab-pane fade show active" id="panel-entregas-hoy"><?php render_table('table-reservas-entregas_hoy'); ?></div>
                <div class="tab-pane fade" id="panel-entregas-manana"><?php render_table('table-reservas-entregas_manana'); ?></div>
            </div>
        </div>

        <div class="tab-pane fade" id="panel-retornos" role="tabpanel">
            <ul class="nav nav-pills mb-3"><li class="nav-item"><button class="nav-link active" data-bs-toggle="tab" data-bs-target="#panel-retornos-hoy">Hoy</button></li><li class="nav-item"><button class="nav-link" data-bs-toggle="tab" data-bs-target="#panel-retornos-manana">Mañana</button></li></ul>
            <div class="tab-content">
                <div class="tab-pane fade show active" id="panel-retornos-hoy"><?php render_table('table-reservas-retornos_hoy'); ?></div>
                <div class="tab-pane fade" id="panel-retornos-manana"><?php render_table('table-reservas-retornos_manana'); ?></div>
            </div>
        </div>
        
        <div class="tab-pane fade" id="panel-proximas" role="tabpanel"><?php render_table('table-reservas-proximas'); ?></div>
        <div class="tab-pane fade" id="panel-todas" role="tabpanel"><?php render_table('table-reservas-todas'); ?></div>
    </div>
</main>