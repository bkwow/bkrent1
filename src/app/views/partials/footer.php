<?php
/**
 * footer.php
 *
 * Parte final del layout principal.
 * Cierra los divs del layout, añade el footer global y carga los scripts JS.
 */
?>
        </div> <!-- Cierre del contenedor wrapper: #layoutSidenav_content_wrapper -->
    </div> <!-- Cierre del layout principal: #layoutSidenav -->

    <footer class="app-footer p-3 text-center text-muted small border-top bg-light">
        Copyright © SU INVERSIONES S.A. DE C.V. 2025
    </footer>
    
    <!-- Scripts -->
    <script src="https://code.jquery.com/jquery-3.7.1.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>

    
<!-- ... tus scripts de jQuery, Bootstrap, DataTables ... -->
<script src="https://cdn.datatables.net/1.13.7/js/jquery.dataTables.min.js"></script>
<script src="https://cdn.datatables.net/1.13.7/js/dataTables.bootstrap5.min.js"></script>
<script src="https://cdn.datatables.net/responsive/2.5.0/js/dataTables.responsive.min.js"></script>
<script src="https://cdn.datatables.net/responsive/2.5.0/js/responsive.bootstrap5.min.js"></script>

<!-- ========================================================== -->
<!-- NUEVOS: Scripts para los botones de exportación            -->
<!-- ========================================================== -->
<script src="https://cdn.datatables.net/buttons/2.4.2/js/dataTables.buttons.min.js"></script>
<script src="https://cdn.datatables.net/buttons/2.4.2/js/buttons.bootstrap5.min.js"></script>
<script src="https://cdnjs.cloudflare.com/ajax/libs/jszip/3.10.1/jszip.min.js"></script>
<script src="https://cdnjs.cloudflare.com/ajax/libs/pdfmake/0.1.53/pdfmake.min.js"></script>
<script src="https://cdnjs.cloudflare.com/ajax/libs/pdfmake/0.1.53/vfs_fonts.js"></script>
<script src="https://cdn.datatables.net/buttons/2.4.2/js/buttons.html5.min.js"></script>
<script src="https://cdn.datatables.net/buttons/2.4.2/js/buttons.print.min.js"></script>
<script src="https://cdn.datatables.net/buttons/2.4.2/js/buttons.colVis.min.js"></script>

<!-- Tu script principal, que debe ir al final -->
<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
 

<!-- NUEVO: Librería para Gráficos (Chart.js) -->
<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>

<!-- Tu script principal -->
<script src="public/js/scripts.js?version=<?php echo time(); ?>"></script>


</body>
</html>