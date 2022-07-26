<h1 class="text-center p-3">
    <?= $params['country']['name'] ?><br>
    <a href="/nyelvek-megtekintese?id=<?= $params['country']['id'] ?>" class="list-group-item list-group-item-action"> Beszélt nyelvek </a>
</h1>


<div class="text-center btn-group-sm m-2">
<h2>
    Városok:  
</h2>
    <?php foreach ($params['cities'] as $city) : ?>
        <h3><a href="/varos-megtekintese?id=<?= $city['id'] ?>" class="list-group-item list-group-item-action"> <?= $city['name'] ?> </a> </h3>
        <br>
        <?php endforeach; ?>
</div>