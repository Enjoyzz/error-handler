<?php

namespace Enjoys\Tests\ErrorHandler\ExceptionHandler\OutputProcessor;

use Enjoys\ErrorHandler\ExceptionHandler\ExceptionHandler;
use Enjoys\ErrorHandler\ExceptionHandler\OutputProcessor\Html;
use Enjoys\ErrorHandler\ExceptionHandler\OutputProcessor\Json;
use Enjoys\ErrorHandler\ExceptionHandler\OutputProcessor\OutputError;
use Enjoys\ErrorHandler\ExceptionHandler\View\ErrorView;
use Enjoys\Tests\ErrorHandler\CatchResponse;
use Enjoys\Tests\ErrorHandler\Emitter;
use HttpSoft\Message\ServerRequestFactory;
use PHPUnit\Framework\TestCase;

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

        $exh->handle(new \Exception('The error'));
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
        $exh->setOutputErrorView(Json::class, new class implements ErrorView {

                public function getContent(OutputError $processor): string
                {
                    return json_encode(
                        [
                            'error' => [
                                'httpStatusCode' => $processor->getHttpStatusCode(),
                                'message' => $processor->getError()->message
                            ]
                        ]
                    );
                }
            }
        );

        $exh->handle(new \Exception('The error'));
        $this->assertSame(
            '{"error":{"httpStatusCode":500,"message":"The error"}}',
            CatchResponse::getResponse()->getBody()->__toString()
        );
    }
}