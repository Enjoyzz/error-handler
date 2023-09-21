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

    public function __construct(protected ResponseInterface $response)
    {
    }

    abstract public function getResponse(): ResponseInterface;

    public function setError(Error $error): static
    {
        $this->error = $error;
        return $this;
    }

    protected function getError(): Error
    {
        return $this->error ?? throw new RuntimeException('Error not set');
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

    public function setView(?ErrorView $view = null): static
    {
        $this->view = $view;
        return $this;
    }

    protected function getView(): ?ErrorView
    {
        return $this->view;
    }


}
