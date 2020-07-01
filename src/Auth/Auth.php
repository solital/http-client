<?php

namespace Solital\Auth;
use Solital\Auth\JWT;
use Solital\Core\Http\Response;
use Solital\Exception\NotFoundException;

class Auth
{

    /**
     * @var $_SERVER
     */
    private $server;

    /**
     * $_SERVER
     */
    public function __construct()
    {
        $this->server = $_SERVER;
    }
    
    /**
     * Add http basic authentication
     * @param string $user
     * @param string $pass
     * @param string $realm
     * @return self
     */
    public function basic($user, $pass, $realm): self
    {
        if (empty($this->server['PHP_AUTH_USER']) && empty($this->server['PHP_AUTH_PW'])) {
            if ($this->server['PHP_AUTH_USER'] !== $user && $this->server['PHP_AUTH_PW'] !== $pass) {
                header('WWW-Authenticate: Basic realm=\"'.$realm.'\"');
                header(request()->getProtocolVersion().' 401 Unauthorized');
                NotFoundException::alert(401, "Basic Auth: You are not allowed to access the router");
            }
        }
    }

    /**
     * Add http digest authentication
     * @param string $user
     * @param string $pass
     * @param string $realm
     * @return self
     */
    public function digest($user, $pass, $realm)
    {
        if (empty($this->server['PHP_AUTH_DIGEST'])) {
            header(request()->getProtocolVersion().' 401 Unauthorized');
            header('WWW-Authenticate: Digest realm="'.$realm.
                   '",qop="auth",nonce="'.uniqid().'",opaque="'.md5($realm).'"');
        
            NotFoundException::alert(401, "Digest Auth: You are not allowed to access the router");
        }
    }

    /**
     * Add http jwt authentication
     * @param string $user
     * @param string $pass
     * @param string $realm
     * @return self
     */
    public function JWT()
    {
        $jwtDecode = JWT::decode($this->server['HTTP_AUTHORIZATION']);

        if (empty($jwtDecode) || $jwtDecode == NULL) {
            header('HTTP/1.0 401 Unauthorized');
            NotFoundException::alert(401, "Invalid token");
        }

        if (empty($this->server['HTTP_AUTHORIZATION'])) {
            header('HTTP/1.0 401 Unauthorized');
            NotFoundException::alert(401, "Invalid token");
        }

        return true;
    }
}
