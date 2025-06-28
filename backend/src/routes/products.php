<?php

use Psr\Http\Message\ResponseInterface as Response;
use Psr\Http\Message\ServerRequestInterface as Request;
use Slim\App;

return function (App $app) {

    // Fetch all Product route
    $app->get('/api/products/list/{category}/{priceRange}', function ($request, $response, $args) {
        require __DIR__ . '/../../db.php';

        $category = $args['category'] === '0' ? null : $args['category'];
        $priceRange = $args['priceRange'];

        $sql = "SELECT * FROM products WHERE (:category IS NULL OR category = :category)";

        $params = [':category' => $category];

        if ($priceRange !== '0') {
            $range = explode('-', $priceRange);
            if (count($range) == 2) {
                $sql .= " AND price BETWEEN :min AND :max";
                $params[':min'] = floatval($range[0]);
                $params[':max'] = floatval($range[1]);
            }
        }

        $stmt = $pdo->prepare($sql);
        $stmt->execute($params);

        $products = $stmt->fetchAll();

        $response->getBody()->write(json_encode([
            'status' => 'success',
            'category' => $category,
            'priceRange' => $priceRange,
            'products' => $products
        ]));
        return $response->withHeader('Content-Type', 'application/json')->withStatus(200);
    });


    // Fetch Vendor Products
    $app->get('/api/products/seller/{id}', function ($request, $response, $args) {
        require __DIR__ . '/../../db.php';

        $id = $args['id'];

        $sql = "SELECT * FROM products WHERE vendor_id LIKE :id";
        $stmt = $pdo->prepare($sql);
        $stmt->execute([':id' => $id]);

        $products = $stmt->fetchAll();

        $response->getBody()->write(json_encode([
            'status' => 'success',
            'products' => $products
        ]));
        return $response->withHeader('Content-Type', 'application/json')->withStatus(200);
    });

    // Display Product Details with Vendor Info
    $app->get('/api/product/detail/{id}', function ($request, $response, $args) {
        require __DIR__ . '/../../db.php';
        $id = $args['id'];

        $sql = "SELECT 
                p.*, 
                u.id AS vendor_id, 
                u.username, 
                u.fullname, 
                u.email, 
                u.phone, 
                u.address, 
                u.role, 
                u.avatar, 
                u.created_at AS user_created_at 
            FROM products p
            JOIN users u ON p.vendor_id = u.id
            WHERE p.product_id = :id";

        $stmt = $pdo->prepare($sql);
        $stmt->execute([':id' => $id]);
        $product = $stmt->fetch();

        $response->getBody()->write(json_encode([
            'status' => 'success',
            'product' => $product
        ]));
        return $response->withHeader('Content-Type', 'application/json')->withStatus(200);
    });


    $app->post('/api/products/add', function ($request, $response) {
        require __DIR__ . '/../../db.php';

        $data = json_decode($request->getBody(), true);

        $sql = "INSERT INTO products (vendor_id, name, description, price, stock, category, image_url, created_at)
            VALUES (:vendor_id, :name, :description, :price, :stock, :category, :image_url, NOW())";

        $stmt = $pdo->prepare($sql);
        $stmt->execute([
            ':vendor_id' => $data['vendor_id'],
            ':name' => $data['name'],
            ':description' => $data['description'],
            ':price' => $data['price'],
            ':stock' => $data['stock'],
            ':category' => $data['category'],
            ':image_url' => $data['image_url']
        ]);

        $response->getBody()->write(json_encode(['status' => 'success']));
        return $response->withHeader('Content-Type', 'application/json')->withStatus(201);
    });

    $app->delete('/api/products/delete/{id}', function ($request, $response, $args) {
        require __DIR__ . '/../../db.php';

        $id = $args['id'];

        $sql = "DELETE FROM products WHERE product_id = :id";
        $stmt = $pdo->prepare($sql);
        $stmt->execute([':id' => $id]);

        $response->getBody()->write(json_encode(['status' => 'success']));
        return $response->withHeader('Content-Type', 'application/json')->withStatus(200);
    });

    $app->put('/api/products/update/{id}', function ($request, $response, $args) {
        require __DIR__ . '/../../db.php';

        $data = json_decode($request->getBody(), true);
        $id = $args['id'];

        $sql = "UPDATE products SET 
                vendor_id = :vendor_id,
                name = :name,
                description = :description,
                price = :price,
                stock = :stock,
                category = :category,
                image_url = :image_url
            WHERE product_id = :id";

        $stmt = $pdo->prepare($sql);
        $stmt->execute([
            ':vendor_id' => $data['vendor_id'],
            ':name' => $data['name'],
            ':description' => $data['description'],
            ':price' => $data['price'],
            ':stock' => $data['stock'],
            ':category' => $data['category'],
            ':image_url' => $data['image'],
            ':id' => $id
        ]);

        $response->getBody()->write(json_encode(['status' => 'success']));
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
