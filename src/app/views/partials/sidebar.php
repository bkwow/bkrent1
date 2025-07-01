<?php
/**
 * sidebar.php
 * 
 * Genera el menú de navegación lateral de forma dinámica.
 * Este archivo se incluye dentro de #layoutSidenav.
 */
?>
<aside id="sidebar">
    <ul class="nav flex-column pt-3" id="sidebarMenu">
        <li class="sidebar-heading">Control</li>
        <li class="nav-item">
            <a class="nav-link active" href="index.php?page=dashboard">
                <i class="fas fa-th-large menu-icon"></i> Panel de Control
            </a>
        </li>
        
        <li class="sidebar-heading">Módulos</li>

        <?php 
        foreach ($menuCategories as $category):
            $categoryId = $category['idcat'];
            $categoryName = $category['categoria'];
            $categoryIcon = $category['icon_fo'] ?? 'fas fa-folder';
            $hasSubItems = isset($itemsByCategory[$categoryId]) && !empty($itemsByCategory[$categoryId]);
        ?>
            <li class="nav-item">
                <?php if ($hasSubItems): ?>
                    <a class="nav-link" data-bs-toggle="collapse" href="#collapse-<?= e($categoryId) ?>" role="button" aria-expanded="false" aria-controls="collapse-<?= e($categoryId) ?>">
                        <i class="<?= e($categoryIcon) ?> menu-icon"></i> 
                        <?= e($categoryName) ?>
                        <i class="fas fa-chevron-down float-end mt-1 small"></i>
                    </a>
                    <div class="collapse" id="collapse-<?= e($categoryId) ?>" data-bs-parent="#sidebarMenu">
                        <?php foreach ($itemsByCategory[$categoryId] as $item): ?>
                            <a class="nav-link" href="<?= e($item['link']) ?>">
                                <?= e($item['nom_menu']) ?>
                            </a>
                        <?php endforeach; ?>
                    </div>
                <?php else: ?>
                    <a class="nav-link" href="index.php?page=<?= strtolower(e($categoryName)) ?>">
                        <i class="<?= e($categoryIcon) ?> menu-icon"></i> 
                        <?= e($categoryName) ?>
                    </a>
                <?php endif; ?>
            </li>
        <?php endforeach; ?>
    </ul>
</aside>