<?php

declare(strict_types=1);

namespace Enjoys\ErrorHandler\ExceptionHandler\View\Html;

use Enjoys\ErrorHandler\ExceptionHandler\ErrorOutputProcessor;
use Enjoys\ErrorHandler\ExceptionHandler\OutputProcessor\OutputError;
use Enjoys\ErrorHandler\ExceptionHandler\View\ErrorView;
use HttpSoft\Message\Response;
use ReflectionClass;

final class SimpleHtmlErrorViewVerbose implements ErrorView
{
    public function getContent(OutputError $processor): string
    {
        /** @var string $phrase */
        $phrase = $this->getPhrase($processor->getHttpStatusCode());

        $message = implode(
            ': ',
            array_filter(
                [
                    $processor->getError()->type,
                    empty($error->code) ? null : $error->code,
                    empty($error->message) ? null : htmlspecialchars($error->message),
                ],
                function ($item) {
                    return !is_null($item);
                }
            )
        );

        return <<<HTML
<!DOCTYPE html>
<html lang="en">
<head>
    <title>Error {$processor->getHttpStatusCode()}. $phrase</title>
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
    <code><b>{$processor->getHttpStatusCode()}</b><br>$phrase
    </code>
    <code style="display: block; margin-top: 2em; color: grey">
    $message
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
