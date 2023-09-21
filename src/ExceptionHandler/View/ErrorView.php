<?php

declare(strict_types=1);


namespace Enjoys\ErrorHandler\ExceptionHandler\View;


use Enjoys\ErrorHandler\Error;
use Enjoys\ErrorHandler\ExceptionHandler\OutputProcessor\OutputError;
use Psr\Http\Message\ResponseInterface;

interface ErrorView
{
    public function getContent(Error $error, ResponseInterface $response): string;
}
