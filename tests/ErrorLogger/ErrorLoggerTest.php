<?php

namespace Enjoys\Tests\ErrorHandler\ErrorLogger;

use Enjoys\ErrorHandler\Error;
use Enjoys\ErrorHandler\ErrorLogger\ErrorLogger;
use Enjoys\Tests\ErrorHandler\TestLogger;
use Enjoys\Tests\ErrorHandler\TestLoggerInvalidWithName;
use Enjoys\Tests\ErrorHandler\TestLoggerWithName;
use PHPUnit\Framework\TestCase;
use Psr\Log\LoggerInterface;
use Psr\Log\LogLevel;

class ErrorLoggerTest extends TestCase
{

    private LoggerInterface $psrLogger;
    private ErrorLogger $errorLogger;

    protected function setUp(): void
    {
        $this->psrLogger = new TestLogger();
        $this->psrLogger->reset();
        $this->errorLogger = new ErrorLogger($this->psrLogger);
    }

    public function testLogWithDefaultLogLevel()
    {
        $this->errorLogger->log(Error::createFromThrowable(new \Exception('error')));
        $this->assertCount(1, $this->psrLogger->getLogs()[LogLevel::NOTICE] ?? []);
    }

    public function testLogWithManyLogLevel()
    {
        $this->errorLogger->log(Error::createFromThrowable(new \Exception('error')), [
            LogLevel::ERROR, LogLevel::ALERT
        ]);
        $this->assertCount(1, $this->psrLogger->getLogs()[LogLevel::ERROR] ?? []);
        $this->assertCount(1, $this->psrLogger->getLogs()[LogLevel::ALERT] ?? []);
        $this->assertCount(0, $this->psrLogger->getLogs()[LogLevel::NOTICE] ?? []);
    }

    public function testGetPsrLogger()
    {
        self::assertInstanceOf(LoggerInterface::class, $this->errorLogger->getPsrLogger());
    }

    public function testLogDefaultLogFormat()
    {
        $this->errorLogger->log(Error::createFromPhpError(E_USER_ERROR, 'The error message', 'test.php', 42));
        $this->assertCount(1, $this->psrLogger->getLogs()[LogLevel::ERROR] ?? []);
        $this->assertSame('PHP User Error: The error message in test.php on line 42', $this->psrLogger->getLogs()[LogLevel::ERROR][0]);
    }

    public function testLogWithCustomLogFormat()
    {
        $this->errorLogger->setLoggerFormatMessage(E_USER_ERROR, '%2$s in %3$s:%4$s');
        $this->errorLogger->log(Error::createFromPhpError(E_USER_ERROR, 'The error message', 'test.php', 42));
        $this->assertCount(1, $this->psrLogger->getLogs()[LogLevel::ERROR] ?? []);
        $this->assertSame('The error message in test.php:42', $this->psrLogger->getLogs()[LogLevel::ERROR][0]);
    }

    public function testLogWithCustomLoggerLevel()
    {
        $this->errorLogger->setLogLevel(E_USER_ERROR, LogLevel::CRITICAL);
        $this->errorLogger->log(Error::createFromPhpError(E_USER_ERROR, 'The error message', 'test.php', 42));
        $this->assertCount(0, $this->psrLogger->getLogs()[LogLevel::ERROR] ?? []);
        $this->assertCount(1, $this->psrLogger->getLogs()[LogLevel::CRITICAL] ?? []);
    }

    public function testSetDefaultLogLevel()
    {
        $this->errorLogger->setDefaultLogLevel(LogLevel::EMERGENCY);
        $this->errorLogger->log(Error::createFromThrowable(new \Exception('The error')));
        $this->assertCount(0, $this->psrLogger->getLogs()[LogLevel::NOTICE] ?? []);
        $this->assertCount(1, $this->psrLogger->getLogs()[LogLevel::EMERGENCY] ?? []);
    }


    public function testSkipLog()
    {
        $this->errorLogger->log(Error::createFromThrowable(new \Exception('The error')), false);
        $this->assertCount(0, $this->psrLogger->getLogs());
    }

    public function testInvalidSetLogLevel()
    {
        $this->expectException(\RuntimeException::class);
        $this->expectExceptionMessage('InvalidLogLevel - not allowed, allowed only (EMERGENCY, ALERT, CRITICAL, ERROR, WARNING, NOTICE, INFO, DEBUG)');
        $this->errorLogger->setLogLevel([E_USER_DEPRECATED, E_DEPRECATED], 'InvalidLogLevel');
    }

    public function testLoggerName()
    {
        $errorLogger = new ErrorLogger(new TestLoggerWithName());
        $errorLogger->setLoggerName(E_NOTICE, 'CustomLoggerName');
        $errorLogger->log(Error::createFromPhpError(E_DEPRECATED, 'The message', 'test.php', 42));
        $errorLogger->log(Error::createFromPhpError(E_ERROR, 'The message', 'test.php', 42));
        $errorLogger->log(Error::createFromPhpError(E_NOTICE, 'The message', 'test.php', 42));
        $errorLogger->log(Error::createFromPhpError(E_COMPILE_ERROR, 'The message', 'test.php', 42));

        $logger = $errorLogger->getPsrLogger();

        $this->assertCount(3, $logger->getLogs()['TestLogger']);
        $this->assertCount(1, $logger->getLogs()['CustomLoggerName'] ?? []);

    }

    public function testLoggerNameInvalid()
    {
        $logger = new TestLoggerInvalidWithName();
        $this->expectException(\RuntimeException::class);
        $this->expectExceptionMessage(sprintf(
            'The method `withName` must be return of type %s, %s given',
            LoggerInterface::class,
            $logger::class
        ));
        $errorLogger = new ErrorLogger($logger);
        $errorLogger->setLoggerName(E_NOTICE, 'CustomLoggerName');
        $errorLogger->log(Error::createFromPhpError(E_NOTICE, 'The message', 'test.php', 42));

    }
}
