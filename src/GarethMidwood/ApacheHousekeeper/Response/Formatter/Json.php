<?php

namespace GarethMidwood\ApacheHousekeeper\Response\Formatter;

use GarethMidwood\ApacheHousekeeper\Response\Response;

class Json implements Response
{
    public function send(array $responseData, $responseCode)
    {
        header('Content-type:application/json;charset=utf-8');

        http_response_code($responseCode);

        echo json_encode($responseData) . PHP_EOL;
    }
}
