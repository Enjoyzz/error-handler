<?php

namespace Enjoys\Tests\ErrorHandler\ExceptionHandler\OutputProcessor;

use Enjoys\ErrorHandler\ExceptionHandler\ExceptionHandler;
use Enjoys\Tests\ErrorHandler\CatchResponse;
use Enjoys\Tests\ErrorHandler\Emitter;
use HttpSoft\Message\ServerRequestFactory;
use PHPUnit\Framework\TestCase;

class ImageTest extends TestCase
{
    public function getAccepts(): array
    {
        return [
            ['image/gif'],
            ['image/jpeg'],
            ['image/png'],
            ['image/webp'],
        ];
    }

    /**
     * @dataProvider getAccepts
     */
    public function testImageProcessorDefault($accept)
    {
        $exh = new ExceptionHandler(
            request: (new ServerRequestFactory())->createServerRequest('get', '/')->withAddedHeader(
                'Accept',
                $accept
            ),
            emitter: new Emitter()
        );

        $exh->handle(new \Exception('The error'));

        $response = CatchResponse::getResponse();
        $body = $response->getBody()->__toString();
        $this->assertNotEmpty($body);

        /**
         * Test the string is binary
         * @see https://stackoverflow.com/questions/25343508/detect-if-string-is-binary
         */
        $this->assertDoesNotMatchRegularExpression('//u', $body);
    }
}
