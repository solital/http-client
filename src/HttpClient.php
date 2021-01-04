<?php

namespace Solital;

use Solital\Http\CurlMethods;
use Solital\Exception\NotFoundException;
use Solital\Exception\InvalidArgumentException;

class HttpClient extends CurlMethods
{
    /**
     * @var array
     */
    protected $token = [];

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
     * @var array
     */
    private $http_methods = [
        "GET",
        "POST",
        "PUT",
        "PATCH",
        "DELETE",
        "OPTIONS"
    ];

    /**
     * @param array $headers
     * @param array $token
     * @throws NotFoundException
     */
    public function __construct(array $headers = null, array $token = null)
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

        if (isset($this->token['token'])) {
            array_push($this->headers, "Authorization: Bearer ".$this->token['token']);
        } else if (isset($this->token['user']) && isset($this->token['pass'])) {
            $base = $this->token['user'].":".$this->token['pass'];
            $code = base64_encode($base);
            array_push($this->headers, "Authorization: Basic ".$code);
        }

        return $this;
    }

    /**
     * @param string $method
     * @param string $url
     * @param array $data
     * @throws InvalidArgumentException
     * @return HttpClient
     */
    public function request(string $method, string $url, array $data = null): HttpClient
    {
        $method = strtoupper($method);
        $this->validate($method, $url);
        
        $this->verifyHeaders();
        $this->ch;
        $this->verifyUrl($url);
        $this->ssl;
        curl_setopt($this->ch, CURLOPT_HTTPHEADER, $this->headers);

        if ($method != "GET") {
            $this->verifyMethod($method);   
        }

        if ($data != null) {
            $this->verifyFields($method, $data);
        }

        $this->res = $this->execute();
        
        if ($this->res === false) {
            response()->withStatus(404);
            InvalidArgumentException::alert(404, "Not Found", "");
        }

        response()->withStatus(200);
        return $this;
    }

    /**
     * @return string
     */
    public function toJson(): string
    {
        return (string)$this->res;
    }

    /**
     * @return array
     */
    public function toArray(): array
    {
        return json_decode($this->res, true);
    }

    /**
     * @return array
     */
    public function toObject(): array
    {
        return json_decode($this->res);
    }

    /**
     * @param string $method
     * @param string $url
     * @throws InvalidArgumentException
     * @return void
     */
    private function validate(string $method, string $url): void
    {
        if (!in_array($method, $this->http_methods) || is_numeric($method)) {
            response()->withStatus(400);
            InvalidArgumentException::alert(400, "Bad Request", "The '$method' method entered is not valid");
        }

        if (!filter_var($url, FILTER_VALIDATE_URL) || is_numeric($url)) {
            response()->withStatus(400);
            InvalidArgumentException::alert(400, "Bad Request", "The URL entered is not valid");
        }
    }
}