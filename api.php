<?php

use GTS\Api\Controller\Api\BookingController;

require __DIR__.'/vendor/autoload.php';
require __DIR__ . "/config/bootstrap.php";

$uri = parse_url($_SERVER['REQUEST_URI'], PHP_URL_PATH);
$uri = explode( '/', $uri );
$_POST = json_decode(file_get_contents("php://input"),true);

$inDev = false;

if ($inDev === false) {
    if ((isset($uri[3]) && $uri[3] != 'booking') || !isset($uri[4])) {
        header("HTTP/1.1 404 Not Found");
        exit();
    }
} else {
    if ((isset($uri[2]) && $uri[2] != 'booking') || !isset($uri[3])) {
        header("HTTP/1.1 404 Not Found");
        exit();
    }
}

require PROJECT_ROOT_PATH . "/Controller/Api/BookingController.php";
$objFeedController = new BookingController();
$strMethodName = ($inDev ? $uri[3] : $uri[4]) . 'Action';
$data = $objFeedController->{$strMethodName}();

return $data;