<?php

namespace Enjoys\Tests\ErrorHandler\ExceptionHandler\OutputProcessor;

use Enjoys\ErrorHandler\ExceptionHandler\ExceptionHandler;
use Enjoys\Tests\ErrorHandler\CatchResponse;
use Enjoys\Tests\ErrorHandler\Emitter;
use HttpSoft\Message\ServerRequestFactory;
use PHPUnit\Framework\TestCase;

class JsonTest extends TestCase
{
    public function testJsonProcessorDefault()
    {
        $exh = new ExceptionHandler(
            request: (new ServerRequestFactory())->createServerRequest('get', '/')->withAddedHeader(
                'Accept',
                'application/json'
            ),
            emitter: new Emitter()
        );

        $exh->handle(new \Exception());
        $this->assertSame(
            '{"error":{"type":"Exception","code":0,"message":""}}',
            CatchResponse::getResponse()->getBody()->__toString()
        );
    }
}
