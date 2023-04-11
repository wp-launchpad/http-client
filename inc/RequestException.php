<?php

namespace LaunchpadHTTPClient;

use Psr\Http\Client\RequestExceptionInterface;
use Psr\Http\Message\RequestInterface;

class RequestException extends \Exception implements RequestExceptionInterface
{

    /**
     * @inheritDoc
     */
    public function getRequest(): RequestInterface
    {
        // TODO: Implement getRequest() method.
    }
}