<?php

declare(strict_types=1);

namespace Enjoys\ErrorHandler\ExceptionHandler\Output;

use Psr\Http\Message\ResponseInterface;

final class Plain extends OutputError
{

    public function getResponse(): ResponseInterface
    {
        $response = $this->getResponseFactory()->createResponse();
        $response->getBody()->write(
            sprintf(
                "%s%s\n%s",
                empty($this->getError()->code) ? "" : "[{$this->getError()->code}] ",
                $this->getError()->type,
                $this->getError()->message
            )
        );
        return $response;
    }
}
