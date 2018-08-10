<?php

namespace GarethMidwood\ApacheHousekeeper\Response;

class Responder 
{
    private $_response;

    public function __construct(Response $response) 
    {
        $this->_response = $response;
    }

    public function send(array $responseData, $responseCode = 200)
    {
        $this->_response->send($responseData, $responseCode);
    }
}
