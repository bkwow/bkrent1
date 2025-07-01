<?php
/**
 * dashboard.php
 *
 * Contenido específico de la página del Dashboard.
 * El footer global se carga por separado en footer.php.
 */
?>
<main class="page-content p-4">
    <?php display_flash_messages(); ?>

    <h1 class="page-title"><i class="fas fa-bolt me-2 text-primary"></i>Dashboard</h1>
    <p class="text-muted mb-4">
        <?php 
            $date = new DateTime();
            $formatter = new IntlDateFormatter('es_ES', IntlDateFormatter::FULL, IntlDateFormatter::NONE, null, null, 'EEEE, d \'de\' MMMM \'de\' yyyy');
            echo ucfirst($formatter->format($date));
        ?>
    </p>

    <!-- PESTAÑAS DE NAVEGACIÓN -->
    <div class="nav nav-tabs mb-4 content-tabs" id="dashboard-tabs" role="tablist">
        <?php foreach ($menuCategories as $index => $category): ?>
            <button class="nav-link <?= $index === 0 ? 'active' : '' ?>" id="tab-<?= e($category['idcat']) ?>" data-bs-toggle="tab" data-bs-target="#panel-<?= e($category['idcat']) ?>" type="button" role="tab"><?= e(strtoupper($category['categoria'])) ?></button>
        <?php endforeach; ?>
    </div>

    <!-- PANELES DE CONTENIDO -->
    <div class="tab-content" id="dashboard-tab-content">
        <?php foreach ($menuCategories as $index => $category): 
            $currentCategoryId = $category['idcat'];
            $currentCategoryName = $category['categoria'];
        ?>
            <div class="tab-pane fade <?= $index === 0 ? 'show active' : '' ?>" id="panel-<?= e($currentCategoryId) ?>" role="tabpanel">
               <!--  <div class="p-3 mb-4 bg-light border rounded-2">
                    <h5 class="m-0 fw-light"><i class="<?= e($category['icon_fo'] ?? 'fas fa-folder') ?> me-2 text-primary"></i> <?= e(strtoupper($currentCategoryName)) ?></h5>
                </div> -->
                <div class="row g-4">
                    <?php if (isset($itemsByCategory[$currentCategoryId]) && !empty($itemsByCategory[$currentCategoryId])): ?>
                        <?php foreach ($itemsByCategory[$currentCategoryId] as $item): ?>
                            <div class="col-md-6 col-lg-4">
                                <a href="<?= e($item['link']) ?>" class="module-card">
                                    <div class="card-content">
                                        <div class="icon-title"><i class="<?= e($item['icon_fo'] ?? 'fas fa-file-alt') ?> text-primary"></i><h4><?= e($item['nom_menu']) ?></h4></div>
                                        <p class="text-muted"><?= e($item['detalle']) ?></p>
                                    </div>
                                    <div class="card-image"><img src="public/img/<?= e($item['img'] ?? 'placeholder-image.png') ?>" alt="<?= e($item['nom_menu']) ?>"></div>
                                </a>
                            </div>
                        <?php endforeach; ?>
                    <?php else: ?>
                        <div class="col-12"><p class="text-muted">No hay módulos disponibles en la categoría "<?= e($currentCategoryName) ?>".</p></div>
                    <?php endif; ?>
                </div>
            </div>
        <?php endforeach; ?>
    </div>
</main>