<!DOCTYPE html>
<html lang="en">

<head>
  <meta charset="UTF-8" />
  <meta http-equiv="X-UA-Compatible" content="IE=edge" />
  <meta name="viewport" content="width=device-width, initial-scale=1.0" />
  <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/twitter-bootstrap/4.6.0/css/bootstrap.min.css" />
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.0.2/dist/css/bootstrap.min.css" rel="stylesheet" integrity="sha384-EVSTQN3/azprG1Anm3QDgpJLIm9Nao0Yz1ztcQTwFspd3yD65VohhpuuCOmLASjC" crossorigin="anonymous">
  <link rel="stylesheet" href="./public/style.css">
  <title>Web</title>
</head>

<body><div>
  <img class="headerimg" src="https://i.postimg.cc/wT3TQS3V/header-image2.jpg" width="100%" height="100" alt="">
  
  
  <div class="top-right">
  <?php if($params['isAuthorized']): ?>
  <form action="/logout" method="POST">
  <button type="submit" class="btn btn-sm btn-danger m-2">Kijelentkezés</button>
        </form></div>
        <?php endif; ?>
      </div>
  
  <div class="hamburger-menu">
    <input id="menu__toggle" type="checkbox" />
    <label class="menu__btn" for="menu__toggle">
      <span></span>
    </label>
    <?php echo $params["content"] ?>
    <ul class="menu__box">
      <li><a class="menu__item"  href="/">Főoldal</a></li>
      <li><a class="menu__item"  href="/termekek">Termékek</a></li>
      <li><a class="menu__item"  href="/penzvalto">Pénzváltó</a></li>
      <li><a class="menu__item"  href="/orszagok">Országok listája</a></li>
      <li><a class="menu__item"  href="/reglog">Bejelentkezés</a></li>
    </ul>
  </div>
  </nav>
  
  <footer>
        <div class="text-center p-3">
            <img class="footerlogo" src="./public/rd.png" alt="footerlogo">
        </div>
    </footer>
</body>

</html>