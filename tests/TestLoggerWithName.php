<?php

namespace Enjoys\Tests\ErrorHandler;

use Psr\Log\LoggerInterface;
use Psr\Log\LogLevel;

class TestLoggerWithName extends TestLogger
{

    private string $name = 'TestLogger';


    public function withName(string $name): TestLogger
    {
        $new = clone $this;
        $new->name = $name;
        return $new;
    }

    public function log($level, \Stringable|string $message, array $context = []): void
    {
        self::$logs[$this->name][$level][] = $message;
    }



}
