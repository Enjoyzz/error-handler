<?php

namespace Enjoys\Tests\Oophps\ExceptionHandler\OutputProcessor;

use Enjoys\Oophps\Error;
use Enjoys\Oophps\ExceptionHandler\ExceptionHandler;
use Enjoys\Oophps\ExceptionHandler\OutputProcessor\OutputError;
use Enjoys\Oophps\ExceptionHandler\OutputProcessor\Plain;
use Enjoys\Oophps\ExceptionHandler\View\ErrorView;
use Enjoys\Tests\Oophps\CatchResponse;
use Enjoys\Tests\Oophps\Emitter;
use Exception;
use HttpSoft\Message\ServerRequestFactory;
use PHPUnit\Framework\TestCase;
use Psr\Http\Message\ResponseInterface;

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

        $exh->handle(new Exception('The Error'));
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

                public function getContent(Error $error, ResponseInterface $response): string
                {
                    return sprintf('%s: %s', $response->getStatusCode(), $error->message);
                }
            }]
        );

        $exh->handle(new Exception('The error'));
        $this->assertSame(
            '500: The error',
            CatchResponse::getResponse()->getBody()->__toString()
        );
    }
}
