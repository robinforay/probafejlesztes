<div class="text-center btn-group-sm m-2">
<h1 class="text-center p-3"><?= $params['city']['name'] ?></h1>
        <h4>Populáció: <?= $params['city']['population'] ?></h4>
        <h5>Főváros: <?php
                        if ($params['city']['isCapital'] < "1") {
                            echo "Nem";
                        } else {
                            echo "Igen";
                        } ?><br><br>
</div>