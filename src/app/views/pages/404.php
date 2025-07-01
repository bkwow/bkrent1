<?php
// src/app/views/pages/404.php
// Una página de error simple pero profesional

// Si esta página se carga desde el router principal, el header y footer ya estarán incluidos.
// Si se quiere usar de forma independiente, se deberían incluir aquí.
?>
<main>
    <div class="container">
        <div class="row justify-content-center">
            <div class="col-lg-6">
                <div class="text-center mt-4">
                    <img class="mb-4 img-error" src="public/img/illustrations/404-error-with-a-cute-animal.svg" style="max-width: 20rem;"/>
                    <p class="lead">La página que buscas no fue encontrada.</p>
                    <a href="index.php?page=dashboard">
                        <i class="fas fa-arrow-left me-1"></i>
                        Volver al Dashboard
                    </a>
                </div>
            </div>
        </div>
    </div>
</main>