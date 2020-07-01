<?php

namespace Solital\Exception;

class NotFoundException
{
    public static function alert(int $code, string $msg)
    {
        include_once "template/error-http.php";
        die;
    }
}
