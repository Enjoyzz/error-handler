<?php

declare(strict_types=1);

namespace Enjoys\Tests\ErrorHandler\ExceptionHandler;

use Enjoys\ErrorHandler\ErrorLogger\ErrorLogger;
use Enjoys\ErrorHandler\ExceptionHandler\ExceptionHandler;
use Enjoys\Tests\ErrorHandler\CatchResponse;
use Enjoys\Tests\ErrorHandler\Emitter;
use Enjoys\Tests\ErrorHandler\TestLogger;
use Enjoys\Tests\ErrorHandler\TestLoggerInvalidWithName;
use Enjoys\Tests\ErrorHandler\TestLoggerWithName;
use PHPUnit\Framework\TestCase;
use Psr\Log\LogLevel;

class ExceptionHandlerTest extends TestCase
{

    protected function tearDown(): void
    {
        TestLogger::reset();
        TestLoggerInvalidWithName::reset();
        TestLoggerWithName::reset();
    }

    public function testHttpStatusCodeMap()
    {
        $exh = new ExceptionHandler(
            httpStatusCodeMap: [
                405 => [\DivisionByZeroError::class]
            ],
            emitter: new Emitter()
        );

        $exh->handle(new \DivisionByZeroError());
        $this->assertSame(405, CatchResponse::getResponse()->getStatusCode());

        $exh->handle(new \ArithmeticError());
        $this->assertSame(500, CatchResponse::getResponse()->getStatusCode());

        $exh->setHttpStatusCodeMap([
            405 => ['\ArithmeticError']
        ]);

        $exh->handle(new \ArithmeticError());
        $this->assertSame(405, CatchResponse::getResponse()->getStatusCode());
    }

    public function testSetLoggerTypeMapInConstruct()
    {
        $exh = new ExceptionHandler(
            loggerTypeMap: [
                500 =>  [LogLevel::CRITICAL]
            ],
            logger: new ErrorLogger($psrLogger = new TestLogger()),
            emitter: new Emitter()
        );

        $exh->handle(new \Exception());
        $this->assertSame(500, CatchResponse::getResponse()->getStatusCode());
        $this->assertCount(1, $psrLogger->getLogs()[LogLevel::CRITICAL] ?? []);

    }

    public function testLoggerTypeMap()
    {
        $exh = new ExceptionHandler(
            emitter: new Emitter()
        );
        $exh->setErrorLogger(new ErrorLogger($psrLogger = new TestLogger()));

        $exh->handle(new \DivisionByZeroError());
        $this->assertSame(500, CatchResponse::getResponse()->getStatusCode());

        $exh->setLoggerTypeMap([
            405 => [LogLevel::ERROR],
            \ArithmeticError::class => [LogLevel::CRITICAL]
        ]);

        $exh->handle(new \DivisionByZeroError());
        $this->assertSame(500, CatchResponse::getResponse()->getStatusCode());

        $exh->setHttpStatusCodeMap([
            405 => [\DivisionByZeroError::class]
        ]);
        $exh->handle(new \DivisionByZeroError());
        $this->assertSame(405, CatchResponse::getResponse()->getStatusCode());

        $exh->setHttpStatusCodeMap([
            405 => [\DivisionByZeroError::class, \ArithmeticError::class]
        ]);
        $exh->handle(new \ArithmeticError());
        $this->assertSame(405, CatchResponse::getResponse()->getStatusCode());

        $this->assertCount(2, $psrLogger->getLogs()[LogLevel::ERROR] ?? []);
        $this->assertCount(1, $psrLogger->getLogs()[LogLevel::CRITICAL] ?? []);
    }


}
