<?php

namespace Solital\Http;

abstract class CurlMethods
{
    /**
     * @var string
     */
    protected $url;

    /**
     * @var string
     */
    protected $method;

    /**
     * @var array
     */
    protected $headers;

    /**
     * @var curl
     */
    protected $field;

    /**
     * @var curl
     */
    protected $execute;

    /**
     * Inserts headers in curl
     * @param array $headers
     * @return CurlMethods
     */
    public function headers(array $headers = null): void
    {
        $this->headers = [
            'Content-Type: application/json',
            'Accept: application/json'
        ];

        if ($headers != null) {
            $this->headers = $headers;
        }
    }

    /**
     * Check the request url
     * @param string $url
     * @return string
     */
    protected function verifyUrl(string $url): string
    {
        $this->url = curl_setopt($this->ch, CURLOPT_URL, $url);
        $this->url .= curl_setopt($this->ch, CURLOPT_RETURNTRANSFER, true);
        return $this->url;
    }

    /**
     * Check the request method
     * @param string $method
     * @return string
     */
    protected function verifyMethod(string $method): string
    {
        if ($this->method == "POST") {
            $this->method = curl_setopt($this->ch, CURLOPT_POST, true);
        } else {
            $this->method = curl_setopt($this->ch, CURLOPT_CUSTOMREQUEST, "$method");
        }

        return $this->method;
    }

    /**
     * Check the request fields
     * @param string $method
     * @param string $data
     * @return string
     */
    protected function verifyFields($method, $data): string
    {
        if ($method == "POST") {
            $this->field = curl_setopt($this->ch, CURLOPT_POSTFIELDS, $data);
        } else if ($this->method == "PUT") {
            $this->field = curl_setopt($this->ch, CURLOPT_POSTFIELDS, json_encode($data));
        }

        return $this->field;
    }

    /**
     * Performs the curl execution
     * @return string
     */
    protected function execute(): string
    {
        $this->execute = curl_exec($this->ch);
        $this->execute .= curl_close($this->ch);

        return $this->execute;
    }
}