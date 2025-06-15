<?php

use Psr\Http\Message\ResponseInterface as Response;
use Psr\Http\Message\ServerRequestInterface as Request;
use Slim\App;

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
        $email = $data['email'] ?? '';
        $password = $data['password'] ?? '';
        $role = $data['role'] ?? '';

        // Validation
        if (empty($username) || empty($email) || empty($password) || empty($role)) {
            $response->getBody()->write(json_encode([
                'status' => 'error',
                'message' => 'All fields are required'
            ]));
            return $response->withHeader('Content-Type', 'application/json')->withStatus(400);
        }

        try {
            // Check for duplicates
            $stmt = $pdo->prepare("SELECT id FROM users WHERE username = ? OR email = ?");
            $stmt->execute([$username, $email]);
            if ($stmt->fetch()) {
                $response->getBody()->write(json_encode([
                    'status' => 'error',
                    'message' => 'Username or email already exists'
                ]));
                return $response->withHeader('Content-Type', 'application/json')->withStatus(409);
            }

            // Hash password and insert
            $hashedPassword = password_hash($password, PASSWORD_DEFAULT);
            $stmt = $pdo->prepare("INSERT INTO users (username, email, password, role) VALUES (?, ?, ?, ?)");
            $stmt->execute([$username, $email, $hashedPassword, $role]);

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

        // ✅ Use JSON parse (not form parse)
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
                // ✅ Correct success status
                $response->getBody()->write(json_encode([
                    'status' => 'success',
                    'message' => 'Login successful',
                    'role' => $user['role']
                    // you can add 'token' here if using JWT later
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
};
