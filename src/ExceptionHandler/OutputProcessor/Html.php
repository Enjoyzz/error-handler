<?php

declare(strict_types=1);

namespace Enjoys\ErrorHandler\ExceptionHandler\OutputProcessor;

use Psr\Http\Message\ResponseInterface;

final class Html extends OutputError
{


    public function getResponse(): ResponseInterface
    {
        $response = $this->getResponseFactory()->createResponse($this->getHttpStatusCode());
        $response->getBody()->write(
            $this->getView()?->getContent($this) ?? $this->getDefaultBody()
        );
        return $response;
    }

    /**
     * @return string
     */
    public function getDefaultBody(): string
    {
        return <<<HTML
<!DOCTYPE html>
<html lang="en">
<head>
    <title>Error {$this->getHttpStatusCode()}. {$this->getHttpReasonPhrase()}</title>
    <style>
        body {

            margin: 0 1em;
            font-family: Tahoma, Verdana, Arial, sans-serif;
        }

        code {
            font-size: 120%;
        }
    </style>
</head>
<body>
<h1>An error occurred.</h1>
<p>Sorry, the page you are looking for is currently unavailable.<br/>
    Please try again later.</p>
<p>If you are the system administrator of this resource then you should check
    the error log for details.</p>
<p>
    <code><b>{$this->getHttpStatusCode()}</b><br>{$this->getHttpReasonPhrase()}
    </code>
</p>
<p>
HTML;
    }


}
