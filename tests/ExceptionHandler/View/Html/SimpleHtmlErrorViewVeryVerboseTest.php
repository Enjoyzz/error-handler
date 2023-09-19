<?php

namespace Enjoys\Tests\ErrorHandler\ExceptionHandler\View\Html;

use Enjoys\ErrorHandler\ExceptionHandler\ExceptionHandler;
use Enjoys\ErrorHandler\ExceptionHandler\OutputProcessor\Html;
use Enjoys\ErrorHandler\ExceptionHandler\View\Html\SimpleHtmlErrorViewVerbose;
use Enjoys\ErrorHandler\ExceptionHandler\View\Html\SimpleHtmlErrorViewVeryVerbose;
use Enjoys\Tests\ErrorHandler\CatchResponse;
use Enjoys\Tests\ErrorHandler\Emitter;
use PHPUnit\Framework\TestCase;

class SimpleHtmlErrorViewVeryVerboseTest extends TestCase
{
    public function testResponse()
    {
        $exh = new ExceptionHandler(
            outputErrorViewMap: [
                Html::class => new SimpleHtmlErrorViewVeryVerbose()
            ],
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
