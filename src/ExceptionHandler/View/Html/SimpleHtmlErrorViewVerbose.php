<?php

declare(strict_types=1);

namespace Enjoys\Oophps\ExceptionHandler\View\Html;

use Enjoys\Oophps\Error;
use Enjoys\Oophps\ExceptionHandler\View\ErrorView;
use Psr\Http\Message\ResponseInterface;

final class SimpleHtmlErrorViewVerbose implements ErrorView
{
    public function getContent(Error $error, ResponseInterface $response): string
    {
        $message = htmlspecialchars(
            sprintf(
                '%s(%s): %s',
                $error->type,
                $error->code,
                $error->message,
            )
        );

        $code = $response->getStatusCode();
        $phrase = $response->getReasonPhrase();

        return <<<HTML
<!DOCTYPE html>
<html lang="en">
<head>
    <title>Error {$code}. {$phrase}</title>
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
    <code><b>{$code}</b><br>{$phrase}
    </code>
    <code style="display: block; margin-top: 2em; color: grey">
    $message
    </code>
</p>
<p>
HTML;
    }

}
