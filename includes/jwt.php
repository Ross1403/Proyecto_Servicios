<?php
// includes/jwt.php
class JWTAuth {
    private static $secret = 'DEVIOZ_PROYECTOS_SECRET_KEY_JWT_2026';
    
    public static function generateToken($payload) {
        $header = json_encode(['typ' => 'JWT', 'alg' => 'HS256']);
        $payload['exp'] = time() + (3600 * 24); // 24 horas de validez
        
        $base64UrlHeader = self::base64UrlEncode($header);
        $base64UrlPayload = self::base64UrlEncode(json_encode($payload));
        
        $signature = hash_hmac('sha256', $base64UrlHeader . "." . $base64UrlPayload, self::$secret, true);
        $base64UrlSignature = self::base64UrlEncode($signature);
        
        return $base64UrlHeader . "." . $base64UrlPayload . "." . $base64UrlSignature;
    }
    
    public static function validateToken($token) {
        $parts = explode('.', $token);
        if (count($parts) !== 3) return false;
        
        $signature = hash_hmac('sha256', $parts[0] . "." . $parts[1], self::$secret, true);
        $base64UrlSignature = self::base64UrlEncode($signature);
        
        if (hash_equals($base64UrlSignature, $parts[2])) {
            $payload = json_decode(self::base64UrlDecode($parts[1]), true);
            if (isset($payload['exp']) && $payload['exp'] >= time()) {
                return $payload; // Token válido y no expirado
            }
        }
        return false;
    }

    private static function base64UrlEncode($data) {
        return str_replace(['+', '/', '='], ['-', '_', ''], base64_encode($data));
    }

    private static function base64UrlDecode($data) {
        $padding = strlen($data) % 4;
        if ($padding > 0) {
            $data .= str_repeat('=', 4 - $padding);
        }
        return base64_decode(str_replace(['-', '_'], ['+', '/'], $data));
    }
}
