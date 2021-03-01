<?php

namespace Solital\Http\Auth;

use Solital\Exception\NotFoundException;

class HttpAuth
{
    /**
     * @var array
     */
    private $auth = [
        "token" => "",
        "user" => "",
        "pass" => ""
    ];

    /**
     * @var array
     */
    private $token = [];

    /**
     * @var $_SERVER
     */
    private $server;

    /**
     * @param array $token
     */
    public function __construct(array $token = null)
    {
        $this->server = $_SERVER;

        if ($token != null) {
            $this->token = $token;
        }
    }

    /**
     * @param string $realm
     * @return void
     */
    public function basic(string $realm = "SolitalHttpClientBasic"): void
    {
        if (isset($this->server['PHP_AUTH_USER'])) {
            $this->auth['user'] = $this->server['PHP_AUTH_USER'];
            $this->auth['pass'] = $this->server['PHP_AUTH_PW'];
        } elseif (isset($this->server['HTTP_AUTHORIZATION'])) {
            $replace = str_replace("Basic", "", $this->server['HTTP_AUTHORIZATION']);
            $code = explode(":", base64_decode(trim($replace)));
            $this->auth['user'] = $code[0];
            $this->auth['pass'] = $code[1];
        }

        if (array_key_exists("user", $this->token) && array_key_exists("pass", $this->token)) {
            if (strcasecmp($this->token['user'], $this->auth['user']) != 0 || strcasecmp($this->token['pass'], $this->auth['pass']) != 0) {
                header('WWW-Authenticate: Basic realm="' . $realm . '"');
                header('HTTP/'.$_SERVER['SERVER_PROTOCOL'].' 401 Unauthorized');
                NotFoundException::alert(401, "You are not allowed to access the router");
            }
        } else {
            NotFoundException::alert(400, "'user' and/or 'pass' key not defined");
        }
    }

    /**
     * @param array $users
     * @param string $realm
     * @return void
     */
    public function digest(array $users, string $realm = "SolitalHttpClientDigest"): void
    {
        if (empty($_SERVER['PHP_AUTH_DIGEST'])) {
            header('HTTP/'.$_SERVER['SERVER_PROTOCOL'].' 401 Unauthorized');
            header('WWW-Authenticate: Digest realm="' . $realm .
                '",qop="auth",nonce="' . uniqid() . '",opaque="' . md5($realm) . '"');

            die('Authentication has been canceled ');
        }

        if (
            !($data = $this->http_digest_parse($_SERVER['PHP_AUTH_DIGEST'])) ||
            !isset($users[$data['username']])
        ) {
            NotFoundException::alert(401, "You are not allowed to access the router");
        }

        $A1 = md5($data['username'] . ':' . $realm . ':' . $users[$data['username']]);
        $A2 = md5($_SERVER['REQUEST_METHOD'] . ':' . $data['uri']);
        $valid_response = md5($A1 . ':' . $data['nonce'] . ':' . $data['nc'] . ':' . $data['cnonce'] . ':' . $data['qop'] . ':' . $A2);
        
        if ($data['response'] != $valid_response) {
            NotFoundException::alert(401, "You are not allowed to access the router");
        }

    }

    /**
     * @param mixed $txt
     */
    private function http_digest_parse($txt): ?array
    {
        $needed_parts = array('nonce' => 1, 'nc' => 1, 'cnonce' => 1, 'qop' => 1, 'username' => 1, 'uri' => 1, 'response' => 1);
        $data = array();
        $keys = implode('|', array_keys($needed_parts));

        preg_match_all('@(' . $keys . ')=(?:([\'"])([^\2]+?)\2|([^\s,]+))@', $txt, $matches, PREG_SET_ORDER);

        foreach ($matches as $m) {
            $data[$m[1]] = $m[3] ? $m[3] : $m[4];
            unset($needed_parts[$m[1]]);
        }

        return $needed_parts ? false : $data;
    }
}
