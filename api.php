<?php

use GTS\Api\Controller\Api\Verify;
use GTS\Api\Utils\Config;
use GTS\Api\Utils\Validate;
use Psr\Http\Message\ResponseInterface as Response;
use Psr\Http\Message\ServerRequestInterface as Request;
use Psr\Log\LoggerInterface;
use Slim\Factory\AppFactory;
use GTS\Api\Controller\Api\BookingController;

require __DIR__ . '/vendor/autoload.php';
require __DIR__ . "/config/bootstrap.php";

$environment = Config::getConfig()->environment ?? 'development';

if ($environment !== 'development') {
    if (!isset($_SERVER['HTTPS'])) {
        header('HTTP/1.0 403 Forbidden');
        die;
    }
}

$app = AppFactory::create();

$customErrorHandler = function (
    Request $request,
    Throwable $exception,
    bool $displayErrorDetails,
    bool $logErrors,
    bool $logErrorDetails,
    ?LoggerInterface $logger = null
) use ($app) {
    $logger->error($exception->getMessage());

    $payload = [
        'error' => $exception->getMessage(),
        'code' => $exception->getCode()
    ];

    $response = $app->getResponseFactory()->createResponse();
    $response->getBody()->write(
        json_encode($payload, JSON_UNESCAPED_UNICODE)
    );

    return $response;
};

$errorMiddleware = $app->addErrorMiddleware($environment === 'development', true, true);
$errorMiddleware->setDefaultErrorHandler($customErrorHandler);
$app->addBodyParsingMiddleware();

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

$app->post('/api.php/verify', function (Request $request, Response $response) {
    $parsedBody = (object)$request->getParsedBody();

    if (empty($parsedBody->token)) {
        throw new Exception('Invalid parameters', 400);
    }

    $controller = new Verify($parsedBody->token);
    $challengePassed = $controller->verify();

    $response->getBody()->write(json_encode(['verified' => $challengePassed]));
    return $response;
});

$app->post('/api.php/book', function (Request $request, Response $response) {
    $parsedBody = (object)$request->getParsedBody();
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