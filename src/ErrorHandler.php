<?php

declare(strict_types=1);

namespace Enjoys\ErrorHandler;

use ErrorException;
use Throwable;

final class ErrorHandler
{

    public const ERROR_NAMES = [
        0 => 'Exception',
        E_ERROR => 'PHP Fatal Error',
        E_WARNING => 'PHP Warning',
        E_PARSE => 'PHP Parse Error',
        E_NOTICE => 'PHP Notice',
        E_CORE_ERROR => 'PHP Core Error',
        E_CORE_WARNING => 'PHP Core Warning',
        E_COMPILE_ERROR => 'PHP Compile Error',
        E_COMPILE_WARNING => 'PHP Compile Warning',
        E_USER_ERROR => 'PHP User Error',
        E_USER_WARNING => 'PHP User Warning',
        E_USER_NOTICE => 'PHP User Notice',
        E_STRICT => 'PHP Strict Warning',
        E_RECOVERABLE_ERROR => 'PHP Recoverable Error',
        E_DEPRECATED => 'PHP Deprecated',
        E_USER_DEPRECATED => 'PHP User Deprecated',
    ];


    private const E_FATAL_ERROR
        = E_ERROR
        | E_PARSE
        | E_CORE_ERROR
        | E_CORE_WARNING
        | E_COMPILE_ERROR
        | E_COMPILE_WARNING
        | E_USER_ERROR;

    private int $fatalErrorLevel = self::E_FATAL_ERROR;

    public function __construct(
        private ExceptionHandlerInterface $exceptionHandler,
        private ErrorLoggerInterface $logger
    ) {
        $this->exceptionHandler->setErrorLogger($logger);
    }


    public function getErrorLogger(): ErrorLoggerInterface
    {
        return $this->logger;
    }

    /**
     * Register this error handler.
     */
    public function register(bool $displayErrors = false): void
    {
        self::displayErrors($displayErrors);

        // Handles throwable, echo output and exit.
        set_exception_handler([$this, 'exceptionHandler']);

        // Handles PHP execution errors such as warnings and notices.
        set_error_handler([$this, 'errorHandler']);

        // Handles fatal error.
        register_shutdown_function([$this, 'shutdownFunction']);
    }

    /**
     * Unregisters this error handler by restoring the PHP error and exception handlers.
     */
    public function unregister(): void
    {
        restore_error_handler();
        restore_exception_handler();
    }

    /**
     * @param Throwable $error
     * @return void
     * @internal
     */
    public function exceptionHandler(Throwable $error): void
    {
        // disable error capturing to avoid recursive errors while handling exceptions
        $this->unregister();
        $this->exceptionHandler->handle($error);
    }

    /**
     * @param int $severity
     * @param string $message
     * @param string $file
     * @param int $line
     * @return bool
     * @throws ErrorException
     * @internal
     */
    public function errorHandler(int $severity, string $message, string $file, int $line): bool
    {
        // Logging all php errors
        $this->logger->log(Error::createFromPhpError($severity, $message, $file, $line));

        if (!(error_reporting() & $severity)) {
            // This error code is not included in error_reporting.
            return true;
        }

        if ($this->isFatalError($severity)) {
            throw new ErrorException(
                sprintf('%s: %s', self::ERROR_NAMES[$severity] ?? '', $message),
                0,
                $severity,
                $file,
                $line
            );
        }
        return true;
    }

    /**
     * @return void
     * @throws ErrorException
     * @internal
     */
    public function shutdownFunction(): void
    {
        $e = error_get_last();
//dd($e);
        if ($e !== null && $this->isFatalError($e['type'])) {
            throw new ErrorException(
                message: sprintf('%s: %s', self::ERROR_NAMES[$e['type']] ?? '', $e['message']),
                code: 0,
                severity: $e['type'],
                filename: $e['file'],
                line: $e['line']
            );
        }
    }

    public function getFatalErrorLevel(): int
    {
        return $this->fatalErrorLevel;
    }

    private function isFatalError(int $severity): bool
    {
        return !!($this->getFatalErrorLevel() & $severity);
    }


    public function addFatalError(int $errorLevel): ErrorHandler
    {
        $this->fatalErrorLevel = self::E_FATAL_ERROR | $errorLevel;
        return $this;
    }

    public function resetFatalErrorLevel(): ErrorHandler
    {
        $this->fatalErrorLevel = self::E_FATAL_ERROR;
        return $this;
    }

    public static function displayErrors(bool $value): void
    {
        ini_set('display_errors', $value ? '1' : '0');
    }




}
