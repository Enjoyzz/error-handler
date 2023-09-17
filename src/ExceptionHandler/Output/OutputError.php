<?php

declare(strict_types=1);


namespace Enjoys\ErrorHandler\ExceptionHandler\Output;


use Enjoys\ErrorHandler\Error;
use Enjoys\ErrorHandler\ExceptionHandler\View\ViewInterface;
use HttpSoft\Message\ResponseFactory;
use Psr\Http\Message\ResponseFactoryInterface;
use RuntimeException;

abstract class OutputError implements ErrorOutputInterface
{
    private ?ResponseFactoryInterface $responseFactory = null;
    private string $mimeType = 'text/html';
    private int $httpStatusCode = 500;
    private ?Error $error = null;
    private ?ViewInterface $view = null;


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

    public function setView(?ViewInterface $view = null): static
    {
        $this->view = $view;
        return $this;
    }

    public function getView(): ?ViewInterface
    {
        return $this->view;
    }

}
