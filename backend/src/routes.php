<?php

use Psr\Http\Message\ResponseInterface as Response;
use Psr\Http\Message\ServerRequestInterface as Request;
use Slim\App;

require __DIR__ . '/../db.php';

function moveUploadedFile($directory, $uploadedFile)
{
    $extension = pathinfo($uploadedFile->getClientFilename(), PATHINFO_EXTENSION);
    $basename = bin2hex(random_bytes(8));
    $filename = sprintf('%s.%s', $basename, $extension);

    file_put_contents(__DIR__ . '/../log.txt', 'Writable? ' . (is_writable($directory) ? 'YES' : 'NO') . PHP_EOL, FILE_APPEND);
    $uploadedFile->moveTo($directory . DIRECTORY_SEPARATOR . $filename);

    return $filename;
}

return function (App $app) {
    (require __DIR__ . '/routes/auth.php')($app);
    (require __DIR__ . '/routes/products.php')($app);
    (require __DIR__ . '/routes/carts.php')($app);
    (require __DIR__ . '/routes/orders.php')($app);

    // Hello route
    $app->get('/api/hello', function (Request $request, Response $response) {
        $response->getBody()->write(json_encode(['message' => 'Hello from Slim!']));
        return $response->withHeader('Content-Type', 'application/json');
    });

    // Test route
    $app->get('/api/test', function (Request $request, Response $response) {
        $data = ["status" => "success"];
        $response->getBody()->write(json_encode($data));
        return $response->withHeader('Content-Type', 'application/json');
    });
};
