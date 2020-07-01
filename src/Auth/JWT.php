<?php

namespace Solital\Auth;

class JWT
{
    /**
     * Encodes the data to jwt
     * @param array $payload
     * @param string $key
     */
    public static function encode(array $payload, string $key)
    {
        //Header Token
        $header = [
            'typ' => 'JWT',
            'alg' => 'HS256'
        ];

        //Payload - Content
        /*$payload = [
            'exp' => date("H:i"),
            'uid' => 1,
            'email' => 'email@email.com',
        ];*/

        //JSON
        $header = json_encode($header);
        $payload = json_encode($payload);

        //Base 64
        $header = self::base64url_encode($header);
        $payload = self::base64url_encode($payload);

        //Sign
        $sign = hash_hmac('sha256', $header . "." . $payload, $key, true);
        $sign = self::base64url_encode($sign);

        //Token
        $token = $header . '.' . $payload . '.' . $sign;

        return $token;
    }

    /**
     * Decodes JWT token
     * @param string $token
     */
    public static function decode($token)
    {
        if ($token) {
            $array = explode('.', $token);
            
            if (array_key_exists(1, $array)) {
                $str1 = str_replace('-','+',$array[1]);
                $str2 = str_replace('_', '/', $str1);
                $b64 = self::base64url_decode($str2);
                $json = json_decode($b64);

                return $json;
            }
        }
    }

    /**
     * Encode to base64url
     * @param string
     */
    private static function base64url_encode($data)
    {
        // First of all you should encode $data to Base64 string
        $b64 = base64_encode($data);

        // Make sure you get a valid result, otherwise, return FALSE, as the base64_encode() function do
        if ($b64 === false) {
            return false;
        }

        // Convert Base64 to Base64URL by replacing “+” with “-” and “/” with “_”
        $url = strtr($b64, '+/', '-_');

        // Remove padding character from the end of line and return the Base64URL result
        return rtrim($url, '=');
    }

    /**
     * Decode data from Base64URL
     * @param string $data
     * @param boolean $strict
     * @return boolean|string
     */
    private static function base64url_decode($data, $strict = false)
    {
        // Convert Base64URL to Base64 by replacing “-” with “+” and “_” with “/”
        $b64 = strtr($data, '-_', '+/');

        // Decode Base64 string and return the original data
        return base64_decode($b64, $strict);
    }
}
