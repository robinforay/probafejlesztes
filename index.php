<?php

use function PHPSTORM_META\override;

$method = $_SERVER['REQUEST_METHOD']; // mindig első metód lekérés
$parsed = parse_url($_SERVER['REQUEST_URI']); // url kiparszolás
$path = $parsed['path']; // parszolás path lekérés


$routes = [  // útvonalválasztó létrehozása
    "GET" => [
        "/" => "homeHandler",
        "/termekek" => "productListHandler",
        "/penzvalto" => "exchangeHandler",
        "/orszagok" => "countryListHandler",
        '/orszag-megtekintese' => 'singleCountryHandler',
        '/varos-megtekintese' => 'singleCityHandler',
        '/nyelvek-megtekintese' => 'languagesHandler',
        '/reglog' => 'reglogHandler'
    ],
    "POST" => [
        "/delete-product" => "deleteProductHandler",
        "/termekek" => "createProductHandler",
        "/update-product" => "updatedProductHandler",
        '/register' => 'registrationHandler',
        '/login' => 'loginHandler',
        '/logout' => 'logoutHandler'
    ]
];

$handlerFunction = $routes[$method][$path] ?? "notFoundHandler"; // handlerfunkció path és method alapján + ha nincs találat notFoundHandler. FONTOS A SORREND 1. METHOD 2. PATH

$safehandlerFunction = function_exists($handlerFunction) ? $handlerFunction : "notFoundHandler";

$safehandlerFunction();

function compileTemplate($filePath, $params = []): string
{
    ob_start();
    require __DIR__ . "/views/" . $filePath;
    return ob_get_clean();
};

function getPathWithId($url) { 
    $parsed = parse_url($url);
    if(!isset($parsed['query'])) {
        return $url;
    }
    $queryParams = []; 
    parse_str($parsed['query'], $queryParams);
    return $parsed['path'] . "?id=" . $queryParams['id'];
}

function homeHandler()
{
    $homeTemplate = compileTemplate('./home.php');
    echo compileTemplate('./wrapper.php', [
        'content' => $homeTemplate,
        'isAuthorized' => isLoggedIn()
    ]);
};

function notFoundHandler()
{   
    if (!isLoggedIn()) {  
        echo compileTemplate("wrapper.php", [ 
            'content' => compileTemplate('login.php', [
                'info' => $_GET['info'] ?? '',
                'isRegistration' => isset($_GET['isRegistration']),
                'url' => getPathWithId($_SERVER['REQUEST_URI']), 
            ]),  
            'isAuthorized' => false,
        ]);
        return;
    }
    echo compileTemplate('./wrapper.php', [
        'content' => "Oldal nem található 404",
        'isAuthorized' => true,
    ]);
};

function productListHandler()
{

    $content = file_get_contents("./public/products.json");
    $products = json_decode($content, true);
    $isSucces = isset($_GET['siker']);
    $isDeleted = isset($_GET['torles']);
    $szerkeszt = isset($_GET['szerkeszt']);

    $productlistTemplate = compileTemplate('./termekek.php', [
        'isSucces' => $isSucces,
        'products' => $products,
        'isDeleted' => $isDeleted,
        'szerkeszt' => $szerkeszt,
        "updatedProductId" => $_GET["szerkesztes"] ?? "",
    ]);

    echo compileTemplate('./wrapper.php', [
        'content' => $productlistTemplate,
        'isAuthorized' => isLoggedIn()
    ]);
};

function exchangeHandler()
{
    if (!isLoggedIn()) {  
        echo compileTemplate("wrapper.php", [ 
            'content' => compileTemplate('login.php', [
                'info' => $_GET['info'] ?? '',
                'isRegistration' => isset($_GET['isRegistration']),
                'url' => getPathWithId($_SERVER['REQUEST_URI']), 
            ]),  
            'isAuthorized' => false,
        ]);
        return;
    }

    $value = $_GET['mennyit'] ?? 1;
    $sourceCurrency = $_GET['mirol'] ?? "USD";
    $targetCurrency = $_get['mire'] ?? "HUF";
    $content = file_get_contents('https://kodbazis.hu/api/exchangerates?base=' . $sourceCurrency);
    $decodedContent = json_decode($content, true);
    $currencies = json_decode(file_get_contents('./public/currencies.json'), true);
    $vegeredmeny = $decodedContent['rates'][$targetCurrency] * $value;

    $exchangeTemplate = compileTemplate('./penzvalto.php', [
        'value' => $value,
        'sourceCurrency' => $sourceCurrency,
        'targetCurrency' => $targetCurrency,
        'vegeredmeny' => $vegeredmeny,
        'currencies' => $currencies
    ]);

    echo compileTemplate('./wrapper.php', [
        'content' => $exchangeTemplate,
        'isAuthorized' => true,
    ]);
};

function createProductHandler()
{
    $newProduct = [
        "id" => uniqid(),
        "name" => $_POST['name'],
        "price" => $_POST['price'],
        "imageURL" => $_POST['imageURL']
    ];
    $content = file_get_contents("./public/products.json");
    $products = json_decode($content, true);
    array_push($products, $newProduct);
    $json = json_encode($products);
    file_put_contents("./public/products.json", $json);

    header("Location: ./termekek?siker=1");
};

function deleteProductHandler()
{
    $content = file_get_contents("./public/products.json");
    $products = json_decode($content, true);
    $deletedProductId = $_GET["id"] ?? "";
    $foundProductIndex = -1;

    foreach ($products as $index => $product) {
        if ($product['id'] === $deletedProductId) {
            $foundProductIndex = $index;
        };
    };

    if ($foundProductIndex === -1) {
        header("Location: /termekek");
        return;
    };

    array_splice($products, $foundProductIndex, 1);
    $json = json_encode($products);
    file_put_contents("./public/products.json", $json);
    header("Location: /termekek?torles=1");
}

function updatedProductHandler()
{
    $updatedProductId = $_GET["id"] ?? "";
    $products = json_decode(file_get_contents("./public/products.json"), true);

    $foundProductIndex = -1;
    foreach ($products as $index => $product) {
        if ($product["id"] === $updatedProductId) { // Figyelem! Átmásoláskor a változót is át kell nevezni $deletedProductId-ról $updatedProductId-ra!  
            $foundProductIndex = $index;
            break;
        }
    }

    if ($foundProductIndex === -1) {
        header("Location: /termekek");
        return;
    }

    $updatedProduct = [
        "id" => $updatedProductId,
        "name" => filter_var($_POST["name"]),
        "price" => (int)$_POST["price"],
        "imageURL" => $_POST["imageURL"]
    ];

    $products[$foundProductIndex] = $updatedProduct;

    file_put_contents('./public/products.json', json_encode($products));
    header("Location: /termekek?szerkeszt=1");
}

function countryListHandler()
{
    if (!isLoggedIn()) {  
        echo compileTemplate("wrapper.php", [ 
            'content' => compileTemplate('login.php', [
                'info' => $_GET['info'] ?? '',
                'isRegistration' => isset($_GET['isRegistration']),
                'url' => getPathWithId($_SERVER['REQUEST_URI']), 
            ]),  
            'isAuthorized' => false,
        ]);
        return;
    }
    $pdo = getConnection();

    $statement = $pdo->prepare('SELECT * FROM `countries`');
    $statement->execute();
    $countries = $statement->fetchAll(PDO::FETCH_ASSOC);

    $countryTemplate = compileTemplate('./orszagok.php', [
        'countries' => $countries,

    ]);
    echo compileTemplate('./wrapper.php', [
        'content' => $countryTemplate,
        'isAuthorized' => true,
    ]);
}

function singleCountryHandler()
{   
    if (!isLoggedIn()) {  
        echo compileTemplate("wrapper.php", [ 
            'content' => compileTemplate('login.php', [
                'info' => $_GET['info'] ?? '',
                'isRegistration' => isset($_GET['isRegistration']),
                'url' => getPathWithId($_SERVER['REQUEST_URI']), 
            ]),  
            'isAuthorized' => false,
        ]);
        return;
    }
    $countryId = $_GET['id'] ?? '';
    $pdo = getConnection();
    $statement = $pdo->prepare('SELECT * FROM countries WHERE id = ?');
    $statement->execute([$countryId]);
    $country = $statement->fetch(PDO::FETCH_ASSOC);

    $statement = $pdo->prepare('SELECT * FROM `cities` WHERE countryId = ?');
    $statement->execute([$countryId]);
    $cities = $statement->fetchAll(PDO::FETCH_ASSOC);

    $citiesTemplate = compileTemplate('./varosok.php', [
        'country' => $country,
        'cities' => $cities
    ]);
    echo compileTemplate('./wrapper.php', [
        'content' => $citiesTemplate,
        'isAuthorized' => true,
    ]);
}

function singleCityHandler()
{
    if (!isLoggedIn()) {  
        echo compileTemplate("wrapper.php", [ 
            'content' => compileTemplate('login.php', [
                'info' => $_GET['info'] ?? '',
                'isRegistration' => isset($_GET['isRegistration']),
                'url' => getPathWithId($_SERVER['REQUEST_URI']), 
            ]),  
            'isAuthorized' => false,
        ]);
        return;
    }
    $cityId = $_GET['id'] ?? '';
    $pdo = getConnection();
    $statement = $pdo->prepare('SELECT * FROM cities WHERE id = ?');
    $statement->execute([$cityId]);
    $city = $statement->fetch(PDO::FETCH_ASSOC);

    $cityTemplate = compileTemplate('./varos.php', [
        'city' => $city
    ]);
    echo compileTemplate('./wrapper.php', [
        'content' => $cityTemplate,
        'isAuthorized' => true,
    ]);
}

function languagesHandler()
{
    if (!isLoggedIn()) {  
        echo compileTemplate("wrapper.php", [ 
            'content' => compileTemplate('login.php', [
                'info' => $_GET['info'] ?? '',
                'isRegistration' => isset($_GET['isRegistration']),
                'url' => getPathWithId($_SERVER['REQUEST_URI']), 
            ]),  
            'isAuthorized' => false,
        ]);
        return;
    }
    $languageId = $_GET['id'] ?? '';
    $pdo = getConnection();

    $statement = $pdo->prepare('SELECT * FROM countries WHERE id = ?');
    $statement->execute([$languageId]);
    $country = $statement->fetch(PDO::FETCH_ASSOC);


    $statement = $pdo->prepare('SELECT * FROM `countryLanguages`
    JOIN `languages` ON languageId = languages.id
    WHERE countryId = ?');
    $statement->execute([$languageId]);
    $language = $statement->fetchAll(PDO::FETCH_ASSOC);


    $languageTemplate = compileTemplate('./nyelvek.php', [
        'language' => $language,
        'country' => $country
    ]);
    echo compileTemplate('./wrapper.php', [
        'content' => $languageTemplate,
        'isAuthorized' => true,
    ]);
}

function getConnection()
{
    return new PDO(
        'mysql:host=' . $_SERVER['DB_HOST'] . ';dbname=' . $_SERVER['DB_NAME'],
        $_SERVER['DB_USER'],
        $_SERVER['DB_PASSWORD']
    );
}

function registrationHandler()
{

    $pdo = getConnection();
    $statment = $pdo->prepare(
        "INSERT INTO `users` (`email`, `password`, `createdAt`) 
        VALUES (?, ?, ?);"
    );
    $statment->execute([
        $_POST["email"],
        password_hash($_POST["password"], PASSWORD_DEFAULT),
        time()
    ]);

    header('Location: ./reglog' . '&info=registrationSuccessful'); 
}

function loginHandler()
{
    $pdo = getConnection();
    $statement = $pdo->prepare("SELECT * FROM users WHERE email = ?");
    $statement->execute([$_POST["email"]]);
    $user = $statement->fetch(PDO::FETCH_ASSOC);
    
    if (!$user) {
        header('Location: ./reglog' . '&info=invalidCredentials');  
        return;
    }

    $isVerified = password_verify($_POST['password'], $user["password"]);

    if (!$isVerified) {
        header('Location: ./reglog' . '&info=invalidCredentials'); 
        return;
    }

    session_start();
    $_SESSION['userId'] = $user['id'];
    header('Location: ' . getPathWithId($_SERVER['HTTP_REFERER']));
}

function logoutHandler() 
{    
    if(!isset($_SESSION)) 
    { 
        session_start(); 
    }
    else
    {
        session_destroy();
        session_start(); 
    }
    $params = session_get_cookie_params(); 
    setcookie(session_name(),  '', 0, $params['path'], $params['domain'], $params['secure'], isset($params['httponly']));
    session_destroy(); 
    header('Location: ' . $_SERVER['HTTP_REFERER']);   
}

function isLoggedIn(): bool
{
    if (!isset($_COOKIE[session_name()])) {
        return false;
    }

    if(!isset($_SESSION)) 
    { 
        session_start(); 
    }

    if (!isset($_SESSION['userId'])) {
        return false;
    }

    return true;
}

function reglogHandler()
{
    echo compileTemplate("wrapper.php", [ 
        'content' => compileTemplate('login.php', [
            'info' => $_GET['info'] ?? '',
            'url' => getPathWithId($_SERVER['REQUEST_URI']), 
            'isRegistration' => isset($_GET['isRegistration']),
        ]),  
        'isAuthorized' => isLoggedIn()]);
}