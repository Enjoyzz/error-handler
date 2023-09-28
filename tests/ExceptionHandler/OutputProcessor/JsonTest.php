<?php

namespace Enjoys\Tests\Oophps\ExceptionHandler\OutputProcessor;

use Enjoys\Oophps\Error;
use Enjoys\Oophps\ExceptionHandler\ExceptionHandler;
use Enjoys\Oophps\ExceptionHandler\OutputProcessor\Json;
use Enjoys\Oophps\ExceptionHandler\View\ErrorView;
use Enjoys\Tests\Oophps\CatchResponse;
use Enjoys\Tests\Oophps\Emitter;
use Exception;
use HttpSoft\Message\ServerRequestFactory;
use PHPUnit\Framework\TestCase;
use Psr\Http\Message\ResponseInterface;

class JsonTest extends TestCase
{
    public function getAccepts(): array
    {
        return [
            ['text/json'],
            ['application/json'],
        ];
    }

    /**
     * @dataProvider getAccepts
     */
    public function testJsonProcessorDefault($accept)
    {
        $exh = new ExceptionHandler(
            request: (new ServerRequestFactory())->createServerRequest('get', '/')->withAddedHeader(
                'Accept',
                $accept
            ),
            emitter: new Emitter()
        );

        $exh->handle(new Exception('The error'));
        $this->assertSame(
            '{"error":{"type":"Exception","code":0,"message":"The error"}}',
            CatchResponse::getResponse()->getBody()->__toString()
        );
    }

    /**
     * @dataProvider getAccepts
     */
    public function testJsonProcessorWithCustomTemplate($accept)
    {
        $exh = new ExceptionHandler(
            request: (new ServerRequestFactory())->createServerRequest('get', '/')->withAddedHeader(
                'Accept',
                $accept
            ),
            emitter: new Emitter()
        );
        $exh->setOutputErrorView(
            Json::class,
            new class implements ErrorView {

                public function getContent(Error $error, ResponseInterface $response): string
                {
                    return json_encode(
                        [
                            'error' => [
                                'httpStatusCode' => $response->getStatusCode(),
                                'message' => $error->message
                            ]
                        ]
                    );
                }
            }
        );

        $exh->handle(new Exception('The error'));
        $this->assertSame(
            '{"error":{"httpStatusCode":500,"message":"The error"}}',
            CatchResponse::getResponse()->getBody()->__toString()
        );
    }
}
