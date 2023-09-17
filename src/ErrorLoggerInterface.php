<?php

declare(strict_types=1);


namespace Enjoys\ErrorHandler;


use Psr\Log\LoggerInterface;

interface ErrorLoggerInterface
{
    public function log(Error $error, array|false $logLevels = null): void;

    public function getPsrLogger(): LoggerInterface;
}
