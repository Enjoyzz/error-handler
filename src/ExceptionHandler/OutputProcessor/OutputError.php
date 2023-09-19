<?php

declare(strict_types=1);


namespace Enjoys\ErrorHandler\ExceptionHandler\OutputProcessor;


use Enjoys\ErrorHandler\Error;
use Enjoys\ErrorHandler\ExceptionHandler\View\ErrorView;
use HttpSoft\Message\Response;
use HttpSoft\Message\ResponseFactory;
use Psr\Http\Message\ResponseFactoryInterface;
use Psr\Http\Message\ResponseInterface;
use ReflectionClass;
use ReflectionException;
use RuntimeException;

/**
 * @psalm-consistent-constructor
 */
abstract class OutputError
{
    private ?ResponseFactoryInterface $responseFactory = null;
    private string $mimeType = 'text/html';
    private int $httpStatusCode = 500;
    private ?Error $error = null;
    private ?ErrorView $view = null;

    abstract public function getResponse(): ResponseInterface;

    /**
     * @psalm-suppress MixedReturnStatement, MixedInferredReturnType
     * @throws ReflectionException
     */
    private function getReasonPhrase(int $statusCode): string
    {
        $phrases = (new ReflectionClass(Response::class))->getProperty('phrases');
        $phrases->setAccessible(true);
        return $phrases->getValue()[$statusCode] ?? 'Unknown error';
    }

    public function setError(Error $error): static
    {
        $this->error = $error;
        return $this;
    }

    public function getError(): Error
    {
        return $this->error ?? throw new RuntimeException('Error not set');
    }

    final public function setResponseFactory(?ResponseFactoryInterface $responseFactory): static
    {
        $this->responseFactory = $responseFactory;
        return $this;
    }

    protected function getResponseFactory(): ResponseFactoryInterface
    {
        return $this->responseFactory ?? new ResponseFactory();
    }

    public function setMimeType(string $mimeType): static
    {
        $this->mimeType = $mimeType;
        return $this;
    }

    protected function getMimeType(): string
    {
        return $this->mimeType;
    }

    public function setHttpStatusCode(int $httpStatusCode): static
    {
        $this->httpStatusCode = $httpStatusCode;
        return $this;
    }

    public function getHttpStatusCode(): int
    {
        return $this->httpStatusCode;
    }

    public function setView(?ErrorView $view = null): static
    {
        $this->view = $view;
        return $this;
    }

    protected function getView(): ?ErrorView
    {
        return $this->view;
    }

    /**
     * @throws ReflectionException
     */
    public function getHttpReasonPhrase(): string
    {
        return $this->getReasonPhrase($this->httpStatusCode);
    }

}
