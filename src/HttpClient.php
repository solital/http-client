<?php

namespace Solital;

use Solital\CurlMethods;
use Solital\Exception\NotFoundException;
use Solital\Exception\InvalidArgumentException;

class HttpClient extends CurlMethods
{
    /**
     * @var string
     */
    protected $token;

    /**
     * @var array
     */
    protected $headers;

    /**
     * @var curl
     */
    protected $ch;

    /**
     * @var string
     */
    protected $url;

    /**
     * @var curl
     */
    protected $ssl;

    /**
     * @var string
     */
    protected $method;

    /**
     * @var curl
     */
    protected $field;

    /**
     * @var curl
     */
    protected $execute;

    /**
     * Start a cUrl extension
     * @throw new NotFoundException 
     */
    public function __construct()
    {
        if (!in_array('curl', get_loaded_extensions())) {
            throw new \NotFoundException("'curl' extension is not enabled");
        }
        
        $this->ch = curl_init();
        $this->ssl = curl_setopt($this->ch, CURLOPT_SSL_VERIFYPEER, false);
    }

    /**
     * Insert a token in header
     * @param string $token
     */
    public function token(string $token)
    {
        $this->token = $token;
        return $this->token;
    }

    /**
     * Enable SSL verify
     */
    public function enableSSL()
    {
        $this->ssl = curl_setopt($this->ch, CURLOPT_SSL_VERIFYPEER, true);
        return $this->ssl;
    }

    /**
     * Inserts headers in curl
     * @param array $headers
     */
    public function headers(array $headers = null)
    {
        $this->headers = [
            'Content-Type: application/json',
            'Accept: application/json',
            'Authorization: Bearer '. $this->token
        ];
        
        if ($headers != null) {
            $this->headers = $headers;
        }

        return $this;
    }

    /**
     * Performs a GET request
     * @param string $url
     * @param bool   $decode
     */
    public function get(string $url, bool $decode = false) 
    {
        $this->verifyHeaders();

        $this->ch;
        $this->verifyUrl($url);
        $this->ssl;
        curl_setopt($this->ch, CURLOPT_HTTPHEADER, $this->headers);
        $res = $this->execute();
        $decoded = json_decode($res, true);

        if ($res === false) {
            throw new \InvalidArgumentException(curl_error($this->ch));
        }

        if ($decode == true) {
            return $decoded;
        } else {
            return $res;
        }
    }

    /**
     * Performs a POST request
     * @param string $url
     * @param array  $data
     */
    public function post(string $url, array $data) 
    {
        $this->verifyHeaders();

        $this->ch;
        $this->verifyUrl($url);
        $this->ssl;
        curl_setopt($this->ch, CURLOPT_HTTPHEADER, $this->headers);
        $this->verifyMethod("POST");
        $this->verifyFields("POST", $data);
        $res = $this->execute();

        if ($res === false) {
            echo curl_error($this->ch);
            die();
        }

        return $res;
    }

    /**
     * Performs a PUT request
     * @param string $url
     * @param array  $data
     */
    public function put(string $url, array $data) 
    {
        $this->verifyHeaders();
        
        $this->ch;
        $this->verifyUrl($url);
        $this->ssl;
        curl_setopt($this->ch, CURLOPT_HTTPHEADER, $this->headers);
        $this->verifyMethod("PUT");
        $this->verifyFields("PUT",$data);
        $res = $this->execute();

        if ($res === false) {
            throw new \InvalidArgumentException(curl_error($this->ch));
        }

        return $res;
    }

    /**
     * Performs a DELETE request
     * @param string $url
     */
    public function delete(string $url) 
    {
        $this->verifyHeaders();

        $this->ch;
        $this->verifyUrl($url);
        $this->ssl;
        curl_setopt($this->ch, CURLOPT_HTTPHEADER, $this->headers);
        $this->verifyMethod("DELETE");
        $res = $this->execute();

        if ($res === false) {
            throw new \InvalidArgumentException(curl_error($this->ch));
        }

        return $res;
    }

}
