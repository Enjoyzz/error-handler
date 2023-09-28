<?php

namespace Enjoys\Tests\Oophps\ExceptionHandler\OutputProcessor;

use Enjoys\Oophps\ExceptionHandler\ExceptionHandler;
use Enjoys\Tests\Oophps\CatchResponse;
use Enjoys\Tests\Oophps\Emitter;
use Exception;
use HttpSoft\Message\ServerRequestFactory;
use PHPUnit\Framework\TestCase;

class XmlTest extends TestCase
{
    public function getAccepts(): array
    {
        return [
            ['text/xml'],
            ['application/xml'],
        ];
    }

    /**
     * @dataProvider getAccepts
     */
    public function testXmlProcessorDefault($accept)
    {
        $exh = new ExceptionHandler(
            request: (new ServerRequestFactory())->createServerRequest('get', '/')->withAddedHeader(
                'Accept',
                $accept
            ),
            emitter: new Emitter()
        );

        $exh->handle(new Exception('The error'));
        $this->assertSame(
            <<<XML
<?xml version="1.0" encoding="utf-8"?>
<error>
    <type>Exception</type>
    <code>0</code>
    <message>The error</message>
</error>
XML,
            CatchResponse::getResponse()->getBody()->__toString()
        );
    }
}
