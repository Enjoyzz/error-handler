<?php

namespace Enjoys\Tests\ErrorHandler\ExceptionHandler\OutputProcessor;

use Enjoys\ErrorHandler\ExceptionHandler\ExceptionHandler;
use Enjoys\ErrorHandler\ExceptionHandler\OutputProcessor\OutputError;
use Enjoys\ErrorHandler\ExceptionHandler\OutputProcessor\Plain;
use Enjoys\ErrorHandler\ExceptionHandler\View\ErrorView;
use Enjoys\Tests\ErrorHandler\CatchResponse;
use Enjoys\Tests\ErrorHandler\Emitter;
use HttpSoft\Message\ServerRequestFactory;
use PHPUnit\Framework\TestCase;

class PlainTest extends TestCase
{
    public function getAccepts(): array
    {
        return [
            ['text/plain'],
            ['text/css'],
            ['text/javascript'],
        ];
    }

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


    /**
     * @dataProvider getAccepts
     */
    public function testPlainProcessorWithCustomTemplate($accept)
    {
        $exh = new ExceptionHandler(
            request: (new ServerRequestFactory())->createServerRequest('get', '/')->withAddedHeader(
                'Accept',
                $accept
            ),
            emitter: new Emitter()
        );
        $exh->setOutputErrorViewMap([Plain::class => new class implements ErrorView {

                public function getContent(OutputError $processor): string
                {
                    return sprintf('%s: %s', $processor->getHttpStatusCode(), $processor->getError()->message);
                }
            }]
        );

        $exh->handle(new \Exception('The error'));
        $this->assertSame(
            '500: The error',
            CatchResponse::getResponse()->getBody()->__toString()
        );
    }
}
