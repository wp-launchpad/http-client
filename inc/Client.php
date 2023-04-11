<?php
namespace LaunchpadHTTPClient;

use Psr\Http\Client\ClientInterface;
use Psr\Http\Message\RequestInterface;
use Psr\Http\Message\ResponseInterface;

class Client implements ClientInterface
{

    public function sendRequest(RequestInterface $request): ResponseInterface
    {
        // TODO: Implement sendRequest() method.
    }
}