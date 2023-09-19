<?php

namespace Enjoys\Tests\ErrorHandler\ExceptionHandler\OutputProcessor;

use Enjoys\ErrorHandler\ExceptionHandler\ExceptionHandler;
use Enjoys\ErrorHandler\ExceptionHandler\OutputProcessor\Html;
use Enjoys\ErrorHandler\ExceptionHandler\OutputProcessor\OutputError;
use Enjoys\ErrorHandler\ExceptionHandler\View\ErrorView;
use Enjoys\ErrorHandler\ExceptionHandler\View\Html\SimpleHtmlErrorViewVeryVerbose;
use Enjoys\Tests\ErrorHandler\CatchResponse;
use Enjoys\Tests\ErrorHandler\Emitter;
use HttpSoft\Message\ServerRequestFactory;
use PHPUnit\Framework\TestCase;

class HtmlTest extends TestCase
{
    public function getAccepts(): array
    {
        return [
            ['text/html'],
        ];
    }

    /**
     * @dataProvider getAccepts
     */
    public function testHtmlProcessorDefault($accept)
    {
        $exh = new ExceptionHandler(
            request: (new ServerRequestFactory())->createServerRequest('get', '/')->withAddedHeader(
                'Accept',
                $accept
            ),
            emitter: new Emitter()
        );

        $exh->handle(new \Exception('The error'));
        $this->assertStringContainsString(
            '<title>Error 500. Internal Server Error</title>',
            CatchResponse::getResponse()->getBody()->__toString()
        );
    }

    /**
     * @dataProvider getAccepts
     */
    public function testHtmlProcessorWithCustomTemplate($accept)
    {
        $exh = new ExceptionHandler(
            outputErrorViewMap: [
                Html::class => new SimpleHtmlErrorViewVeryVerbose()
            ],
            request: (new ServerRequestFactory())->createServerRequest('get', '/')->withAddedHeader(
                'Accept',
                $accept
            ),
            emitter: new Emitter()
        );

        $exh->handle(new \ErrorException('The error', filename: $file = __FILE__, line: $line = __LINE__));
        $response = CatchResponse::getResponse();
        $this->assertStringContainsString(
            sprintf('ErrorException: The error in %s:%s', $file, $line),
            $response->getBody()->__toString()
        );
    }
}
