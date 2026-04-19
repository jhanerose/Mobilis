<?php
declare(strict_types=1);

require_once __DIR__ . '/../../app/bootstrap.php';
requireAuth(['admin', 'staff']);

$vehicles = getVehicles(12);

viewBegin('app', appLayoutData('Live tracking', 'tracking'));
?>
<section class="page-content-head">
    <h3>Live tracking overview</h3>
</section>

<section class="content-grid split-grid">
    <article class="card">
        <div class="card-header">
            <h4>Live fleet map</h4>
            <a href="vehicles.php" class="ghost-link">Full map</a>
        </div>
        <div class="map-embed-wrap">
            <iframe
                title="Live fleet map"
                loading="lazy"
                referrerpolicy="no-referrer-when-downgrade"
                src="https://www.google.com/maps?q=Metro+Manila,+Philippines&output=embed"></iframe>
        </div>
    </article>

    <article class="card">
        <div class="card-header">
            <h4>Tracked vehicles</h4>
        </div>
        <ul class="list clean">
            <?php foreach ($vehicles as $vehicle): ?>
                <?php $status = strtolower((string) ($vehicle['status'] ?? 'available')); ?>
                <li>
                    <div>
                        <strong><?= htmlspecialchars((string) $vehicle['name']) ?></strong>
                        <p><?= htmlspecialchars((string) $vehicle['plate']) ?></p>
                    </div>
                    <span class="pill <?= htmlspecialchars($status) ?>"><?= htmlspecialchars(ucfirst($status)) ?></span>
                </li>
            <?php endforeach; ?>
        </ul>
    </article>
</section>
<?php viewEnd();
?>
