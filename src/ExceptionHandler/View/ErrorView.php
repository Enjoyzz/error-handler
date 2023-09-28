<?php

declare(strict_types=1);


namespace Enjoys\Oophps\ExceptionHandler\View;


use Enjoys\Oophps\Error;
use Enjoys\Oophps\ExceptionHandler\OutputProcessor\OutputError;
use Psr\Http\Message\ResponseInterface;

interface ErrorView
{
    public function getContent(Error $error, ResponseInterface $response): string;
}
