<?php

namespace Enjoys\Tests\ErrorHandler\ExceptionHandler\OutputProcessor;

use Enjoys\ErrorHandler\ExceptionHandler\ExceptionHandler;
use Enjoys\Tests\ErrorHandler\CatchResponse;
use Enjoys\Tests\ErrorHandler\Emitter;
use HttpSoft\Message\ServerRequestFactory;
use PHPUnit\Framework\TestCase;

class PlainTest extends TestCase
{
    /**
     * @dataProvider getAccepts
     */
    public function testPlainProcessorDefault($accept)
    {
        $exh = new ExceptionHandler(
            request: (new ServerRequestFactory())->createServerRequest('get', '/')->withAddedHeader(
                'Accept',
                $accept
            ),
            emitter: new Emitter()
        );

        $exh->handle(new \Exception('The Error'));
        $this->assertSame(
            "Exception\nThe Error",
            CatchResponse::getResponse()->getBody()->__toString()
        );
    }

    public function getAccepts()
    {
        return [
            ['text/plain'],
            ['text/css'],
            ['text/javascript'],
        ];
    }
}
