<?php

declare(strict_types=1);

namespace Enjoys\ErrorHandler\ExceptionHandler\OutputProcessor;

use Psr\Http\Message\ResponseInterface;

final class Json extends OutputError
{


    public function getResponse(): ResponseInterface
    {

        $response = $this->getResponseFactory()->createResponse($this->getHttpStatusCode());

        $body  = $this->getView()?->getContent($this) ?? json_encode(
            [
                'error' => [
                    'type' => $this->getError()->type,
                    'code' => $this->getError()->code,
                    'message' => $this->getError()->message
                ]
            ]
        );
        $response->getBody()->write($body);

        return $response;
    }
}
