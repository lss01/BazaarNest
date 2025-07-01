<?php

use Firebase\JWT\JWT;
use Firebase\JWT\Key;


function generateJWT($userId, $role)
{
    $secretKey = $_ENV['JWT_SECRET'];
    if (!$secretKey) {
        throw new Exception('JWT_SECRET is not set in the environment');
    }

    $payload = [
        "sub" => $userId,
        "role" => $role,
        "iat" => time(),
        "exp" => time() + (60 * 60 * 24) // 1 day
    ];

    return JWT::encode($payload, $secretKey, 'HS256');
}


function validateJWT($token)
{
    $secretKey = $_ENV['JWT_SECRET'];
    return JWT::decode($token, new Key($secretKey, 'HS256'));
}
