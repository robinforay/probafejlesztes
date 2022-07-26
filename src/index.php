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
        '/nyelvek-megtekintese' => 'languagesHandler'
    ],
    "POST" => [
        "/delete-product" => "deleteProductHandler",
        "/termekek" => "createProductHandler",
        "/update-product" => "updatedProductHandler"
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

function homeHandler()
{

    $homeTemplate = compileTemplate('./home.php');
    echo compileTemplate('./wrapper.php', [
        'innerTemplate' => $homeTemplate,
        'activeLink' => '/'
    ]);
};

function notFoundHandler()
{
    echo compileTemplate('./wrapper.php', [
        'innerTemplate' => "Oldal nem található 404",
        'activeLink' => ''
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
        "updatedProductId" => $_GET["szerkesztes"] ?? ""
    ]);

    echo compileTemplate('./wrapper.php', [
        'innerTemplate' => $productlistTemplate,
        'activeLink' => '/termekek',
    ]);
};

function exchangeHandler()
{
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
        'innerTemplate' => $exchangeTemplate,
        'activeLink' => '/penzvalto'
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
    $pdo = getConnection();

    $statement = $pdo->prepare('SELECT * FROM `countries`');
    $statement->execute();
    $countries = $statement->fetchAll(PDO::FETCH_ASSOC);

    $countryTemplate = compileTemplate('./orszagok.php', [
        'countries' => $countries,
        
    ]);
    echo compileTemplate('./wrapper.php', [
        'innerTemplate' => $countryTemplate,
        'activeLink' => '/orszagok'
    ]);
}

function singleCountryHandler()
{
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
        'cities' => $cities]);
        echo compileTemplate('./wrapper.php', [
            'innerTemplate' => $citiesTemplate,
            'activeLink' => '/orszag-megtekintese'
        ]);
}

function singleCityHandler()
{
    $cityId = $_GET['id'] ?? '';
    $pdo = getConnection();
    $statement = $pdo->prepare('SELECT * FROM cities WHERE id = ?');
    $statement->execute([$cityId]);
    $city = $statement->fetch(PDO::FETCH_ASSOC);

    $cityTemplate = compileTemplate('./varos.php', [
        'city' => $city]);
        echo compileTemplate('./wrapper.php', [
            'innerTemplate' => $cityTemplate,
            'activeLink' => '/varos-megtekintese'
        ]);
}

function languagesHandler()
{
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
        'country' => $country]);
        echo compileTemplate('./wrapper.php', [
            'innerTemplate' => $languageTemplate,
            'activeLink' => '/nyelvek-megtekintese'
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


