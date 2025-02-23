<?php
require '../vendor/autoload.php';
use Firebase\JWT\JWT;
use Firebase\JWT\Key;

class JWTHandler {
    private static $secret_key = "i+nQD~K8.Y+O{(M"; // Change this!
    private static $algorithm = "HS256";

    public static function generateToken($data) {
        $payload = [
            'iat' => time(),
            'exp' => time() + (30*24*60*60), // 30 days expiration
            'data' => $data
        ];
        return JWT::encode($payload, self::$secret_key, self::$algorithm);
    }

    public static function verifyToken($token) {
        try {
            return JWT::decode($token, new Key(self::$secret_key, self::$algorithm));
        } catch (Exception $e) {
            return false;
        }
    }
}
?>

