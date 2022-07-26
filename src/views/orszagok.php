<div class="list-group">
    <?php foreach ($params['countries'] as $country) : ?>
        <a href="/orszag-megtekintese?id=<?= $country['id'] ?>" class="list-group-item list-group-item-action">
            <div class="d-flex w-100 justify-content-between">
                <h5 class="mb-1">
                    <?= $country['name'] ?>
                </h5>
                <small>(<?= $country["continent"] ?>)</small>
            </div>
            <small>Népesség: <?= $country["population"] ?> fő</small>
        </a> 
    <?php endforeach; ?>
</div>