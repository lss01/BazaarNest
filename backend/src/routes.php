<?php

use Psr\Http\Message\ResponseInterface as Response;
use Psr\Http\Message\ServerRequestInterface as Request;
use Slim\App;

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
    $app->get('/api/test', function (Request $request, Response $response) {
        $data = ["status" => "success"];
        $response->getBody()->write(json_encode($data));
        return $response->withHeader('Content-Type', 'application/json');
    });

    // Hello route
    $app->get('/api/hello', function (Request $request, Response $response) {
        $response->getBody()->write(json_encode(['message' => 'Hello from Slim!']));
        return $response->withHeader('Content-Type', 'application/json');
    });

    // Register route
    $app->post('/api/register', function (Request $request, Response $response) {
        require __DIR__ . '/../db.php';

        // Parse JSON input
        $data = json_decode($request->getBody()->getContents(), true);
        if (json_last_error() !== JSON_ERROR_NONE) {
            $response->getBody()->write(json_encode([
                'status' => 'error',
                'message' => 'Invalid JSON input'
            ]));
            return $response->withHeader('Content-Type', 'application/json')->withStatus(400);
        }

        $username = $data['username'] ?? '';
        $fullname = $data['fullname'] ?? '';
        $email = $data['email'] ?? '';
        $phone = $data['phone'] ?? '';
        $address = $data['address'] ?? '';
        $password = $data['password'] ?? '';
        $role = $data['role'] ?? '';

        // Validation
        if (empty($username) || empty($fullname) || empty($email) || empty($phone) || empty($address) || empty($password) || empty($role)) {
            $response->getBody()->write(json_encode([
                'status' => 'error',
                'message' => 'All fields are required'
            ]));
            return $response->withHeader('Content-Type', 'application/json')->withStatus(400);
        }

        try {
            // Check for duplicates
            $stmt = $pdo->prepare("SELECT id FROM users WHERE username = ? ");
            $stmt->execute([$username]);
            if ($stmt->fetch()) {
                $response->getBody()->write(json_encode([
                    'status' => 'error',
                    'message' => 'Username already exists'
                ]));
                return $response->withHeader('Content-Type', 'application/json')->withStatus(409);
            }

            // Hash password and insert
            $hashedPassword = password_hash($password, PASSWORD_DEFAULT);
            $stmt = $pdo->prepare("INSERT INTO users (username, fullname, email, phone, address, password, role) VALUES (?, ?, ?, ?, ?, ?, ?)");
            $stmt->execute([$username, $fullname, $email, $phone, $address, $hashedPassword, $role]);

            $response->getBody()->write(json_encode([
                'status' => 'success',
                'message' => 'Registration successful! Please login.'
            ]));
            return $response->withHeader('Content-Type', 'application/json')->withStatus(201);
        } catch (PDOException $e) {
            error_log('Database error: ' . $e->getMessage());
            $response->getBody()->write(json_encode([
                'status' => 'error',
                'message' => 'An internal server error occurred'
            ]));
            return $response->withHeader('Content-Type', 'application/json')->withStatus(500);
        }
    });

    // Login route
    $app->post('/api/login', function (Request $request, Response $response) {
        require __DIR__ . '/../db.php';

        // Use JSON parse (not form parse)
        $data = json_decode($request->getBody()->getContents(), true);

        $username = $data['username'] ?? '';
        $password = $data['password'] ?? '';
        $role = $data['role'] ?? '';

        if (empty($username) || empty($password) || empty($role)) {
            $response->getBody()->write(json_encode([
                'status' => 'error',
                'message' => 'All fields are required'
            ]));
            return $response->withHeader('Content-Type', 'application/json')->withStatus(400);
        }

        try {
            $stmt = $pdo->prepare("SELECT * FROM users WHERE username = ? AND role = ?");
            $stmt->execute([$username, $role]);
            $user = $stmt->fetch(PDO::FETCH_ASSOC);

            if ($user && password_verify($password, $user['password'])) {
                // Correct success status
                $response->getBody()->write(json_encode([
                    'status' => 'success',
                    'message' => 'Login successful',
                    'role' => $user['role'],
                    'avatar' => $user['avatar'] ?? null,
                    'userId' => $user['id'],
                    //can add 'token' here if using JWT later
                ]));
                return $response->withHeader('Content-Type', 'application/json')->withStatus(200);
            } else {
                $response->getBody()->write(json_encode([
                    'status' => 'error',
                    'message' => 'Invalid username, password, or role'
                ]));
                return $response->withHeader('Content-Type', 'application/json')->withStatus(401);
            }
        } catch (PDOException $e) {
            error_log($e->getMessage());
            $response->getBody()->write(json_encode([
                'status' => 'error',
                'message' => 'Internal server error'
            ]));
            return $response->withHeader('Content-Type', 'application/json')->withStatus(500);
        }
    });


    $app->get('/api/profile/{username}', function (Request $request, Response $response, array $args) {
        require __DIR__ . '/../db.php';

        $username = $args['username'];

        try {
            $stmt = $pdo->prepare("SELECT username, fullname ,email, phone , address, avatar FROM users WHERE username = ?");
            $stmt->execute([$username]);
            $user = $stmt->fetch(PDO::FETCH_ASSOC);

            if ($user) {
                $response->getBody()->write(json_encode([
                    'status' => 'success',
                    'data' => $user
                ]));
                return $response->withHeader('Content-Type', 'application/json')->withStatus(200);
            } else {
                $response->getBody()->write(json_encode([
                    'status' => 'error',
                    'message' => 'User not found'
                ]));
                return $response->withHeader('Content-Type', 'application/json')->withStatus(404);
            }
        } catch (PDOException $e) {
            error_log($e->getMessage());
            $response->getBody()->write(json_encode([
                'status' => 'error',
                'message' => 'Internal server error'
            ]));
            return $response->withHeader('Content-Type', 'application/json')->withStatus(500);
        }
    });

    $app->post('/api/profile/update', function (Request $request, Response $response) {
        require __DIR__ . '/../db.php';
        $params = json_decode($request->getBody()->getContents(), true);

        $username = $params['username'] ?? '';
        $fullname = $params['fullname'] ?? '';
        $email = $params['email'] ?? '';
        $phone = $params['phone'] ?? '';
        $address = $params['address'] ?? '';

        try {
            $stmt = $pdo->prepare("UPDATE users SET fullname = ?, email = ?, phone = ?, address = ? WHERE username = ?");
            $stmt->execute([$fullname, $email, $phone, $address, $username]);

            $response->getBody()->write(json_encode([
                'status' => 'success',
                'message' => 'Profile updated successfully',
                'data' => [
                    'username' => $username,
                    'fullname' => $fullname,
                    'email' => $email,
                    'phone' => $phone,
                    'address' => $address,
                    'avatar' => $avatarUrl
                ]
            ]));
            return $response->withHeader('Content-Type', 'application/json');
        } catch (PDOException $e) {
            $response->getBody()->write(json_encode([
                'status' => 'error',
                'message' => 'Failed to update profile'
            ]));
            return $response->withHeader('Content-Type', 'application/json')->withStatus(500);
        }
    });

    $app->post('/api/upload-avatar', function ($request, $response) {
        $uploadDir = __DIR__ . '/../src/uploads/avatars';
        $uploadedFiles = $request->getUploadedFiles();

        if (empty($uploadedFiles['avatar'])) {
            $response->getBody()->write(json_encode([
                'status' => 'error',
                'message' => 'No file uploaded'
            ]));
            return $response->withHeader('Content-Type', 'application/json')->withStatus(400);
        }

        $avatar = $uploadedFiles['avatar'];
        if ($avatar->getError() === UPLOAD_ERR_OK) {
            $allowedTypes = ['image/jpeg', 'image/png', 'image/jpg', 'image/gif'];
            if (!in_array($avatar->getClientMediaType(), $allowedTypes)) {
                $response->getBody()->write(json_encode([
                    'status' => 'error',
                    'message' => 'Invalid image type'
                ]));
                return $response->withHeader('Content-Type', 'application/json')->withStatus(400);
            }

            if (!is_dir($uploadDir)) {
                mkdir($uploadDir, 0777, true);
            }

            $filename = uniqid() . '_' . $avatar->getClientFilename();
            $avatar->moveTo($uploadDir . DIRECTORY_SEPARATOR . $filename);

            $username = $_POST['username'] ?? null;
            if (!$username) {
                $response->getBody()->write(json_encode([
                    'status' => 'error',
                    'message' => 'Missing username'
                ]));
                return $response->withHeader('Content-Type', 'application/json')->withStatus(400);
            }

            require __DIR__ . '/../db.php';
            $stmt = $pdo->prepare("UPDATE users SET avatar = ? WHERE username = ?");
            $stmt->execute([$filename, $username]);

            $response->getBody()->write(json_encode([
                'status' => 'success',
                'avatarUrl' => $filename
            ]));
            return $response->withHeader('Content-Type', 'application/json');
        }

        $response->getBody()->write(json_encode([
            'status' => 'error',
            'message' => 'Failed to upload file'
        ]));
        return $response->withHeader('Content-Type', 'application/json')->withStatus(500);
    });

    $app->get('/api/products/{category}/{priceRange}', function ($request, $response, $args) {
        require __DIR__ . '/../db.php';
        $category = $args['category'];
        $priceRange = $args['priceRange'];

        $queryParams = $request->getQueryParams();
        $search = isset($queryParams['search']) ? '%' . $queryParams['search'] . '%' : null;

        $sql = "SELECT
                    p.product_id AS id,
                    p.name,
                    p.description,
                    p.price,
                    p.image_url AS image,
                    v.shop_name AS shopName
                FROM products p
                JOIN vendors v ON v.id = p.vendor_id";

        $conditions = [];
        $bindings = [];

        if ($search) {
            $conditions[] = "p.name LIKE :search OR p.description LIKE :search";
            $bindings[':search'] = $search;
        }

        if ($category != 0) {
            $sql .= " AND p.category = :category";
            $bindings[':category'] = $category;
        }

        if ($priceRange != 0) {
            if ($priceRange === '0-50') {
                $sql .= " AND p.price BETWEEN 0 AND 50";
            } elseif ($priceRange === '51-100') {
                $sql .= " AND p.price BETWEEN 51 AND 100";
            } elseif ($priceRange === '101-200') {
                $sql .= " AND p.price BETWEEN 101 AND 200";
            } elseif ($priceRange === '201+') {
                $sql .= " AND p.price >= 201";
            }
        }

        if (!empty($conditions)) {
            $sql .= " WHERE " . implode(" AND ", $conditions);
        }

        $stmt = $pdo->prepare($sql);
        if (isset($bindings)) {
            $stmt->execute($bindings);
        }
        $products = $stmt->fetchAll();

        if ($products) {
            $response->getBody()->write(json_encode([
                'status' => 'success',
                'products' => $products
            ]));
            return $response->withHeader('Content-Type', 'application/json')->withStatus(200);
        } else {
            $response->getBody()->write(json_encode([
                'status' => 'error',
                'message' => 'No products found'
            ]));
            return $response->withHeader('Content-Type', 'application/json')->withStatus(200);
        }
    });
    $app->get('/api/product-detail/{id}', function ($request, $response, $args) {
        require __DIR__ . '/../db.php';
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

            unset($product['image_url'], $product['shopName'], $product['shopDescription'], $product['sellerAvatar'], $product['sellerId']);
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
    $app->get('/api/add-cart/{id}/{productId}/{quantity}', function ($request, $response, $args) {
        require __DIR__ . '/../db.php';
        $id = $args['id'];
        $productId = $args['productId'];
        $quantity = $args['quantity'];
        $sql = "INSERT INTO cart_items (user_id, product_id, quantity,added_at) VALUES (?, ?, ?, NOW())
                ON DUPLICATE KEY UPDATE quantity = quantity + ?";

        $stmt = $pdo->prepare($sql);
        $stmt->execute([$id, $productId, $quantity, $quantity]);

        $response->getBody()->write(json_encode([
            'status' => 'success',
            'message' => 'successfully added to cart',
        ]));
        return $response->withHeader('Content-Type', 'application/json')->withStatus(200);
    });
    $app->get('/api/cart/{id}', function ($request, $response, $args) {
        require __DIR__ . '/../db.php';
        $id = $args['id'];
        $sql = "SELECT
                  c.product_id AS id,
                  p.name AS name,
                  v.shop_name AS shopName,
                  p.image_url AS image,
                  c.quantity AS quantity,
                  p.price AS price
                FROM cart_items c
                JOIN products p ON p.product_id = c.product_id
                JOIN vendors v ON v.vendor_id = p.vendor_id
                WHERE c.user_id = :id";

        $bindings = [':id' => $id];
        $stmt = $pdo->prepare($sql);
        $stmt->execute($bindings);
        $products = $stmt->fetchAll();

        if ($products) {
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
    $app->get('/api/cart-update/{id}/{productId}/{quantity}', function ($request, $response, $args) {
        require __DIR__ . '/../db.php';
        $id = $args['id'];
        $productId = $args['productId'];
        $quantity = $args['quantity'];
        $sql = "UPDATE cart_items
                SET quantity = ?
                WHERE user_id = ? AND product_id = ?";

        $stmt = $pdo->prepare($sql);
        $stmt->execute([$quantity, $id, $productId]);

        $response->getBody()->write(json_encode([
            'status' => 'success',
            'message' => 'successfully updated',
        ]));
        return $response->withHeader('Content-Type', 'application/json')->withStatus(200);
    });
    $app->get('/api/cart-remove/{id}/{productId}', function ($request, $response, $args) {
        require __DIR__ . '/../db.php';
        $id = $args['id'];
        $productId = $args['productId'];
        $sql = "DELETE FROM cart_items
                WHERE user_id = ? AND product_id = ?";

        $stmt = $pdo->prepare($sql);
        $stmt->execute([$id, $productId]);

        $response->getBody()->write(json_encode([
            'status' => 'success',
            'message' => 'successfully removed from cart',
        ]));
        return $response->withHeader('Content-Type', 'application/json')->withStatus(200);
    });
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
};
