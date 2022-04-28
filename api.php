<?php

use Psr\Http\Message\ResponseInterface as Response;
use Psr\Http\Message\ServerRequestInterface as Request;
use Slim\Factory\AppFactory;
use GTS\Api\Controller\Api\BookingController;

require __DIR__.'/vendor/autoload.php';
require __DIR__ . "/config/bootstrap.php";

//if(!isset($_SERVER['HTTPS'])) {
//    header('HTTP/1.0 403 Forbidden');
//    die;
//}

// Instantiate App
$app = AppFactory::create();

// Add error middleware
$app->addErrorMiddleware(true, true, true);
$app->addBodyParsingMiddleware();

// Add routes
$app->get('/api.php/booking/list', function (Request $request, Response $response) {
    $parsedBody = $request->getQueryParams();
    return $response;
});

$app->post('/api.php/book', function (Request $request, Response $response, $args) {
    $parsedBody = $request->getParsedBody();
    var_dump($parsedBody);
    return $response;
});

$app->run();

//$uri = parse_url($_SERVER['REQUEST_URI'], PHP_URL_PATH);
//$uri = explode( '/', $uri );
//$_POST = json_decode(file_get_contents("php://input"),true);
//
//$inDev = true;
//
//if ($inDev === false) {
//    if ((isset($uri[3]) && $uri[3] != 'booking') || !isset($uri[4])) {
//        header("HTTP/1.1 404 Not Found");
//        exit();
//    }
//} else {
//    if ((isset($uri[2]) && $uri[2] != 'booking') || !isset($uri[3])) {
//        header("HTTP/1.1 404 Not Found");
//        exit();
//    }
//}
//
//require PROJECT_ROOT_PATH . "/Controller/Api/BookingController.php";
//$objFeedController = new BookingController();
//$strMethodName = ($inDev ? $uri[3] : $uri[4]) . 'Action';
//$data = $objFeedController->{$strMethodName}();
//
//return $data;
