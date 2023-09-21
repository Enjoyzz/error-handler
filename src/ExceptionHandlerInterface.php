<?php

declare(strict_types=1);


namespace Enjoys\ErrorHandler;


use Throwable;

interface ExceptionHandlerInterface
{
    public function handle(Throwable $error, int $httpStatusCode = ErrorHandler::DEFAULT_HTTP_STATUS_CODE): void;
}
