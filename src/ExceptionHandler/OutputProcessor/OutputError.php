<?php

declare(strict_types=1);


namespace Enjoys\ErrorHandler\ExceptionHandler\OutputProcessor;


use Enjoys\ErrorHandler\Error;
use Enjoys\ErrorHandler\ExceptionHandler\View\ErrorView;
use HttpSoft\Message\Response;
use HttpSoft\Message\ResponseFactory;
use Psr\Http\Message\ResponseFactoryInterface;
use Psr\Http\Message\ResponseInterface;
use RuntimeException;

abstract class OutputError
{
    private ?ResponseFactoryInterface $responseFactory = null;
    private string $mimeType = 'text/html';
    private int $httpStatusCode = 500;
    private ?Error $error = null;
    private ?ErrorView $view = null;

    abstract public function getResponse(): ResponseInterface;

    protected function getReasonPhrase(int $statusCode): string
    {
        $reflection = new \ReflectionClass(Response::class);
        $phrases = $reflection->getProperty('phrases');
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

    public function getResponseFactory(): ResponseFactoryInterface
    {
        return $this->responseFactory ?? new ResponseFactory();
    }

    public function setMimeType(string $mimeType): static
    {
        $this->mimeType = $mimeType;
        return $this;
    }

    public function getMimeType(): string
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

    public function getView(): ?ErrorView
    {
        return $this->view;
    }

    public function getHttpReasonPhrase(): string
    {
        return $this->getReasonPhrase($this->httpStatusCode);
    }

}
