<?php

namespace Solital;

abstract class CurlMethods
{
    /**
     * Check if there are headers in the request
     */
    protected function verifyHeaders()
    {
        if ($this->headers() == null) {
            $this->headers = [
                'Content-Type: application/json',
                'Accept: application/json',
                'Authorization: Bearer '. $this->token
            ];
        }

        return $this->headers;
    }

    /**
     * Check the request url
     * @param string $url
     */
    protected function verifyUrl(string $url)
    {
        $this->url = curl_setopt($this->ch, CURLOPT_URL, $url);
        $this->url .= curl_setopt($this->ch, CURLOPT_RETURNTRANSFER, true);
        return $this->url;
    }

    /**
     * Check the request method
     * @param string $method
     */
    protected function verifyMethod(string $method)
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
     */
    protected function verifyFields($method, $data)
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
     */
    protected function execute()
    {
        $this->execute = curl_exec($this->ch);
        $this->execute .= curl_close($this->ch);

        return $this->execute;
    }
}
