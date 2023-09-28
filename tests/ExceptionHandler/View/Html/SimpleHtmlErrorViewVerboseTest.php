<?php

namespace Enjoys\Tests\Oophps\ExceptionHandler\View\Html;

use Enjoys\Oophps\ExceptionHandler\ExceptionHandler;
use Enjoys\Oophps\ExceptionHandler\OutputProcessor\Html;
use Enjoys\Oophps\ExceptionHandler\View\Html\SimpleHtmlErrorViewVerbose;
use Enjoys\Tests\Oophps\CatchResponse;
use Enjoys\Tests\Oophps\Emitter;
use ErrorException;
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
        $exh->handle(new ErrorException('The error'));
        $response = CatchResponse::getResponse();
        $this->assertStringContainsString(
            'ErrorException(0): The error',
            $response->getBody()->__toString()
        );
    }
}
