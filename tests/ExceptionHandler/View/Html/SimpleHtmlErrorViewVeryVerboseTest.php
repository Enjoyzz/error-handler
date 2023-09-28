<?php

namespace Enjoys\Tests\Oophps\ExceptionHandler\View\Html;

use Enjoys\Oophps\ExceptionHandler\ExceptionHandler;
use Enjoys\Oophps\ExceptionHandler\OutputProcessor\Html;
use Enjoys\Oophps\ExceptionHandler\View\Html\SimpleHtmlErrorViewVerbose;
use Enjoys\Oophps\ExceptionHandler\View\Html\SimpleHtmlErrorViewVeryVerbose;
use Enjoys\Tests\Oophps\CatchResponse;
use Enjoys\Tests\Oophps\Emitter;
use ErrorException;
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
        $exh->handle(new ErrorException('The error', filename: $file = __FILE__, line: $line = __LINE__));
        $response = CatchResponse::getResponse();
        $this->assertStringContainsString(
            sprintf('ErrorException: The error in %s:%s', $file, $line),
            $response->getBody()->__toString()
        );
    }
}
