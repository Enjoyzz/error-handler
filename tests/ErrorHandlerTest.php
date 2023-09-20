<?php

namespace Enjoys\Tests\ErrorHandler;

use Enjoys\ErrorHandler\ErrorHandler;
use Enjoys\ErrorHandler\ErrorLogger\ErrorLogger;
use Enjoys\ErrorHandler\ErrorLoggerInterface;
use Enjoys\ErrorHandler\ExceptionHandler\ExceptionHandler;
use Enjoys\ErrorHandler\ExceptionHandlerInterface;
use PHPUnit\Framework\TestCase;

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
    }

    public function testExceptionHandler()
    {
        $exceptionHandler = $this->createMock(ExceptionHandlerInterface::class);
        $exceptionHandler->expects($this->once())->method('handle');
        $errorHandler = new ErrorHandler(
            $exceptionHandler,
            $this->createMock(ErrorLoggerInterface::class)
        );
        $errorHandler->exceptionHandler(new \Exception());
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
        $this->expectException(\ErrorException::class);
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
            $this->expectException(\ErrorException::class);
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
            $logger->getLogs()[$logLevel][0] ?? ''
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
        $errorHandler->exceptionHandler(new \RuntimeException());
    }
}
