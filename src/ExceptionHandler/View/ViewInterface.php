<?php

declare(strict_types=1);


namespace Enjoys\ErrorHandler\ExceptionHandler\View;


use Enjoys\ErrorHandler\Error;
use Enjoys\ErrorHandler\ExceptionHandlerInterface;

interface ViewInterface
{
    public function getContent(Error $error, int $statusCode = ExceptionHandlerInterface::DEFAULT_STATUS_CODE): string;
}
