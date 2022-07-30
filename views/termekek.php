    <div class="card p-3 m-2">
        <?php if ($params['isSucces']) : ?>
            <div class="alert alert-success">
                Termék létrehozása sikeres!
            </div>
        <?php endif ?>
        <?php if ($params['szerkeszt']) : ?>
            <div class="alert alert-success">
                Termék szerkesztése sikeres!
            </div>
        <?php endif ?>
        <?php if ($params['isDeleted']) : ?>
            <div class="alert alert-danger">
                Termék sikeresen kitörölve!
            </div>
        <?php endif ?>
        <?php if (isLoggedIn()) : ?><form action="/termekek" method="POST">
            <input type="text" name="name" placeholder="Név" />
            <input type="number" name="price" placeholder="Ár" />
            <input type="text" name="imageURL" placeholder="KépLink" />
            <button class="btn btn-success" type="submit">Küldés</button>
        </form>
        <?php endif; ?>
        <?php foreach ($params['products'] as $product) : ?>
            <h3><?php echo $product['name'] ?></h3>
            <p><?php echo "<img src=" . $product['imageURL'] . " width='50px' />" ?>
            <p><?php echo $product['price'] . " Ft" ?></p>
            
            <?php if ($params["updatedProductId"] === $product["id"]) : ?>

                <form class="form-inline form-group" action="/update-product?id=<?php echo $product["id"] ?>" method="post">
                    <input class="form-control mr-2" type="text" name="name" placeholder="Név" value="<?php echo $product["name"] ?>" />
                    <input class="form-control mr-2" type="number" name="price" placeholder="Ár" value="<?php echo $product["price"] ?>" />
                    <input class="form-control mr-2" type="text" name="imageURL" placeholder="Kép Link" value="<?php echo $product["imageURL"] ?>" />

                    <a href="/termekek">
                        <button type="button" class="btn btn-outline-primary mr-2">Vissza</button>
                    </a>

                    <button type="submit" class="btn btn-success">Küldés</button>
                </form>
                
                
            <?php else : ?>
                <div class="btn-group">
                <?php if (isLoggedIn()) : ?>
                    <a href="/termekek?szerkesztes=<?php echo $product["id"] ?>">
                        <button class="btn btn-warning mr-2">Szerkesztés</button>
                    </a>

                    <form action="/delete-product?id=<?php echo $product["id"] ?>" method="post">
                        <button type="submit" class="btn btn-danger">Törlés</button>
                    </form>
                    <?php endif; ?>
                </div>
            <?php endif; ?>
            <hr>


        <?php endforeach; ?>
    </div>