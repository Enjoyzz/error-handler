<?php

namespace Enjoys\Tests\Oophps;

use ArithmeticError;
use DivisionByZeroError;
use Enjoys\Oophps\Error;
use Enjoys\Oophps\ErrorHandler;
use Enjoys\Oophps\ErrorLogger\ErrorLogger;
use Enjoys\Oophps\ErrorLoggerInterface;
use Enjoys\Oophps\ExceptionHandler\ExceptionHandler;
use Enjoys\Oophps\ExceptionHandlerInterface;
use ErrorException;
use Exception;
use PHPUnit\Framework\TestCase;
use Psr\Log\LogLevel;
use ReflectionClass;
use RuntimeException;

class ErrorHandlerTest extends TestCase
{

    private const FATAL_ERROR_LEVEL = E_ERROR
    | E_PARSE
    | E_CORE_ERROR
    | E_CORE_WARNING
    | E_COMPILE_ERROR
    | E_COMPILE_WARNING
    | E_USER_ERROR;


    protected function tearDown(): void
    {
        restore_error_handler();
        restore_exception_handler();
        TestLogger::reset();
    }

    public function testRegister()
    {
        $errorHandler = new ErrorHandler(
            $this->createMock(ExceptionHandlerInterface::class),
            $this->createMock(ErrorLoggerInterface::class)
        );
        $errorHandler->register();

        $this->assertSame('0', ini_get('display_errors'));
        $this->assertSame([$errorHandler, 'exceptionHandler'], set_exception_handler(null));
        $this->assertSame([$errorHandler, 'errorHandler'], set_error_handler(null));
        $this->assertTrue($errorHandler->isRegisteredShutdownFunction());

        $reflection = new ReflectionClass($errorHandler);
        $isRegistered = $reflection->getProperty('registered');
        $isRegistered->setAccessible(true);
        $this->assertTrue($isRegistered->getValue($errorHandler));
    }

    public function testUnRegister()
    {
        $errorHandler = new ErrorHandler(
            $this->createMock(ExceptionHandlerInterface::class),
            $this->createMock(ErrorLoggerInterface::class)
        );

        set_exception_handler(null);
        set_error_handler(null);

        $errorHandler->register();
        $errorHandler->unregister();

        $this->assertSame(null, set_exception_handler(null));
        $this->assertSame(null, set_error_handler(null));

        $reflection = new ReflectionClass($errorHandler);
        $isRegistered = $reflection->getProperty('registered');
        $isRegistered->setAccessible(true);
        $this->assertFalse($isRegistered->getValue($errorHandler));
    }

    public function testExceptionHandler()
    {
        $exceptionHandler = $this->createMock(ExceptionHandlerInterface::class);
        $exceptionHandler->expects($this->once())->method('handle');
        $errorHandler = new ErrorHandler(
            $exceptionHandler,
            $this->createMock(ErrorLoggerInterface::class)
        );
        $errorHandler->register();
        $reflection = new ReflectionClass($errorHandler);
        $isRegistered = $reflection->getProperty('registered');
        $isRegistered->setAccessible(true);
        $this->assertTrue($isRegistered->getValue($errorHandler));
        $errorHandler->exceptionHandler(new Exception());
        $this->assertFalse($isRegistered->getValue($errorHandler));
    }

    public function testFatalError()
    {
        $exceptionHandler = $this->createMock(ExceptionHandlerInterface::class);
        $errorHandler = new ErrorHandler(
            $exceptionHandler,
            $this->createMock(ErrorLoggerInterface::class)
        );


        $this->assertSame(
            self::FATAL_ERROR_LEVEL,
            $errorHandler->getFatalErrorLevel()
        );
        $errorHandler->addFatalError(E_USER_DEPRECATED | E_DEPRECATED);
        $this->assertSame(
            self::FATAL_ERROR_LEVEL | E_USER_DEPRECATED | E_DEPRECATED,
            $errorHandler->getFatalErrorLevel()
        );
        $errorHandler->resetFatalErrorLevel();
        $this->assertSame(
            self::FATAL_ERROR_LEVEL,
            $errorHandler->getFatalErrorLevel()
        );
    }

    public function testGetErrorLogger()
    {
        $errorHandler = new ErrorHandler(
            $this->createMock(ExceptionHandlerInterface::class),
            $this->createMock(ErrorLoggerInterface::class)
        );

        $this->assertInstanceOf(ErrorLoggerInterface::class, $errorHandler->getErrorLogger());
    }

    public function testShutdownFunction()
    {
        $invalidPath = uniqid('invalid_path');
        $this->expectException(ErrorException::class);
        $this->expectExceptionCode(0);
        $this->expectExceptionMessageMatches(
            sprintf(
                '/PHP Warning: fopen\(%s\): Failed to open/',
                $invalidPath
            )
        );
        $errorHandler = new ErrorHandler(
            $this->createMock(ExceptionHandlerInterface::class),
            $this->createMock(ErrorLoggerInterface::class)
        );
        $errorHandler->addFatalError(E_WARNING);
        @fopen($invalidPath, 'r');
        $errorHandler->shutdownFunction();
    }


    public function dataForTestErrorHandler()
    {
        return [
            [true, E_USER_ERROR, 0, E_ALL],
            [false, E_USER_WARNING, 0, E_ALL, 'warning'],
            [true, E_USER_WARNING, E_USER_WARNING, E_ALL],
            [false, E_WARNING, E_USER_WARNING, E_ALL, 'warning'],
            [true, E_DEPRECATED, E_DEPRECATED | E_USER_DEPRECATED, E_ALL],
            [true, E_USER_DEPRECATED, E_DEPRECATED | E_USER_DEPRECATED, E_ALL],
            [false, E_USER_DEPRECATED, 0, E_ALL, 'warning'],
            [true, E_ERROR, 0, E_ALL],
            [true, E_USER_NOTICE, E_ALL, E_ALL],
            [true, E_RECOVERABLE_ERROR, E_ALL, E_ALL],
            [false, E_RECOVERABLE_ERROR, 0, E_ALL, 'notice'],
            [false, E_USER_WARNING, E_USER_WARNING, E_ALL & ~E_USER_WARNING, 'warning'],
        ];
    }


    /**
     * @dataProvider dataForTestErrorHandler
     */
    public function testErrorHandler(
        $expectException,
        $severity,
        $addedFatalErrorLevels = 0,
        $errorReporting = E_ALL,
        $logLevel = null
    ) {
        error_reporting($errorReporting);

        $errorHandler = new ErrorHandler(
            $this->createMock(ExceptionHandlerInterface::class),
            new ErrorLogger($logger = new TestLogger())
        );
        $errorHandler->addFatalError($addedFatalErrorLevels);


        if ($expectException) {
            $this->expectException(ErrorException::class);
            $this->expectExceptionCode(0);
            $this->expectExceptionMessageMatches(
                sprintf(
                    '/%s:/',
                    ErrorHandler::ERROR_NAMES[$severity] ?? ''
                )
            );
        }

        $this->assertTrue(
            $errorHandler->errorHandler($severity, sprintf('The Error Message: %s', __METHOD__), __FILE__, __LINE__)
        );

        $this->assertMatchesRegularExpression(
            sprintf(
                '/%s:/',
                ErrorHandler::ERROR_NAMES[$severity] ?? ':'
            ),
            $logger->getLogs()[$logLevel][0]['message'] ?? ''
        );
    }

    public function testSetDisplayErrors()
    {
        ErrorHandler::displayErrors(true);
        $this->assertSame('1', ini_get('display_errors'));
    }

    public function testSetLoggerForExceptionHandlerInConstruct()
    {
        $logger = $this->createMock(ErrorLoggerInterface::class);
        $logger->expects($this->once())->method('log');
        $errorHandler = new ErrorHandler(new ExceptionHandler(emitter: new Emitter()), $logger);
        $errorHandler->exceptionHandler(new RuntimeException());
    }

    public function testHttpStatusCodeMap()
    {

        $errorHandler = new ErrorHandler(
            new ExceptionHandler(emitter: new Emitter()),
            $this->createMock(ErrorLoggerInterface::class)
        );

        $errorHandler->setHttpStatusCodeMap([
            405 => [DivisionByZeroError::class]
        ]);

        $errorHandler->exceptionHandler(new DivisionByZeroError());
        $this->assertSame(405, CatchResponse::getResponse()?->getStatusCode());

        $errorHandler->exceptionHandler(new ArithmeticError());
        $this->assertSame(500, CatchResponse::getResponse()?->getStatusCode());

        $errorHandler->setHttpStatusCodeMap([
            405 => ['\ArithmeticError']
        ]);

        $errorHandler->exceptionHandler(new ArithmeticError());
        $this->assertSame(405, CatchResponse::getResponse()?->getStatusCode());
    }


    public function testLoggerTypeMap()
    {
        $errorHandler = new ErrorHandler(
            new ExceptionHandler(emitter: new Emitter()),
            new ErrorLogger($psrLogger = new TestLogger())
        );

        $errorHandler->exceptionHandler(new DivisionByZeroError());
        $this->assertSame(500, CatchResponse::getResponse()->getStatusCode());

        $errorHandler->setLoggerTypeMap([
            405 => [LogLevel::ERROR],
            ArithmeticError::class => [LogLevel::CRITICAL]
        ]);

        $errorHandler->exceptionHandler(new DivisionByZeroError());
        $this->assertSame(500, CatchResponse::getResponse()->getStatusCode());

        $errorHandler->setHttpStatusCodeMap([
            405 => [DivisionByZeroError::class]
        ]);
        $errorHandler->exceptionHandler(new DivisionByZeroError());
        $this->assertSame(405, CatchResponse::getResponse()->getStatusCode());

        $errorHandler->setHttpStatusCodeMap([
            405 => [DivisionByZeroError::class, ArithmeticError::class]
        ]);
        $errorHandler->exceptionHandler(new ArithmeticError());
        $this->assertSame(405, CatchResponse::getResponse()->getStatusCode());

        $this->assertCount(2, $psrLogger->getLogs()[LogLevel::ERROR] ?? []);
        $this->assertCount(1, $psrLogger->getLogs()[LogLevel::CRITICAL] ?? []);
    }

    public function testLogContext()
    {
        $errorHandler = new ErrorHandler(
            new ExceptionHandler(emitter: new Emitter()),
            new ErrorLogger($psrLogger = new TestLogger())
        );
        $errorHandler->setLogContextCallable(function ($e){
            return ['param_type' => $e::class];
        });
        $errorHandler->exceptionHandler(new Exception());
        $this->assertSame(['param_type' => Error::class], $psrLogger->getLogs()[LogLevel::ERROR][0]['context'] ?? []);
        $errorHandler->errorHandler(E_WARNING, 'The warning', '', 0);
        $this->assertSame(['param_type' => Error::class], $psrLogger->getLogs()[LogLevel::WARNING][0]['context'] ?? []);

    }

}
