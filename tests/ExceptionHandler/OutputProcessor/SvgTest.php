<?php

namespace Enjoys\Tests\ErrorHandler\ExceptionHandler\OutputProcessor;

use Enjoys\ErrorHandler\ExceptionHandler\ExceptionHandler;
use Enjoys\ErrorHandler\ExceptionHandler\OutputProcessor\Svg;
use Enjoys\Tests\ErrorHandler\CatchResponse;
use Enjoys\Tests\ErrorHandler\Emitter;
use HttpSoft\Message\ServerRequestFactory;
use PHPUnit\Framework\TestCase;

class SvgTest extends TestCase
{
    public function getAccepts(): array
    {
        return [
            ['image/svg+xml'],
        ];
    }

    /**
     * @dataProvider getAccepts
     */
    public function testSvgProcessorDefault($accept)
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
            <<<SVG
<svg xmlns="http://www.w3.org/2000/svg" width="200">
    <text x="20" y="30" title="Exception">
         Exception
    </text>
    <text x="20" y="60"  title="The error">
        The error
    </text>
</svg>
SVG,
            CatchResponse::getResponse()->getBody()->__toString()
        );
    }
}
