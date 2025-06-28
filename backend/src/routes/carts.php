<?php
 
use Slim\App;

return function (App $app) {
    // Add to Cart
    $app->get('/api/cart/add/{id}/{productId}/{quantity}', function ($request, $response, $args) {
        require __DIR__ . '/../../db.php';
        $id = $args['id'];
        $productId = $args['productId'];
        $quantity = $args['quantity'];

        $sql = "INSERT INTO cart_items (user_id, product_id, quantity, added_at)
                VALUES (?, ?, ?, NOW())
                ON DUPLICATE KEY UPDATE quantity = quantity + ?";

        $stmt = $pdo->prepare($sql);
        $stmt->execute([$id, $productId, $quantity, $quantity]);

        $response->getBody()->write(json_encode([
            'status' => 'success',
            'message' => 'successfully added to cart',
        ]));
        return $response->withHeader('Content-Type', 'application/json')->withStatus(200);
    });

    // Get Cart Items
    $app->get('/api/cart/user/{id}', function ($request, $response, $args) {
        require __DIR__ . '/../../db.php';
        $id = $args['id'];

        $sql = "SELECT c.*, p.name AS name, p.price AS price, p.image_url, u.fullname AS sellerName
                FROM cart_items c
                JOIN products p ON c.product_id = p.product_id
                JOIN users u ON p.vendor_id = u.id
                WHERE c.user_id = :id";

        $stmt = $pdo->prepare($sql);
        $stmt->execute([':id' => $id]);
        $cartItems = $stmt->fetchAll();

        if (!empty($cartItems)) {
            $response->getBody()->write(json_encode([
                'status' => 'success',
                'data' => $cartItems
            ]));
            return $response->withHeader('Content-Type', 'application/json')->withStatus(200);
        } else {
            $response->getBody()->write(json_encode([
                'status' => 'error',
                'message' => 'cart not found'
            ]));
            return $response->withHeader('Content-Type', 'application/json')->withStatus(404);
        }
    });

    // Update Cart Quantity
    $app->get('/api/cart/update/{id}/{productId}/{quantity}', function ($request, $response, $args) {
        require __DIR__ . '/../../db.php';
        $id = $args['id'];
        $productId = $args['productId'];
        $quantity = $args['quantity'];

        $sql = "UPDATE cart_items SET quantity = ? WHERE user_id = ? AND product_id = ?";

        $stmt = $pdo->prepare($sql);
        $stmt->execute([$quantity, $id, $productId]);

        $response->getBody()->write(json_encode([
            'status' => 'success',
            'message' => 'successfully updated',
        ]));
        return $response->withHeader('Content-Type', 'application/json')->withStatus(200);
    });

    // Remove Item from Cart
    $app->get('/api/cart/remove/{id}/{productId}', function ($request, $response, $args) {
        require __DIR__ . '/../../db.php';
        $id = $args['id'];
        $productId = $args['productId'];

        $sql = "DELETE FROM cart_items WHERE user_id = ? AND product_id = ?";

        $stmt = $pdo->prepare($sql);
        $stmt->execute([$id, $productId]);

        $response->getBody()->write(json_encode([
            'status' => 'success',
            'message' => 'successfully removed from cart',
        ]));
        return $response->withHeader('Content-Type', 'application/json')->withStatus(200);
    });

    $app->get('/api/cart/checkout/{id}', function ($request, $response, $args) {
        require __DIR__ . '/../../db.php';
        $userId = $args['id'];

        try {
            // Get cart items
            $stmt = $pdo->prepare("SELECT c.*, p.price FROM cart_items c JOIN products p ON c.product_id = p.product_id WHERE c.user_id = ?");
            $stmt->execute([$userId]);
            $cartItems = $stmt->fetchAll();

            if (empty($cartItems)) {
                $response->getBody()->write(json_encode([
                    'status' => 'error',
                    'message' => 'Cart is empty'
                ]));
                return $response->withHeader('Content-Type', 'application/json')->withStatus(400);
            }

            // Calculate total
            $total = 0;
            foreach ($cartItems as $item) {
                $total += $item['price'] * $item['quantity'];
            }

            $pdo->beginTransaction();

            // Insert into orders
            $stmt = $pdo->prepare("INSERT INTO orders (order_id, id, total_price, status, created_at) VALUES (UUID(), ?, ?, 'pending', NOW())");
            $stmt->execute([$userId, $total]);
            $orderId = $pdo->lastInsertId();

            // Insert into order_items
            $itemStmt = $pdo->prepare("INSERT INTO order_items (order_id, product_id, quantity, price) VALUES (?, ?, ?, ?)");
            foreach ($cartItems as $item) {
                $itemStmt->execute([$orderId, $item['product_id'], $item['quantity'], $item['price']]);
            }

            // Clear cart
            $del = $pdo->prepare("DELETE FROM cart_items WHERE user_id = ?");
            $del->execute([$userId]);

            $pdo->commit();

            $response->getBody()->write(json_encode([
                'status' => 'success',
                'message' => 'Checkout successful',
                'order_id' => $orderId
            ]));
            return $response->withHeader('Content-Type', 'application/json')->withStatus(200);
        } catch (Exception $e) {
            if ($pdo->inTransaction()) {
                $pdo->rollBack();
            }

            $response->getBody()->write(json_encode([
                'status' => 'error',
                'message' => 'Checkout failed',
                'error' => $e->getMessage()
            ]));
            return $response->withHeader('Content-Type', 'application/json')->withStatus(500);
        }
    }); 

};
