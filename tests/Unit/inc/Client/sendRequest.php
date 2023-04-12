<?php

namespace Unit\inc\Client;

use HttpSoft\Message\RequestFactory;
use LaunchpadHTTPClient\Client;
use LaunchpadHTTPClient\Tests\Unit\TestCase;
use Mockery;
use Psr\Http\Message\RequestFactoryInterface;
use Psr\Http\Message\RequestInterface;
use Psr\Http\Message\ResponseFactoryInterface;
use Psr\Http\Message\ResponseInterface;

class Test_SendRequest extends TestCase
{
    protected $client;
    protected $requestFactory;
    protected $responseFactory;
    protected $response;

    protected function setUp(): void
    {
        parent::setUp();
        $this->request = Mockery::mock(RequestInterface::class);
        $this->responseFactory = Mockery::mock(ResponseFactoryInterface::class);
        $this->response = Mockery::mock(ResponseInterface::class);
        $this->client = new Client($this->responseFactory);
    }

    /**
     * @dataProvider configTestData
     */
    public function testShouldReturnAsExpected($config, $excepted) {
        $this->configureRequest($config);
        $this->configureError($config, $excepted);
        $response = $this->client->sendRequest($this->request);

        $this->assertSame($response, $this->response);
    }

    protected function configureRequest($config) {

    }

    protected function configureError($config, $expected) {
        if(! key_exists('error', $config)) {
            return;
        }

        $this->expectException($expected['exception']);
    }
}