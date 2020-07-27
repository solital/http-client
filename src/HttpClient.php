<?php

namespace Solital;

use Solital\Http\CurlMethods;
use Solital\Exception\NotFoundException;
use Solital\Exception\InvalidArgumentException;

class HttpClient extends CurlMethods
{
    /**
     * @var string
     */
    protected $token;

    /**
     * @var Curl
     */
    protected $ch;

    /**
     * @var Curl
     */
    protected $ssl;

    /**
     * @var HttpClient
     */
    private $res;

    /**
     * Start a cUrl extension
     * @param array $headers
     * @param string $token
     * @throw new NotFoundException 
     */
    public function __construct(array $headers = null, string $token = null)
    {
        if (!in_array('curl', get_loaded_extensions())) {
            NotFoundException::alert(404, "'curl' extension is not enabled");
        }

        $this->ch = curl_init();
        $this->ssl = curl_setopt($this->ch, CURLOPT_SSL_VERIFYPEER, false);

        if ($headers != null) {
            $this->headers = $headers;
        }

        if ($token != null) {
            $this->token = $token;
        }
    }

    /**
     * Enable SSL verify
     * @return string
     */
    public function enableSSL(): string
    {
        $this->ssl = curl_setopt($this->ch, CURLOPT_SSL_VERIFYPEER, true);
        return $this->ssl;
    }

    /**
     * Check if there are headers in the request
     * @return HttpClient
     */
    protected function verifyHeaders(): HttpClient
    {
        if ($this->headers == null) {
            $this->headers = [
                'Content-Type: application/json',
                'Accept: application/json'
            ];
        }

        if ($this->token != null) {
            array_push($this->headers, "Authorization: Bearer ".$this->token);
        }

        return $this;
    }

    /**
     * @param string $method
     * @param string $url
     * @param array $data
     * @return string
     */
    public function request(string $method, string $url, array $data = null): HttpClient
    {
        $this->verifyHeaders();
        $this->ch;
        $this->verifyUrl($url);
        $this->ssl;
        curl_setopt($this->ch, CURLOPT_HTTPHEADER, $this->headers);
        $method = strtoupper($method);

        if ($method == "POST" || $method == "PUT" || $method == "DELETE") {
            $this->verifyMethod($method);

            if ($method != "GET" || $method != "DELETE") {
                $this->verifyFields($method, $data);
            }
        }

        $this->res = $this->execute();
        
        if ($this->res === false) {
            response()->withStatus(404);
            throw new InvalidArgumentException(curl_error($this->ch));
        }

        response()->withStatus(200);
        return $this;
    }

    /**
     * @return string|json
     */
    public function json(): string
    {
        return $this->res; 
    }

    /**
     * @return array
     */
    public function array(): array
    {
        return json_decode($this->res, true);        
    }
}