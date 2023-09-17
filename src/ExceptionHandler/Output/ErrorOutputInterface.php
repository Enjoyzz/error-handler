<?php

declare(strict_types=1);


namespace Enjoys\ErrorHandler\ExceptionHandler\Output;


use Enjoys\ErrorHandler\Error;
use Enjoys\ErrorHandler\ExceptionHandler\View\ViewInterface;
use Psr\Http\Message\ResponseFactoryInterface;
use Psr\Http\Message\ResponseInterface;

interface ErrorOutputInterface
{
    public function getResponse(): ResponseInterface;

    public function setResponseFactory(?ResponseFactoryInterface $responseFactory): static;

    public function setMimeType(string $mimeType): static;

    public function setHttpStatusCode(int $httpStatusCode): static;

    public function setError(Error $error): static;

    public function setView(?ViewInterface $view = null);
}
