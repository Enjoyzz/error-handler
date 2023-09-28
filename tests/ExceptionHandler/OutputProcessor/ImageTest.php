<?php

namespace Enjoys\Tests\Oophps\ExceptionHandler\OutputProcessor;

use Enjoys\Oophps\Error;
use Enjoys\Oophps\ExceptionHandler\ExceptionHandler;
use Enjoys\Oophps\ExceptionHandler\OutputProcessor\Image;
use Enjoys\Oophps\ExceptionHandler\OutputProcessor\OutputError;
use Enjoys\Oophps\ExceptionHandler\View\ErrorView;
use Enjoys\Tests\Oophps\CatchResponse;
use Enjoys\Tests\Oophps\Emitter;
use Exception;
use GdImage;
use HttpSoft\Message\ServerRequestFactory;
use PHPUnit\Framework\TestCase;
use Psr\Http\Message\ResponseInterface;

class ImageTest extends TestCase
{
    public function getAccepts(): array
    {
        return [
            ['image/gif','image/gif'],
            ['image/jpeg','image/jpeg'],
            ['image/png','image/png'],
            ['image/webp','image/webp'],
        ];
    }

    /**
     * @dataProvider getAccepts
     */
    public function testImageProcessorDefault($accept, $mime)
    {
        $exh = new ExceptionHandler(
            request: (new ServerRequestFactory())->createServerRequest('get', '/')->withAddedHeader(
                'Accept',
                $accept
            ),
            emitter: new Emitter()
        );

        $exh->handle(new Exception('The error'));

        $response = CatchResponse::getResponse();
        $body = $response->getBody()->__toString();
        $this->assertNotEmpty($body);

        $this->assertInstanceOf(GdImage::class, imagecreatefromstring($body));
        $size = getimagesizefromstring($body);
        $this->assertSame(200, $size[0]);
        $this->assertSame(200, $size[1]);
        $this->assertSame($mime, $size['mime']);
    }


    public function testImageProcessorWithCustomView()
    {
        $exh = new ExceptionHandler(
            request: (new ServerRequestFactory())->createServerRequest('get', '/')->withAddedHeader(
                'Accept',
                'image/jpeg'
            ),
            emitter: new Emitter()
        );

        $exh->setOutputErrorView(Image::class, new class implements ErrorView
        {
            public function getContent(Error $error, ResponseInterface $response): string
            {
                return 'response';
            }
        });

        $exh->handle(new Exception('The error'));

        $this->assertSame('response', CatchResponse::getResponse()->getBody()->__toString());


    }


}
