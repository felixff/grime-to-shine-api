<?php

use GTS\Api\Controller\Api\Verify;
use GTS\Api\Utils\Config;
use GTS\Api\Utils\Validate;
use Psr\Http\Message\ResponseInterface as Response;
use Psr\Http\Message\ServerRequestInterface as Request;
use Slim\Factory\AppFactory;
use GTS\Api\Controller\Api\BookingController;

require __DIR__ . '/vendor/autoload.php';
require __DIR__ . '/config/bootstrap.php';

$environment = Config::getConfig()->environment ?? 'development';

if ($environment !== 'development') {
    if (!isset($_SERVER['HTTPS'])) {
        header('HTTP/1.0 403 Forbidden');
        die;
    }
}

$app = AppFactory::create();
$app->addErrorMiddleware($environment === 'development', true, true);
$app->addBodyParsingMiddleware();

if ($environment !== 'development') {
    $app->setBasePath('/api');
}

$app->get('/api.php/bookings', function (Request $request, Response $response) {
    $queryParams = $request->getQueryParams();

    if (count($queryParams) < 1) {
        throw new Exception('Invalid parameters', 400);
    }

    $action = $queryParams['a'] . 'Action';
    $controller = new BookingController();

    if (is_callable([$controller, $action])) {
        $response->getBody()->write(json_encode($controller->$action()));
    } else {
        throw new Exception('Invalid action', 400);
    }

    return $response;
});

$app->post('/api.php/book', function (Request $request, Response $response) {
    $parsedBody = (object)$request->getParsedBody();

    $controller = new Verify($parsedBody->token ?? '');
    $challengePassed = $controller->verify();

    if (!$challengePassed) {
        throw new Exception('Bye Bye Bot', 403);
    }

    $event = Validate::validateAndSanitiseParameters('BOOKING_ACTION', $parsedBody);

    $controller = new BookingController();
    $bookedEvent = $controller->bookEventAction($event);

    if (isset($bookedEvent['error'])) {
        throw new Exception('Action failed', 501);
    }

    $response->getBody()->write(json_encode($bookedEvent));
    return $response;
});

$app->run();