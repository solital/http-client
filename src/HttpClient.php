<?php

namespace Solital;

use Solital\Http\CurlMethods;
use Solital\Exception\{NotFoundException, InvalidArgumentException};

class HttpClient extends CurlMethods
{
    /**
     * @var array
     */
    protected array $token = [];

    /**
     * @var mixed
     */
    protected mixed $ch;

    /**
     * @var bool
     */
    protected bool $ssl = false;

    /**
     * @var mixed
     */
    private mixed $res;

    /**
     * @var array
     */
    private array $http_methods = [
        "GET",
        "POST",
        "PUT",
        "PATCH",
        "DELETE",
        "OPTIONS"
    ];

    /**
     * @param array $headers
     * @throws NotFoundException
     */
    public function __construct(array $headers = null)
    {
        if (!in_array('curl', get_loaded_extensions())) {
            http_response_code(404);
            throw new NotFoundException("'curl' extension is not enabled", 404);
        }

        $this->ch = curl_init();
        $this->ssl = curl_setopt($this->ch, CURLOPT_SSL_VERIFYPEER, false);

        if ($headers != null) {
            $this->headers = $headers;
        }

        /* if ($token != null) {
            $this->token = $token;
        } */
    }

    /**
     * Enable SSL verify
     * 
     * @return string
     */
    public function enableSSL(): string
    {
        $this->ssl = true;

        return $this->ssl;
    }

    /**
     * @param string $token
     * 
     * @return HttpClient
     */
    public function bearerToken(string $token): HttpClient
    {
        array_push($this->headers, "Authorization: Bearer " . $token);

        return $this;
    }

    /**
     * @param string $user
     * @param string $password
     * 
     * @return HttpClient
     */
    public function basicAuth(string $user, string $password): HttpClient
    {
        $base = $user . ":" . $password;
        $code = base64_encode($base);
        array_push($this->headers, "Authorization: Basic " . $code);

        return $this;
    }

    /**
     * Check if there are headers in the request
     * 
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

        return $this;
    }

    /**
     * @param string $method
     * @param string $url
     * @param array|null $data
     * 
     * @return HttpClient
     */
    public function request(string $method, string $url, array $data = null): HttpClient
    {
        $method = strtoupper($method);

        $this->validate($method, $url);
        $this->verifyHeaders();
        $this->ch;
        $this->verifyUrl($url);

        curl_setopt($this->ch, CURLOPT_SSL_VERIFYPEER, $this->ssl);
        curl_setopt($this->ch, CURLOPT_HTTPHEADER, $this->headers);

        if ($method != "GET") {
            $this->verifyMethod($method);
        }

        if ($data != null) {
            $this->verifyFields($method, $data);
        }

        $this->res = $this->execute();

        if ($this->res === false) {
            http_response_code(404);
            throw new InvalidArgumentException("Request Not Found", 404);
        }

        http_response_code(200);
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
     * @return mixed
     */
    public function toObject(): mixed
    {
        return json_decode($this->res);
    }

    /**
     * @param string $method
     * @param string $url
     * 
     * @return void
     * @throws InvalidArgumentException
     */
    private function validate(string $method, string $url): void
    {
        if (!in_array($method, $this->http_methods) || is_numeric($method)) {
            http_response_code(400);
            throw new InvalidArgumentException("Bad Request - The '$method' method entered is not valid", 400);
        }

        if (!filter_var($url, FILTER_VALIDATE_URL) || is_numeric($url)) {
            http_response_code(400);
            throw new InvalidArgumentException("Bad Request - The URL entered is not valid", 400);
        }
    }
}
