<?php

namespace Enjoys\Tests\ErrorHandler;

use Enjoys\ErrorHandler\ErrorHandler;
use Enjoys\ErrorHandler\ErrorLoggerInterface;
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
    }

    public function cl1 (\Exception $e) {
        echo '[' . __FUNCTION__ . '] ' . $e->getMessage();
    }

    public function cl2 (\Exception $e) {
        echo '[' . __FUNCTION__ . '] ' . $e->getMessage();
    }

    public function testRegister()
    {

        $errorHandler = new ErrorHandler(
            $this->createMock(ExceptionHandlerInterface::class),
            $this->createMock(ErrorLoggerInterface::class)
        );
        $errorHandler->register();

        $this->assertSame([$errorHandler, 'exceptionHandler'], set_exception_handler(null));
        $this->assertSame([$errorHandler, 'errorHandler'], set_error_handler(null));

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
        $this->expectException(\ErrorException::class);
        $errorHandler = new ErrorHandler(
            $this->createMock(ExceptionHandlerInterface::class),
            $this->createMock(ErrorLoggerInterface::class)
        );
        $errorHandler->addFatalError(E_WARNING);
        @fopen('/'.uniqid('invalid_path'), 'r');
        $errorHandler->shutdownFunction();
    }


    public function dataForTestErrorHandler()
    {
        return [
            [true, E_USER_ERROR],
            [false, E_USER_WARNING],
            [true, E_USER_WARNING, E_USER_WARNING],
            [false, E_WARNING, E_USER_WARNING],
            [true, E_DEPRECATED, E_DEPRECATED | E_USER_DEPRECATED],
            [true, E_USER_DEPRECATED, E_DEPRECATED | E_USER_DEPRECATED],
            [false, E_USER_DEPRECATED],
            [true, E_ERROR],
            [true, E_USER_NOTICE, E_ALL],
            [true, E_RECOVERABLE_ERROR, E_ALL],
            [false, E_RECOVERABLE_ERROR],
            [false, E_USER_WARNING, E_USER_WARNING, E_ALL & ~E_USER_WARNING],
        ];
    }


    /**
     * @dataProvider dataForTestErrorHandler
     */
    public function testErrorHandler($expectException, $severity, $addedFatalErrorLevels = 0, $errorReporting = E_ALL)
    {
        error_reporting($errorReporting);

        if ($expectException) {
            $this->expectException(\ErrorException::class);
        }
        $errorHandler = new ErrorHandler(
            $this->createMock(ExceptionHandlerInterface::class),
            $this->createMock(ErrorLoggerInterface::class)
        );
        $errorHandler->addFatalError($addedFatalErrorLevels);
        $this->assertTrue($errorHandler->errorHandler($severity, 'The Error Message', 'test.php', 42));
    }

}
