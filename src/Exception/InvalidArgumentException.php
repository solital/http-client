<?php

namespace Solital\Exception;

class InvalidArgumentException
{
    public static function alert(int $code, string $type, string $msg)
    {
        include_once "template/error-http.php";
        die;
    }
}
