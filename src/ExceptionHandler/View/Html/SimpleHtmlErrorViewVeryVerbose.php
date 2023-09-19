<?php

declare(strict_types=1);

namespace Enjoys\ErrorHandler\ExceptionHandler\View\Html;

use Enjoys\ErrorHandler\ExceptionHandler\OutputProcessor\OutputError;
use Enjoys\ErrorHandler\ExceptionHandler\View\ErrorView;
use HttpSoft\Message\Response;
use ReflectionClass;

final class SimpleHtmlErrorViewVeryVerbose implements ErrorView
{
    public function getContent(OutputError $processor): string
    {
        $message = htmlspecialchars(
            sprintf('%s: %s in %s:%s', $processor->getError()->type,  $processor->getError()->message,  $processor->getError()->file,  $processor->getError()->line)
        );

        return <<<HTML
<!DOCTYPE html>
<html lang="en">
<head>
    <title>Error {$processor->getHttpStatusCode()}. {$processor->getHttpReasonPhrase()}</title>
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
    <code><b>{$processor->getHttpStatusCode()}</b><br>{$processor->getHttpReasonPhrase()}
    </code>
    <div style="font-family: monospace; display: block; margin-top: 2em; color: grey">
    $message
    </div>
</p>
<p>
HTML;
    }

}
