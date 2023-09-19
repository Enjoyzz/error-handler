<?php

declare(strict_types=1);


namespace Enjoys\ErrorHandler\ExceptionHandler;


use Enjoys\ErrorHandler\Error;
use Enjoys\ErrorHandler\ErrorLoggerInterface;
use Enjoys\ErrorHandler\ExceptionHandler\OutputProcessor\Html;
use Enjoys\ErrorHandler\ExceptionHandler\OutputProcessor\Image;
use Enjoys\ErrorHandler\ExceptionHandler\OutputProcessor\Json;
use Enjoys\ErrorHandler\ExceptionHandler\OutputProcessor\OutputError;
use Enjoys\ErrorHandler\ExceptionHandler\OutputProcessor\Plain;
use Enjoys\ErrorHandler\ExceptionHandler\OutputProcessor\Svg;
use Enjoys\ErrorHandler\ExceptionHandler\OutputProcessor\Xml;
use Enjoys\ErrorHandler\ExceptionHandler\View\ErrorView;
use Enjoys\ErrorHandler\ExceptionHandlerInterface;
use HttpSoft\Emitter\EmitterInterface;
use HttpSoft\Emitter\SapiEmitter;
use HttpSoft\Message\ResponseFactory;
use HttpSoft\ServerRequest\ServerRequestCreator;
use Psr\Http\Message\ResponseFactoryInterface;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Log\LogLevel;
use Throwable;

final class ExceptionHandler implements ExceptionHandlerInterface
{
    /**
     * @var array<class-string<ErrorOutputProcessor>, string[]>
     */
    private array $outputErrorMimeMap = [
        Json::class => ['application/json', 'text/json'],
        Html::class => ['text/html'],
        Xml::class => ['text/xml', 'application/xml'],
        Plain::class => ['text/plain', 'text/css', 'text/javascript'],
        Svg::class => ['image/svg+xml'],
        Image::class => ['image/gif', 'image/jpeg', 'image/png', 'image/webp']
    ];

    /**
     * @var array<class-string<ErrorOutputProcessor>, ErrorView>
     */
    private array $outputErrorViewMap;

    /**
     * @var array<int, list<string>>
     */
    private array $httpStatusCodeMap;

    /**
     * @var array<array-key, list<string>>
     */
    private array $loggerTypeMap;

    private ?ErrorLoggerInterface $logger;

    private ServerRequestInterface $request;

    private EmitterInterface $emitter;

    private ResponseFactoryInterface $responseFactory;

    /**
     * @param array<int, list<string>> $httpStatusCodeMap
     * @param array<class-string<ErrorOutputProcessor>, ErrorView> $outputErrorViewMap
     * @param array<array-key, list<string>> $loggerTypeMap
     * @param ServerRequestInterface|null $request
     * @param EmitterInterface|null $emitter
     * @param ResponseFactoryInterface|null $responseFactory
     */
    public function __construct(
        array $httpStatusCodeMap = [],
        array $outputErrorViewMap = [],
        array $loggerTypeMap = [500 => [LogLevel::ERROR]],
        ?ErrorLoggerInterface $logger = null,
        ?ServerRequestInterface $request = null,
        ?EmitterInterface $emitter = null,
        ?ResponseFactoryInterface $responseFactory = null,
    ) {
        $this->logger = $logger;
        $this->request = $request ?? ServerRequestCreator::createFromGlobals();
        $this->emitter = $emitter ?? new SapiEmitter();
        $this->responseFactory = $responseFactory ?? new ResponseFactory();
        $this->httpStatusCodeMap = $httpStatusCodeMap;
        $this->outputErrorViewMap = $outputErrorViewMap;
        $this->loggerTypeMap = $loggerTypeMap;
    }

    /**
     * @throws Throwable
     */
    public function handle(Throwable $error): void
    {
        $httpStatusCode = $this->getStatusCode($error);

        $this->logger?->log(
            Error::createFromThrowable($error),
            $this->getLogLevels($error, $httpStatusCode)
        );

        $response = $this->getErrorOutput($error, $httpStatusCode)
            ->getResponse();

        $this->emitter->emit($response);
    }


    /**
     * @param array<int, list<string>> $httpStatusCodeMap
     */
    public function setHttpStatusCodeMap(array $httpStatusCodeMap): ExceptionHandler
    {
        $this->httpStatusCodeMap = $httpStatusCodeMap;
        return $this;
    }


    private function getStatusCode(Throwable $error): int
    {
        foreach ($this->httpStatusCodeMap as $statusCode => $stack) {
            if (in_array($error::class, $stack) || in_array('\\' . $error::class, $stack)) {
                return $statusCode;
            }
        }
        return ExceptionHandlerInterface::DEFAULT_STATUS_CODE;
    }


    private function getErrorOutput(Throwable $error, int $httpStatusCode): OutputError
    {
        /** @var class-string<OutputError> $output */
        foreach ($this->outputErrorMimeMap as $output => $mimes) {
            foreach ($mimes as $mime) {
                if (stripos($this->request->getHeaderLine('Accept'), $mime) !== false) {
                    return (new $output())
                        ->setResponseFactory($this->responseFactory)
                        ->setHttpStatusCode($httpStatusCode)
                        ->setMimeType($mime)
                        ->setError(Error::createFromThrowable($error))
                        ->setView($this->outputErrorViewMap[$output] ?? null);
                }
            }
        }

        return (new Html())
            ->setError(Error::createFromThrowable($error))
            ->setHttpStatusCode($httpStatusCode)
            ->setView($this->outputErrorViewMap[Html::class] ?? null);
    }

    /**
     * @param array<array-key, list<string>> $loggerTypeMap
     */
    public function setLoggerTypeMap(array $loggerTypeMap): ExceptionHandler
    {
        $this->loggerTypeMap = $loggerTypeMap;
        return $this;
    }


    private function getLogLevels(Throwable $error, int $httpStatusCode): array|false
    {
        if (array_key_exists($error::class, $this->loggerTypeMap)) {
            return $this->loggerTypeMap[$error::class];
        }

        return $this->loggerTypeMap[$httpStatusCode] ?? false;
    }

    public function setErrorLogger(?ErrorLoggerInterface $logger): ExceptionHandlerInterface
    {
        $this->logger = $logger;
        return $this;
    }

    /**
     * @param class-string<OutputError> $type
     * @param ErrorView $template
     * @return ExceptionHandlerInterface
     */
    public function setOutputErrorView(string $type, ErrorView $template): ExceptionHandlerInterface
    {
        $this->outputErrorViewMap[$type] = $template;
        return $this;
    }

    public function setOutputErrorViewMap(array $outputErrorViewMap): ExceptionHandler
    {
        $this->outputErrorViewMap = $outputErrorViewMap;
        return $this;
    }


}
