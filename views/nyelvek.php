<h1 class="text-center p-3">
    <?= $params['country']['name'] ?>
</h1>


<div class="text-center btn-group-sm m-2">
<h2 class="p-2">
    Beszélt nyelvek:
</h2>
    <?php foreach ($params['language'] as $lg) : ?>
        <h3> <?= $lg['name'] ?></h3>
        <p>Hivatalos nyelv: <?php
                        if ($lg['isOfficial'] < "1") {
                            echo "Nem";
                        } else {
                            echo "Igen";
                        } ?></p>
        <p>Százalékos arány: <?= $lg['percentage'] . "%" ?></p>
        <br>
        <?php endforeach; ?>
</div>