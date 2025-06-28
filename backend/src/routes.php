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

    // Payment routes
    $app->get('/api/cart-checkout/{id}', function ($request, $response, $args) {
        require __DIR__ . '/../db.php';
        $id = $args['id'];
        $sql = "SELECT
                  c.product_id AS product_id,
                  p.name AS name,
                  c.quantity AS quantity,
                  p.price AS price
                FROM cart_items c
                JOIN products p ON p.product_id = c.product_id
                WHERE c.user_id =  ?";

        $stmt = $pdo->prepare($sql);
        $stmt->execute([$id]);
        $check = $stmt->fetchAll();
        $totalPrice = 5.99;
        $insertOrder = $pdo->prepare("INSERT INTO orders (id, total_price, payment_method,created_at) VALUES (?, ?, ?, NOW())");
        $insertOrder->execute([$id, 0, 'Online']);
        $orderId = $pdo->lastInsertId();

        foreach ($check as $item) {
            $productId = $item['product_id'];
            $quantity = $item['quantity'];
            $price = $item['price'];
            $totalPrice += $price * $quantity;

            $insertOrder = $pdo->prepare("INSERT INTO order_items (order_id, product_id, quantity,price, status) VALUES (?, ?, ?, ?, ?)");
            $insertOrder->execute([$orderId, $productId, $quantity, $price * $quantity, 'pending']);
            // Remove from cart
            $deleteCart = $pdo->prepare("DELETE FROM cart_items WHERE user_id = ? AND product_id = ?");
            $deleteCart->execute([$id, $productId]);
        }
        // update order total price
        $updateOrder = $pdo->prepare("UPDATE orders SET total_price = ? WHERE order_id = ?");
        $updateOrder->execute([$totalPrice, $orderId]);


        $response->getBody()->write(json_encode([
            'status' => 'success',
            'message' => 'successfully removed from cart',
        ]));
        return $response->withHeader('Content-Type', 'application/json')->withStatus(200);
    });

    //Order History route
    $app->get('/api/order-history/{id}', function ($request, $response, $args) {
        require __DIR__ . '/../db.php';
        $id = $args['id'];
        $sql = "SELECT
                  o.order_id AS id,
                  oi.status AS status,
                  p.name AS productName,
                  p.image_url AS productImage,
                  oi.quantity AS quantity,
                  oi.price AS total ,
                  o.created_at AS date
                FROM orders o
                JOIN order_items oi ON oi.order_id = o.order_id
                JOIN products p ON p.product_id = oi.product_id
                WHERE o.id = ?";

        $stmt = $pdo->prepare($sql);
        $stmt->execute([$id]);
        $orders = $stmt->fetchAll();

        if ($orders) {
            $response->getBody()->write(json_encode([
                'status' => 'success',
                'data' => $orders
            ]));
            return $response->withHeader('Content-Type', 'application/json')->withStatus(200);
        } else {
            return $response->withHeader('Content-Type', 'application/json')
                ->withStatus(404)
                ->write(json_encode(['status' => 'error', 'message' => 'No orders found']));
        }
    });
};
