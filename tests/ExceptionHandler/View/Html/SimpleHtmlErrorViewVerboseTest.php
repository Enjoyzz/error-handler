<?php

namespace Enjoys\Tests\ErrorHandler\ExceptionHandler\View\Html;

use Enjoys\ErrorHandler\ExceptionHandler\ExceptionHandler;
use Enjoys\ErrorHandler\ExceptionHandler\OutputProcessor\Html;
use Enjoys\ErrorHandler\ExceptionHandler\View\Html\SimpleHtmlErrorViewVerbose;
use Enjoys\Tests\ErrorHandler\CatchResponse;
use Enjoys\Tests\ErrorHandler\Emitter;
use PHPUnit\Framework\TestCase;

class SimpleHtmlErrorViewVerboseTest extends TestCase
{
    public function testResponse()
    {
        $exh = new ExceptionHandler(
            outputErrorViewMap: [
                Html::class => SimpleHtmlErrorViewVerbose::class
            ],
            emitter: new Emitter()
        );
        $exh->handle(new \ErrorException('The error'));
        $response = CatchResponse::getResponse();
        $this->assertStringContainsString(
            'ErrorException(0): The error',
            $response->getBody()->__toString()
        );
    }
}
