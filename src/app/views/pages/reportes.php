<main class="page-content p-4">
    <h1 class="page-title mb-4"><i class="fas fa-chart-line me-2 text-primary"></i>Reportes y Estadísticas</h1>

    <ul class="nav nav-tabs" id="report-main-tab" role="tablist">
        <li class="nav-item" role="presentation"><button class="nav-link active" id="tab-reporteador" data-bs-toggle="tab" data-bs-target="#content-reporteador" type="button">Reporteador Personalizado</button></li>
        <li class="nav-item" role="presentation"><button class="nav-link" id="tab-estadisticas" data-bs-toggle="tab" data-bs-target="#content-estadisticas" type="button">Estadísticas Gráficas</button></li>
    </ul>

    <div class="tab-content pt-3">
        <div class="tab-pane fade show active" id="content-reporteador" role="tabpanel">
            <div class="card">
                <div class="card-header"><h5 class="m-0">Generar Reporte Detallado</h5></div>
                <div class="card-body">
                    <form id="report-filters-form" class="row g-3 align-items-end">
                        
                        <div class="col-12 mb-3">
                            <label class="form-label fw-bold">Tipo de Reporte:</label>
                            <div>
                                <div class="form-check form-check-inline">
                                    <input class="form-check-input" type="radio" name="report_type" id="type_entregas" value="entregas" checked>
                                    <label class="form-check-label" for="type_entregas">Entregas (Salidas)</label>
                                </div>
                                <div class="form-check form-check-inline">
                                    <input class="form-check-input" type="radio" name="report_type" id="type_retornos" value="retornos">
                                    <label class="form-check-label" for="type_retornos">Retornos (Llegadas)</label>
                                </div>
                            </div>
                        </div>

                        <div class="col-md-6 col-lg-3"><label for="start_date">Fecha Desde:</label><input type="date" id="start_date" name="start_date" class="form-control"></div>
                        <div class="col-md-6 col-lg-3"><label for="end_date">Fecha Hasta:</label><input type="date" id="end_date" name="end_date" class="form-control"></div>
                        <div class="col-md-4 col-lg-3"><label for="make">Marca:</label><select id="make" name="make" class="form-select"><option value="">Todas</option></select></div>
                        <div class="col-md-4 col-lg-3"><label for="model">Modelo:</label><select id="model" name="model" class="form-select" disabled><option value="">Todos</option></select></div>
                        
                        <div class="col-md-4 col-lg-2">
                            <label for="year">Año Vehículo:</label>
                            <input type="number" id="year" name="year" class="form-control" placeholder="Ej: 2024">
                        </div>

                        <div class="col-md-4 col-lg-2"><label for="class">Clase:</label><select id="class" name="class" class="form-select"><option value="">Todas</option></select></div>
                        <div class="col-md-4 col-lg-2"><label for="fueltype">Combustible:</label><select id="fueltype" name="fueltype" class="form-select"><option value="">Todos</option><option value="Gasoline">Gasolina</option><option value="Diesel">Diesel</option></select></div>
                        <div class="col-lg-12 d-grid mt-3"><button type="submit" class="btn btn-primary">Generar Reporte</button></div>
                    </form>
                </div>
            </div>
            <div class="card mt-4">
                <div class="card-header"><h5 class="m-0">Resultados</h5></div>
                <div class="card-body">
                    <div class="table-responsive"><table id="report-results-table" class="table table-hover" style="width:100%;"><thead><tr><th>Resv #</th><th>Cliente</th><th>Vehículo</th><th>Año</th><th>Desde</th><th>Hasta</th><th>Combustible</th><th>Total</th></tr></thead><tbody></tbody></table></div>
                </div>
            </div>
        </div>

        <!-- ========================================================== -->
        <!-- CORRECCIÓN: Contenido de la pestaña de gráficos restaurado -->
        <!-- ========================================================== -->
       

        <!-- ========================================================== -->
        <!-- PESTAÑA DE ESTADÍSTICAS AHORA CON SUB-PESTAÑAS            -->
        <!-- ========================================================== -->
        <div class="tab-pane fade" id="content-estadisticas" role="tabpanel">
            <ul class="nav nav-pills mb-3">
                <li class="nav-item">
                    <a class="nav-link active" data-bs-toggle="tab" href="#stats-sales">Ventas Anuales</a>
                </li>
                <li class="nav-item">
                    <a class="nav-link" data-bs-toggle="tab" href="#stats-most-rented">Vehículos Más Rentados</a>
                </li>
                <!-- NUEVA PESTAÑA -->
                <li class="nav-item"><a class="nav-link" data-bs-toggle="tab" href="#stats-most-rented-class">Por Clase</a></li>
                <li class="nav-item"><a class="nav-link" data-bs-toggle="tab" href="#stats-by-department">Por Departamento</a></li>
                <li class="nav-item"><a class="nav-link" data-bs-toggle="tab" href="#stats-by-month-country">Por Mes y País</a></li><li class="nav-item"><a class="nav-link" data-bs-toggle="tab" href="#stats-geographical">Análisis Geográfico</a></li>
        
            </ul>

            <div class="tab-content">
                <!-- Sub-Pestaña 1: Gráfico de Ventas Anuales (existente) -->
                <div class="tab-pane fade show active" id="stats-sales">
                    <div class="card">
                        <div class="card-header"><h5 class="m-0">Comparativo de Ventas Anual</h5></div>
                        <div class="card-body">
                            <form id="report-stats-form" class="row g-3 align-items-end">
                                <div class="col-md-4"><label class="form-label">Año a Comparar:</label><input type="number" class="form-control" id="comparison_year" name="year" value="<?= date('Y') ?>"></div>
                                <div class="col-md-4"><button type="submit" class="btn btn-primary">Generar Gráfico</button></div>
                            </form>
                            <hr>
                            <div class="chart-container" style="position: relative; height:40vh; width:100%;"><canvas id="salesComparisonChart"></canvas></div>
                        </div>
                    </div>
                </div>

                <!-- Sub-Pestaña 2: Vehículos Más Rentados (nueva) -->
                <div class="tab-pane fade" id="stats-most-rented">
                     <div class="card">
                        <div class="card-header"><h5 class="m-0">Top Vehículos Más Rentados por Modelo</h5></div>
                        <div class="card-body">
                            <form id="report-vehicles-form" class="row g-3 align-items-end">
                                <div class="col-md-4">
                                    <label for="rented_year" class="form-label">Escribir Año:</label>
                                    <!-- CORRECCIÓN: Cambiado de select a input de texto -->
                                    <input type="number" class="form-control" id="rented_year" name="year" value="<?= date('Y') ?>">
                                </div>
                                <div class="col-md-4">
                                    <button type="submit" class="btn btn-primary">Generar Gráfico</button>
                                </div>
                            </form>
                            <hr>
                           <div class="chart-container" style="position: relative; width:100%;">
                                <canvas id="mostRentedChart"></canvas>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="tab-pane fade" id="stats-most-rented-class">
                     <div class="card">
                        <div class="card-header"><h5 class="m-0">Vehículos Más Rentados por Clase</h5></div>
                        <div class="card-body">
                            <form id="report-class-form" class="row g-3 align-items-end">
                                <div class="col-md-4">
                                    <label for="rented_year_class" class="form-label">Escribir Año:</label>
                                    <input type="number" class="form-control" id="rented_year_class" name="year" value="<?= date('Y') ?>">
                                </div>
                                <div class="col-md-4">
                                    <button type="submit" class="btn btn-primary">Generar Gráfico</button>
                                </div>
                            </form>
                            <hr>
                            <div class="chart-container" style="position: relative; height:40vh; width:100%;">
                                <canvas id="mostRentedByClassChart"></canvas>
                            </div>
                        </div>
                    </div>
                </div>

                 <!-- NUEVO: Gráfico de Rentas por Departamento -->
                <div class="tab-pane fade" id="stats-by-department">
                     <div class="card">
                        <div class="card-header"><h5 class="m-0">Total de Rentas por Departamento</h5></div>
                        <div class="card-body">
                            <form id="report-department-form" class="row g-3 align-items-end">
                                <div class="col-md-4">
                                    <label for="rented_year_department" class="form-label">Escribir Año:</label>
                                    <input type="number" class="form-control" id="rented_year_department" name="year" value="<?= date('Y') ?>">
                                </div>
                                <div class="col-md-4">
                                    <button type="submit" class="btn btn-primary">Generar Gráfico</button>
                                </div>
                            </form>
                            <hr>
                            <div class="chart-container" style="position: relative; height:50vh; width:100%;">
                                <canvas id="rentalsByDepartmentChart"></canvas>
                            </div>
                        </div>
                    </div>
                </div>
                <!-- NUEVO: Gráfico de Rentas por Mes y País/Departamento -->
                <div class="tab-pane fade" id="stats-by-month-country">
                     <div class="card">
                        <div class="card-header"><h5 class="m-0">Rentas Mensuales por País/Departamento</h5></div>
                        <div class="card-body">
                            <form id="report-month-country-form" class="row g-3 align-items-end">
                                <div class="col-md-4">
                                    <label for="rented_year_month_country" class="form-label">Escribir Año:</label>
                                    <input type="number" class="form-control" id="rented_year_month_country" name="year" value="<?= date('Y') ?>">
                                </div>
                                <div class="col-md-4">
                                    <button type="submit" class="btn btn-primary">Generar Gráfico</button>
                                </div>
                            </form>
                            <hr>
                            <div class="chart-container" style="position: relative; height:50vh; width:100%;">
                                <canvas id="rentalsByMonthCountryChart"></canvas>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="tab-pane fade" id="stats-geographical">
                     <div class="card">
                        <div class="card-header"><h5 class="m-0">Distribución de Rentas por País</h5></div>
                        <div class="card-body">
                            <form id="report-geo-form" class="row g-3 align-items-end">
                                <div class="col-md-4">
                                    <label for="geo_year" class="form-label">Escribir Año:</label>
                                    <input type="number" class="form-control" id="geo_year" name="year" value="<?= date('Y') ?>">
                                </div>
                                <div class="col-md-4"><button type="submit" class="btn btn-primary">Generar Análisis</button></div>
                            </form>
                            <hr>
                            <div class="row">
                                <div class="col-md-5">
                                    <div class="chart-container" style="position: relative; height:40vh; width:100%;">
                                        <canvas id="rentalsByCountryChart"></canvas>
                                    </div>
                                </div>
                                <div class="col-md-7">
                                    <!-- Contenedor para el detalle que aparecerá al hacer clic -->
                                    <div id="geo-drilldown-container" style="display: none;">
                                        <h5 id="drilldown-title"></h5>
                                        <div class="chart-container" style="position: relative; height:30vh; width:100%; margin-bottom: 1rem;">
                                            <canvas id="rentalsByStateChart"></canvas>
                                        </div>
                                        <h6>Top Vehículos en esta Región:</h6>
                                        <div class="table-responsive">
                                            <table class="table table-sm table-bordered">
                                                <thead><tr><th>Vehículo</th><th># Rentas</th></tr></thead>
                                                <tbody id="top-vehicles-table-body"></tbody>
                                            </table>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
                
            </div>
        </div>
    </div>
</main>
