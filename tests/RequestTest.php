<?php

declare(strict_types=1);

namespace Solital\Test;

use PHPUnit\Framework\TestCase;
use Solital\HttpClient;

class RequestTest extends TestCase
{
    public function testRequest()
    {
        $http_client = new HttpClient();
        #$http_client->bearerToken("token");
        $http_client->basicAuth('user', 'pass');
        $http_client->request("GET", "http://localhost/arquivos/API_TESTE/usuarios/listar/");
        $res = $http_client->toJson();
        $this->assertJson($res);
    }
}
