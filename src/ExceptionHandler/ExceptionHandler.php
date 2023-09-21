<?php

declare(strict_types=1);


namespace Enjoys\ErrorHandler\ExceptionHandler;


use Enjoys\ErrorHandler\Error;
use Enjoys\ErrorHandler\ErrorHandler;
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
use Throwable;

final class ExceptionHandler implements ExceptionHandlerInterface
{
    /**
     * @var array<string|class-string<OutputError>, string[]>
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
     * @var array<class-string<OutputError>, ErrorView|class-string<ErrorView>>
     */
    private array $outputErrorViewMap;

    private ServerRequestInterface $request;

    private EmitterInterface $emitter;

    private ResponseFactoryInterface $responseFactory;

    /**
     * @param array<class-string<OutputError>, class-string<ErrorView>|ErrorView> $outputErrorViewMap
     * @param ServerRequestInterface|null $request
     * @param EmitterInterface|null $emitter
     * @param ResponseFactoryInterface|null $responseFactory
     */
    public function __construct(
        array $outputErrorViewMap = [],
        ?ServerRequestInterface $request = null,
        ?EmitterInterface $emitter = null,
        ?ResponseFactoryInterface $responseFactory = null,
    ) {
        $this->request = $request ?? ServerRequestCreator::createFromGlobals();
        $this->emitter = $emitter ?? new SapiEmitter();
        $this->responseFactory = $responseFactory ?? new ResponseFactory();
        $this->outputErrorViewMap = $outputErrorViewMap;
    }

    /**
     * @throws Throwable
     */
    public function handle(Throwable $error, int $httpStatusCode = ErrorHandler::DEFAULT_HTTP_STATUS_CODE): void
    {
        $response = $this->getErrorOutput($error, $httpStatusCode)
            ->getResponse();

        $this->emitter->emit($response);
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
                        ->setView($this->getView($output));
                }
            }
        }

        return (new Html())
            ->setError(Error::createFromThrowable($error))
            ->setHttpStatusCode($httpStatusCode)
            ->setView($this->getView(Html::class));
    }

    private function getView(string $output): ?ErrorView
    {
        $view = $this->outputErrorViewMap[$output] ?? null;
        return $view ? new $view() : null;
    }

    /**
     * @param class-string<OutputError> $type
     * @param class-string<ErrorView>|ErrorView $template
     * @return ExceptionHandlerInterface
     */
    public function setOutputErrorView(string $type, string|ErrorView $template): ExceptionHandlerInterface
    {
        $this->outputErrorViewMap[$type] = $template;
        return $this;
    }

    /**
     * @param array<class-string<OutputError>, class-string<ErrorView>|ErrorView> $outputErrorViewMap * @return $this
     */
    public function setOutputErrorViewMap(array $outputErrorViewMap): ExceptionHandler
    {
        $this->outputErrorViewMap = $outputErrorViewMap;
        return $this;
    }


}
