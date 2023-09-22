<?php

declare(strict_types=1);

namespace Enjoys\ErrorHandler;

use ErrorException;
use Psr\Log\LogLevel;
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

    public const DEFAULT_HTTP_STATUS_CODE = 500;

    private int $fatalErrorLevel = self::E_FATAL_ERROR;

    private static bool $registeredShutdownFunction = false;

    /**
     * @var array<array-key, list<string>>
     */
    private array $loggerTypeMap = [500 => [LogLevel::ERROR]];

    /**
     * @var array<int, list<string>>
     */
    private array $httpStatusCodeMap = [];

    /**
     * @var callable(Error):array|null
     */
    private $logContextCallable = null;

    private bool $registered = false;

    public function __construct(
        private ExceptionHandlerInterface $exceptionHandler,
        private ErrorLoggerInterface $logger
    ) {
    }

    public function isRegisteredShutdownFunction(): bool
    {
        return self::$registeredShutdownFunction;
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
        if ($this->registered) {
            return;
        }

        self::displayErrors($displayErrors);

        // Handles throwable, echo output and exit.
        set_exception_handler([$this, 'exceptionHandler']);

        // Handles PHP execution errors such as warnings and notices.
        set_error_handler([$this, 'errorHandler']);

        // Handles fatal error.
        register_shutdown_function([$this, 'shutdownFunction'], self::$registeredShutdownFunction = true);

        $this->registered = true;
    }

    /**
     * Unregisters this error handler by restoring the PHP error and exception handlers.
     */
    public function unregister(): void
    {
        if (!$this->registered) {
            return;
        }

        restore_error_handler();
        restore_exception_handler();

        $this->registered = false;
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

        $this->logger->log(
            error: Error::createFromThrowable($error),
            logLevels: $this->getLogLevels($error),
            logContextCallable: $this->logContextCallable
        );

        $this->exceptionHandler->handle($error, $this->getStatusCode($error));
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
        $this->logger->log(
            error: Error::createFromPhpError($severity, $message, $file, $line),
            logContextCallable: $this->logContextCallable
        );

        if (!(error_reporting() & $severity)) {
            // This error code is not included in error_reporting.
            return true;
        }

        if ($this->isFatalError($severity)) {
            throw new ErrorException(
                message: sprintf('%s: %s', self::ERROR_NAMES[$severity] ?? '', $message),
                code: 0,
                severity: $severity,
                filename: $file,
                line: $line
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

    /**
     * @param array<int, list<string>> $httpStatusCodeMap
     */
    public function setHttpStatusCodeMap(array $httpStatusCodeMap): ErrorHandler
    {
        $this->httpStatusCodeMap = $httpStatusCodeMap;
        return $this;
    }

    /**
     * @param callable(Error):array|null $logContextCallable
     */
    public function setLogContextCallable(?callable $logContextCallable): ErrorHandler
    {
        $this->logContextCallable = $logContextCallable;
        return $this;
    }


    /**
     * @param array<array-key, list<string>> $loggerTypeMap
     */
    public function setLoggerTypeMap(array $loggerTypeMap): ErrorHandler
    {
        $this->loggerTypeMap = $loggerTypeMap;
        return $this;
    }

    /**
     * @return list<string>|false
     */
    private function getLogLevels(Throwable $error): array|false
    {
        if (array_key_exists($error::class, $this->loggerTypeMap)) {
            return $this->loggerTypeMap[$error::class];
        }

        return $this->loggerTypeMap[$this->getStatusCode($error)] ?? false;
    }

    private function getStatusCode(Throwable $error): int
    {
        foreach ($this->httpStatusCodeMap as $statusCode => $stack) {
            if (in_array($error::class, $stack, true) || in_array('\\' . $error::class, $stack, true)) {
                return $statusCode;
            }
        }
        return self::DEFAULT_HTTP_STATUS_CODE;
    }

}
