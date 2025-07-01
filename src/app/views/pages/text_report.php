<main class="page-content p-4">
    <h1 class="page-title mb-4"><i class="fas fa-file-alt me-2 text-primary"></i>Reporte Diario de Operaciones</h1>
    
    <div id="text-report-container">
        <div class="row">
            <!-- Columna para Entregas -->
            <div class="col-lg-6 mb-4">
                <div class="card h-100">
                    <div class="card-header bg-success text-white">
                        <h5 class="m-0"><i class="fas fa-arrow-circle-up me-2"></i>Entregas (Salidas)</h5>
                    </div>
                    <div class="card-body">
                        <h6>Para Hoy: <span class="text-muted"><?= date('d/m/Y') ?></span></h6>
                        <pre id="entregas-hoy" class="report-text-block">Cargando...</pre>
                        <hr>
                        <h6>Para Mañana: <span class="text-muted"><?= date('d/m/Y', strtotime('+1 day')) ?></span></h6>
                        <pre id="entregas-manana" class="report-text-block">Cargando...</pre>
                    </div>
                </div>
            </div>

            <!-- Columna para Retornos -->
            <div class="col-lg-6 mb-4">
                <div class="card h-100">
                    <div class="card-header bg-info text-white">
                        <h5 class="m-0"><i class="fas fa-arrow-circle-down me-2"></i>Retornos (Llegadas)</h5>
                    </div>
                    <div class="card-body">
                        <h6>Para Hoy: <span class="text-muted"><?= date('d/m/Y') ?></span></h6>
                        <pre id="retornos-hoy" class="report-text-block">Cargando...</pre>
                        <hr>
                        <h6>Para Mañana: <span class="text-muted"><?= date('d/m/Y', strtotime('+1 day')) ?></span></h6>
                        <pre id="retornos-manana" class="report-text-block">Cargando...</pre>
                    </div>
                </div>
            </div>
        </div>
    </div>
</main>

<style>
    .report-text-block {
        background-color: #f8f9fa;
        border: 1px solid #e9ecef;
        border-radius: 5px;
        padding: 1rem;
        white-space: pre-wrap; /* Para que el texto se ajuste */
        word-wrap: break-word;
        font-size: 0.9rem;
        min-height: 100px;
    }
</style>
