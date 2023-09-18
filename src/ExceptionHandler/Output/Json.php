<?php

declare(strict_types=1);

namespace Enjoys\ErrorHandler\ExceptionHandler\Output;

use Psr\Http\Message\ResponseInterface;

final class Json extends OutputError
{


    public function getResponse(): ResponseInterface
    {
        $response = $this->getResponseFactory()->createResponse($this->getHttpStatusCode());
        $response->getBody()->write(
            json_encode(
                [
                    'error' => [
                        'type' => $this->getError()->type,
                        'code' => $this->getError()->code,
                        'message' => $this->getError()->message
                    ]
                ]
            )
        );
        return $response;
    }
}
