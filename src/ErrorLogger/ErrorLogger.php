<?php

declare(strict_types=1);


namespace Enjoys\ErrorHandler\ErrorLogger;


use Enjoys\ErrorHandler\Error;
use Enjoys\ErrorHandler\ErrorHandler;
use Enjoys\ErrorHandler\ErrorLoggerInterface;
use Psr\Log\LoggerInterface;
use Psr\Log\LogLevel;
use ReflectionClass;
use RuntimeException;

final class ErrorLogger implements ErrorLoggerInterface
{


    /**
     * @var string|list<string>
     */
    private string|array $defaultLogLevel = LogLevel::NOTICE;

    /**
     * @var array<int, string|list<string>>
     */
    private array $logLevelMap = [
        E_USER_DEPRECATED => LogLevel::WARNING,
        E_DEPRECATED => LogLevel::WARNING,
        E_NOTICE => LogLevel::NOTICE,
        E_USER_NOTICE => LogLevel::NOTICE,
        E_WARNING => LogLevel::WARNING,
        E_USER_WARNING => LogLevel::WARNING,
        E_ERROR => LogLevel::ERROR,
        E_USER_ERROR => LogLevel::ERROR,
        E_PARSE => LogLevel::CRITICAL,
        E_COMPILE_ERROR => LogLevel::CRITICAL,
        E_CORE_ERROR => LogLevel::CRITICAL,
        E_COMPILE_WARNING => LogLevel::ALERT,
        E_CORE_WARNING => LogLevel::ALERT,
    ];

    /**
     * @var array<int, string>
     */
    private array $loggerNameMap = [];

    private string $defaultFormatMessage = '%1$s: %2$s in %3$s on line %4$s';

    /**
     * @var array<int, string>
     */
    private array $loggerFormatMessageMap = [];

    public function __construct(private LoggerInterface $logger)
    {
    }


    /**
     * @inheritdoc
     * @psalm-suppress MixedAssignment
     */
    public function log(Error $error, array|false $logLevels = null, ?callable $logContextCallable = null): void
    {
        if ($logLevels === false) {
            return;
        }

        if ($logLevels === null) {
            $logLevels = (array)($this->logLevelMap[$error->errorLevel] ?? $this->defaultLogLevel);
        }
        $logger = $this->logger;
        if (array_key_exists($error->errorLevel, $this->loggerNameMap)) {
            if (method_exists($logger, 'withName')) {
                $logger = $logger->withName($this->loggerNameMap[$error->errorLevel]);
                if (!$logger instanceof LoggerInterface) {
                    throw new \RuntimeException(
                        sprintf(
                            'The method `withName` must be return of type %s, %s given',
                            LoggerInterface::class,
                            get_debug_type($logger)
                        )
                    );
                }
            }
        }

        foreach ($logLevels as $logLevel) {
            $logger->log(
                $logLevel,
                sprintf(
                    $this->loggerFormatMessageMap[$error->errorLevel] ?? $this->defaultFormatMessage,
                    ErrorHandler::ERROR_NAMES[$error->errorLevel] ?? '',
                    $error->message,
                    $error->file,
                    $error->line,
                    $error->code,
                    $error->traceString
                ),
                $logContextCallable ? $logContextCallable($error) : []
            );
        }
    }

    /**
     * @param string|list<string> $levels
     */
    private function validateLogLevel(string|array $levels): void
    {
        /** @var string[] $logLevels */
        $logLevels = array_keys((new ReflectionClass(LogLevel::class))->getConstants());
        $errors = [];
        foreach ((array)$levels as $level) {
            if (!in_array(strtoupper($level), $logLevels, true)) {
                $errors[] = $level;
            }
        }

        if ($errors !== []) {
            throw new RuntimeException(
                sprintf(
                    '%s - not allowed, allowed only (%s)',
                    implode(', ', $errors),
                    implode(
                        ', ',
                        $logLevels
                    )
                )
            );
        }
    }

    public function setDefaultLogLevel(string $defaultLogLevel): ErrorLogger
    {
        $this->validateLogLevel($defaultLogLevel);
        $this->defaultLogLevel = $defaultLogLevel;
        return $this;
    }

    /**
     * @param int|int[] $errorLevels
     * @param string|list<string> $logLevel
     * @return $this
     */
    public function setLogLevel(int|array $errorLevels, string|array $logLevel): ErrorLogger
    {
        $this->validateLogLevel($logLevel);
        foreach ((array)$errorLevels as $errorLevel) {
            $this->logLevelMap[$errorLevel] = $logLevel;
        }
        return $this;
    }

    /**
     * @param int|int[] $errorLevels
     * @param string $name
     * @return $this
     */
    public function setLoggerName(int|array $errorLevels, string $name): ErrorLogger
    {
        foreach ((array)$errorLevels as $errorLevel) {
            $this->loggerNameMap[$errorLevel] = $name;
        }
        return $this;
    }

    /**
     * @param int|int[] $errorLevels
     * @param string $pattern
     * @return $this
     */
    public function setLoggerFormatMessage(int|array $errorLevels, string $pattern): ErrorLogger
    {
        foreach ((array)$errorLevels as $errorLevel) {
            $this->loggerFormatMessageMap[$errorLevel] = $pattern;
        }
        return $this;
    }

    public function getPsrLogger(): LoggerInterface
    {
        return $this->logger;
    }
}
