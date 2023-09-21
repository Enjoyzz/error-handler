<?php

namespace Enjoys\Tests\ErrorHandler\ExceptionHandler\OutputProcessor;

use Enjoys\ErrorHandler\ExceptionHandler\ExceptionHandler;
use Enjoys\Tests\ErrorHandler\CatchResponse;
use Enjoys\Tests\ErrorHandler\Emitter;
use HttpSoft\Message\Response;
use HttpSoft\Message\ServerRequestFactory;
use PHPUnit\Framework\TestCase;
use Psr\Http\Message\ResponseFactoryInterface;
use Psr\Http\Message\ResponseInterface;

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
            emitter: new Emitter(),
            responseFactory: new class implements ResponseFactoryInterface {
                public function createResponse(
                    int $code = 200,
                    string $reasonPhrase = 'Another responseFactory with custom reason phrase'
                ): ResponseInterface {
                    return new Response($code, [], null, '1.1', $reasonPhrase);
                }
            }
        );

        $exh->handle(new \Exception('The error'));
        $response = CatchResponse::getResponse();
        $this->assertSame('Another responseFactory with custom reason phrase', $response->getReasonPhrase());
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
            $response->getBody()->__toString()
        );
    }
}
