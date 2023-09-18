<?php

namespace Enjoys\Tests\ErrorHandler;

use Psr\Log\LoggerInterface;
use Psr\Log\LogLevel;

class TestLoggerInvalidWithName extends TestLogger
{
    private string $name = 'TestLogger';

    public function withName(string $name): bool
    {
        return true;
    }

    public function log($level, \Stringable|string $message, array $context = []): void
    {
        self::$logs[$this->name][$level][] = $message;
    }

}
