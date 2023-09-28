<?php

declare(strict_types=1);


namespace Enjoys\Oophps;


use Psr\Log\LoggerInterface;

interface ErrorLoggerInterface
{
    /**
     * @param Error $error
     * @param list<string>|false|null $logLevels
     * @param callable(Error):array|null $logContextCallable
     * @return void
     */
    public function log(Error $error, array|false $logLevels = null, ?callable $logContextCallable = null): void;

    public function getPsrLogger(): LoggerInterface;
}
