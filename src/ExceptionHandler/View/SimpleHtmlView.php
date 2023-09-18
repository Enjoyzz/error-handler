<?php

declare(strict_types=1);

namespace Enjoys\ErrorHandler\ExceptionHandler\View;

use Enjoys\ErrorHandler\Error;
use Enjoys\ErrorHandler\ExceptionHandlerInterface;
use HttpSoft\Message\Response;
use ReflectionClass;

final class SimpleHtmlView implements ViewInterface
{
    public function getContent(Error $error, int $statusCode = ExceptionHandlerInterface::DEFAULT_STATUS_CODE): string
    {
        /** @var string $phrase */
        $phrase = $this->getPhrase($statusCode);

        return <<<HTML
<!DOCTYPE html>
<html lang="en">
<head>
    <title>Error $statusCode. $phrase</title>
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
    <code><b>$statusCode</b><br>$phrase
    </code>
</p>
<p>
HTML;
    }

    /**
     * @param int $statusCode
     * @return mixed|string
     */
    private function getPhrase(int $statusCode): mixed
    {
        $reflection = new ReflectionClass(Response::class);
        $phrases = $reflection->getProperty('phrases');
        $phrases->setAccessible(true);
        return $phrases->getValue()[$statusCode] ?? 'Unknown error';
    }
}
