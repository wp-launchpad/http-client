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
        $method = $request->getMethod();

        if(! $method) {
            throw new RequestException('The method is invalid');
        }

        $headers = $request->getHeaders();
        $body = $request->getBody()->getContents();

        $args = [
            'method' => $method,
            'httpversion' => $request->getProtocolVersion(),
        ];

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

        $scheme = $uri->getScheme();

        if ( $scheme !== '') {
            $url .= $scheme . ':';
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

        $query = $uri->getQuery();

        if ($query !== '') {
            $url .= '?' . $query;
        }

        $fragment = $uri->getFragment();

        if ($fragment !== '') {
            $url .= '#' . $fragment;
        }

        return $url;
    }

    protected function generateResponse($response): ResponseInterface {

        if( is_wp_error( $response ) ) {
            return $this->responseFactory->createResponse($response->get_error_code(), $response->get_error_message());
        }

        if(! wp_remote_retrieve_response_code( $response )) {
            throw new NetworkException('The request cannot be sent');
        }

        $response = $this->responseFactory->createResponse(wp_remote_retrieve_response_code( $response ), wp_remote_retrieve_response_message( $response ));

        $response_body = wp_remote_retrieve_body( $response );
        if($response_body) {
            $response->withBody($this->streamFactory->createStream($response_body));
        }

        $headers = wp_remote_retrieve_headers( $response );

        foreach ($headers as $header => $value) {
            $response->withHeader($header, $value);
        }

        return $response;
    }
}