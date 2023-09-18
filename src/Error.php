<?php

namespace Enjoys\ErrorHandler;

use Throwable;

final class Error
{
    private function __construct(
        public string $message,
        public string $file,
        public int $line,
        public string $type,
        public int $code,
        public int $errorLevel,
        public string $traceString = '',
        public array $trace = [],
    ) {
    }

    public static function createFromPhpError(int $severity, string $message, string $file, int $line): Error
    {
        return new self(
            message: $message,
            file: $file,
            line: $line,
            type: '',
            code: 0,
            errorLevel: $severity,
            traceString: '',
            trace: []
        );
    }

    public static function createFromThrowable(Throwable $error): Error
    {
        return new self(
            message: $error->getMessage(),
            file: $error->getFile(),
            line: $error->getLine(),
            type: $error::class,
            code: $error->getCode(),
            errorLevel: 0,
            traceString: $error->getTraceAsString(),
            trace: $error->getTrace(),
        );
    }
}
