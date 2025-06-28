<?php

use Slim\Factory\AppFactory;

require __DIR__ . '/../../vendor/autoload.php';

// Create App
$app = AppFactory::create();

// Enable CORS
$app->add(function ($request, $handler) {
    $response = $handler->handle($request);
    return $response
        ->withHeader('Access-Control-Allow-Origin', '*')
        ->withHeader('Access-Control-Allow-Headers', 'Content-Type, Accept, Origin, Authorization')
        ->withHeader('Access-Control-Allow-Methods', 'GET, POST, PUT, DELETE, OPTIONS');
});


// Define routes
(require __DIR__ . '/../src/routes.php')($app);


$app->run();
