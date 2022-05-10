<?php

namespace Solital\Http\Auth;

use Solital\Exception\NotFoundException;

class HttpAuth
{
    /**
     * @var array
     */
    private array $auth = [
        "token" => "",
        "user" => "",
        "pass" => ""
    ];

    /**
     * @param string $username
     * @param string $password
     * @param string $realm
     * 
     * @return void
     */
    public function basic(string $username, string $password, string $realm = "SolitalHttpClientBasic"): void
    {
        if (isset($_SERVER['PHP_AUTH_USER'])) {
            $this->auth['user'] = $_SERVER['PHP_AUTH_USER'];
            $this->auth['pass'] = $_SERVER['PHP_AUTH_PW'];
        } elseif (isset($_SERVER['HTTP_AUTHORIZATION'])) {
            $replace = str_replace("Basic", "", $_SERVER['HTTP_AUTHORIZATION']);
            $code = explode(":", base64_decode(trim($replace)));
            $this->auth['user'] = $code[0];
            $this->auth['pass'] = $code[1];
        }

        if (!empty($username) && !empty($password)) {
            if (strcasecmp($username, $username) != 0 || strcasecmp($password, $password) != 0) {
                header('WWW-Authenticate: Basic realm="' . $realm . '"');
                header('HTTP/' . $_SERVER['SERVER_PROTOCOL'] . ' 401 Unauthorized');
                http_response_code(401);
                throw new NotFoundException("You are not allowed to access the router", 401);
            }
        } else {
            http_response_code(400);
            throw new NotFoundException("Username and/or password not defined!", 400);
        }
    }

    /**
     * @param array $users
     * @param string $realm
     * 
     * @return void
     */
    public function digest(array $users, string $realm = "SolitalHttpClientDigest"): void
    {
        if (empty($_SERVER['PHP_AUTH_DIGEST'])) {
            header('HTTP/' . $_SERVER['SERVER_PROTOCOL'] . ' 401 Unauthorized');
            header('WWW-Authenticate: Digest realm="' . $realm .
                '",qop="auth",nonce="' . uniqid() . '",opaque="' . md5($realm) . '"');

            die('Authentication has been canceled ');
        }

        if (
            !($data = $this->httpDigestParse($_SERVER['PHP_AUTH_DIGEST'])) ||
            !isset($users[$data['username']])
        ) {
            http_response_code(401);
            throw new NotFoundException("You are not allowed to access the router", 401);
        }

        $A1 = md5($data['username'] . ':' . $realm . ':' . $users[$data['username']]);
        $A2 = md5($_SERVER['REQUEST_METHOD'] . ':' . $data['uri']);
        $valid_response = md5($A1 . ':' . $data['nonce'] . ':' . $data['nc'] . ':' . $data['cnonce'] . ':' . $data['qop'] . ':' . $A2);

        if ($data['response'] != $valid_response) {
            http_response_code(401);
            throw new NotFoundException("You are not allowed to access the router", 401);
        }
    }

    /**
     * @param mixed $txt
     */
    private function httpDigestParse(mixed $txt): mixed
    {
        $needed_parts = ['nonce' => 1, 'nc' => 1, 'cnonce' => 1, 'qop' => 1, 'username' => 1, 'uri' => 1, 'response' => 1];
        $data = [];
        $keys = implode('|', array_keys($needed_parts));

        preg_match_all('@(' . $keys . ')=(?:([\'"])([^\2]+?)\2|([^\s,]+))@', $txt, $matches, PREG_SET_ORDER);

        foreach ($matches as $m) {
            $data[$m[1]] = $m[3] ? $m[3] : $m[4];
            unset($needed_parts[$m[1]]);
        }

        return $needed_parts ? false : $data;
    }
}
