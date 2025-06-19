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
                    'role' => $user['role']
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
        $directory = __DIR__ . '/../uploads/avatars';
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
            // Validate MIME type
            $mimeType = $avatar->getClientMediaType();
            $allowedTypes = ['image/jpeg', 'image/png', 'image/jpg', 'image/gif'];
            if (!in_array($mimeType, $allowedTypes)) {
                $response->getBody()->write(json_encode([
                    'status' => 'error',
                    'message' => 'Invalid image type'
                ]));
                return $response->withHeader('Content-Type', 'application/json')->withStatus(400);
            }
            if (!is_dir($directory)) {
                mkdir($directory, 0777, true);
            }

            if (!is_writable($directory)) {
                $response->getBody()->write(json_encode([
                    'status' => 'error',
                    'message' => 'Upload folder is not writable'
                ]));
                return $response->withHeader('Content-Type', 'application/json')->withStatus(500);
            }

            // Move the file
            $filename = moveUploadedFile($directory, $avatar);
            $avatarUrl = "http://localhost:8080/src/uploads/avatars/" . $filename;

            $username = $_POST['username'] ?? null;

            if (!$username) {
                return $response->withJson(['status' => 'error', 'message' => 'Missing username'], 400);
            }

            // Save the avatar URL to the database
            try {
                require __DIR__ . '/../db.php';
                $stmt = $pdo->prepare("UPDATE users SET avatar = ? WHERE username = ?");
                $stmt->execute([$avatarUrl, $username]);

                $response->getBody()->write(json_encode([
                    'status' => 'success',
                    'avatarUrl' => $avatarUrl
                ]));

                return $response->withHeader('Content-Type', 'application/json')->withStatus(200);
            } catch (PDOException $e) {
                $response->getBody()->write(json_encode([
                    'status' => 'error',
                    'message' => 'DB error: ' . $e->getMessage()
                ]));
                return $response->withHeader('Content-Type', 'application/json')->withStatus(500);
            }
        }

        $response->getBody()->write(json_encode([
            'status' => 'error',
            'message' => 'Failed to upload file'
        ]));
        return $response->withHeader('Content-Type', 'application/json')->withStatus(500);
    });
};
