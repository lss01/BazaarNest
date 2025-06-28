<?php

use Psr\Http\Message\ResponseInterface as Response;
use Psr\Http\Message\ServerRequestInterface as Request;
use Slim\App;

return function (App $app) {

    // Filter Product route
    $app->get('/api/products/{category}/{priceRange}', function ($request, $response, $args) {
        require __DIR__ . '/../../db.php';

        $category = $args['category'];
        $priceRange = $args['priceRange'];

        if ($category == '0') {
            $category = '%';
        }

        $sql = "SELECT * FROM products WHERE category LIKE :category";
        $stmt = $pdo->prepare($sql);
        $stmt->execute([':category' => $category]);

        $products = $stmt->fetchAll();

        $response->getBody()->write(json_encode([
            'status' => 'success',
            'category' => $category,
            'priceRange' => $priceRange,
            'products' => $products
        ]));
        return $response->withHeader('Content-Type', 'application/json')->withStatus(200);
    });

    // Display Product route
    $app->get('/api/product-detail/{id}', function ($request, $response, $args) {
        require __DIR__ . '/../../db.php';
        $id = $args['id'];
        $sql = "SELECT * FROM products WHERE product_id = :id";

        $bindings = [':id' => $id];
        $stmt = $pdo->prepare($sql);
        $stmt->execute([':id' => $id]);
        $product = $stmt->fetch();


        $response->getBody()->write(json_encode([
            'status' => 'success',
            'product' => $product
        ]));
        return $response->withHeader('Content-Type', 'application/json')->withStatus(200);
    });


    // Display Product Backup route
    $app->get('/api/product-detail-backup/{id}', function ($request, $response, $args) {
        require __DIR__ . '/../../db.php';
        $id = $args['id'];
        $sql = "SELECT
            p.product_id AS id,
            p.name,
            p.description,
            p.price,
            p.image_url AS image_url,
            v.shop_name AS shopName,
            v.description AS shopDescription,
            u.avatar AS sellerAvatar,
            u.id AS sellerId
            FROM products p
            JOIN vendors v ON v.id = p.vendor_id
            JOIN users u ON u.id = v.id
            WHERE p.product_id = :id";

        $bindings = [':id' => $id];
        $stmt = $pdo->prepare($sql);
        $stmt->execute($bindings);
        $products = $stmt->fetch();

        if ($products) {
            $product['images'] = [$products['image_url']];
            $product['seller'] = [
                'id' => $products['sellerId'],
                'name' => $products['shopName'],
                'avatar' => $products['sellerAvatar'],
                'description' => $products['shopDescription'],
            ];

            unset(
                $product['image_url'],
                $product['shopName'],
                $product['shopDescription'],
                $product['sellerAvatar'],
                $product['sellerId']
            );
            $response->getBody()->write(json_encode([
                'status' => 'success',
                'data' => $products
            ]));
            return $response->withHeader('Content-Type', 'application/json')->withStatus(200);
        } else {
            return $response->withHeader('Content-Type', 'application/json')
                ->withStatus(404)
                ->write(json_encode(['status' => 'error', 'message' => 'Product not found']));
        }
    });

};
