<?php

use Slim\App;

return function (App $app) {
    $app->get('/api/order/history/vendor/{vendor_id}', function ($request, $response, $args) {
        require __DIR__ . '/../../db.php';
        $vendor_id = $args['vendor_id'];

        $sql = "SELECT
                    o.order_id,
                    o.id,
                    o.total_price,
                    o.status,
                    o.created_at,
                    oi.product_id,
                    oi.quantity,
                    oi.price,
                    p.name AS product_name,
                    p.image_url
                FROM orders o
                JOIN order_items oi ON o.order_id = oi.order_id
                JOIN products p ON p.product_id = oi.product_id
                WHERE p.vendor_id = ?
                ORDER BY o.created_at DESC";

        $stmt = $pdo->prepare($sql);
        $stmt->execute([$vendor_id]);
        $results = $stmt->fetchAll(PDO::FETCH_ASSOC);

        $grouped = [];
        foreach ($results as $row) {
            $orderId = $row['order_id'];
            if (!isset($grouped[$orderId])) {
                $grouped[$orderId] = [
                    'order_id' => $row['order_id'],
                    'id' => $row['id'],
                    'total_price' => $row['total_price'],
                    'status' => $row['status'],
                    'created_at' => $row['created_at'],
                    'items' => [],
                ];
            }

            $grouped[$orderId]['items'][] = [
                'product_id' => $row['product_id'],
                'name' => $row['product_name'],
                'quantity' => $row['quantity'],
                'price' => $row['price'],
                'image_url' => $row['image_url']
            ];
        }

        $response->getBody()->write(json_encode([
            'status' => 'success',
            'data' => array_values($grouped)
        ]));
        return $response->withHeader('Content-Type', 'application/json');
    });
};
