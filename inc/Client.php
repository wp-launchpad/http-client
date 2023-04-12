<?php
namespace LaunchpadHTTPClient;

use HttpSoft\Message\ResponseFactory;
use HttpSoft\Message\StreamFactory;
use Psr\Http\Client\ClientInterface;
use Psr\Http\Message\RequestInterface;
use Psr\Http\Message\ResponseFactoryInterface;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\StreamFactoryInterface;
use Psr\Http\Message\UriInterface;

class Client implements ClientInterface
{

    /**
     * @var ResponseFactoryInterface
     */
    protected $responseFactory;

    /**
     * @var StreamFactoryInterface
     */
    protected $streamFactory;

    /**
     * @param ResponseFactoryInterface|null $responseFactory
     * @param StreamFactoryInterface|null $streamFactory
     */
    public function __construct(ResponseFactoryInterface $responseFactory = null, StreamFactoryInterface $streamFactory = null)
    {
        $this->responseFactory = $responseFactory ?: new ResponseFactory();
        $this->streamFactory = $streamFactory ?: new StreamFactory();
    }


    public function sendRequest(RequestInterface $request): ResponseInterface
    {
        $headers = $request->getHeaders();
        $request->getProtocolVersion();
        $body = $request->getBody()->getContents();
        $args = [
            'method' => $request->getMethod(),
            'httpversion' => $request->getProtocolVersion(),
        ];
        $request->getRequestTarget();

        if($body) {
            $args['body'] = $body;
        }

        if(count($headers) > 0) {
            $args['headers'] = $headers;
        }

        return $this->generateResponse(wp_remote_request($this->generateURL($request->getUri()), $args));
    }

    protected function generateURL(UriInterface $uri): string {

        if(! $uri->getHost()) {
            throw new RequestException('The URI has no Host');
        }

        $url = '';

        if ($uri->getScheme() !== '') {
            $url .= $uri->getScheme() . ':';
        }

        $authority = $uri->getAuthority();

        if ($authority !== '') {
            $url .= '//' . $authority;
        }

        $path = $uri->getPath();

        if ($path !== '') {
            if ($authority === '') {
                $url .= $path === '/' ? '/' . ltrim($path, '/') : $path;
            } else {
                $url .= $path[0] === '/' ? $path : '/' . $path;
            }
        }

        if ($uri->getQuery() !== '') {
            $url .= '?' . $uri->getQuery();
        }

        if ($uri->getFragment() !== '') {
            $url .= '#' . $uri->getFragment();
        }

        return $url;
    }

    protected function generateResponse($response): ResponseInterface {

        if(! is_array( $response )) {
            return $this->responseFactory->createResponse($response->get_error_code(), $response->get_error_message());
        }

        $response = $this->responseFactory->createResponse(wp_remote_retrieve_response_code( $response ), wp_remote_retrieve_response_message( $response ));

        $response_body = wp_remote_retrieve_body( $response );
        if($response_body) {
            $response->withBody($this->streamFactory->createStream($response_body));
        }

        return $response;
    }
}