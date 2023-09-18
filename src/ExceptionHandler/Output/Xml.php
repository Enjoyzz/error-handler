<?php

declare(strict_types=1);


namespace Enjoys\ErrorHandler\ExceptionHandler\Output;


use Psr\Http\Message\ResponseInterface;

final class Xml extends OutputError
{
    public function getResponse(): ResponseInterface
    {
        $response = $this->getResponseFactory()->createResponse($this->getHttpStatusCode());

        $response->getBody()->write(
            <<<XML
<?xml version="1.0" encoding="utf-8"?>
<error>
    <type>{$this->getError()->type}</type>
    <code>{$this->getError()->code}</code>
    <message>{$this->getError()->message}</message>
</error>
XML
        );
        return $response;
    }
}
