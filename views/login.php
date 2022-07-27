<div class="card p-3 m-2">
    <div class="jumbotron text-center">
        <h2>A tartalom megtekintéséhez bejelentkezés szükséges</h2>
    </div>

<?php if ($params['sikerreg']) : ?>
            <div class="alert alert-success">
                Regisztráció sikeres!
            </div>
        <?php endif ?>
        <?php if ($params['sikerlog']) : ?>
            <div class="alert alert-success">
                Sikeres bejelentkezés!
            </div>
        <?php endif ?>
<form action="/login" method="POST">
    <label class="w-100">
        Email cím:
        <input class="form-control" type="email" name="email">
    </label>

    <label class="w-100">
        Jelszó:
        <input class="form-control" type="password" name="password">
    </label>
    <button type="submit" class="btn btn-primary form-control">Bejelentkezés</button>
</form>

<form action="/register" method="POST"> 
<label class="w-100">
        Email cím:
        <input class="form-control" type="email" name="email">
    </label>

    <label class="w-100">
        Jelszó:
        <input class="form-control" type="password" name="password">
    </label>
    <button type="submit" class="btn btn-success form-control">Regisztráció</button>
</form>
</div>