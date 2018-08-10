<?php

namespace GarethMidwood\ApacheHousekeeper\Response;

interface Response
{
    public function send(array $responseData, $httpResponseCode);
}
