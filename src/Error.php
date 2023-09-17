<?php

namespace Enjoys\ErrorHandler;

use Throwable;

final class Error
{
    private function __construct(
        public readonly string $message,
        public readonly string $file,
        public readonly int $line,
        public readonly string $type,
        public readonly int $code,
        public readonly int $errorLevel,
        public readonly string $traceString = '',
        public readonly array $trace = [],
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
