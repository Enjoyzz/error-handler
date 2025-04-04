<?php

declare(strict_types=1);

namespace Enjoys\ErrorHandler\ExceptionHandler\OutputProcessor;

use Psr\Http\Message\ResponseInterface;

final class Json extends OutputError
{


    public function getResponse(): ResponseInterface
    {

        $body  = $this->getView()?->getContent($this->getError(), $this->response) ?? json_encode(
            [
                'error' => [
                    'type' => $this->getError()->type,
                    'code' => $this->getError()->code,
                    'message' => $this->getError()->message
                ]
            ]
        );

        if ($body === false) {
            throw new \RuntimeException('Unable to encode JSON');
        }

        $this->response->getBody()->write($body);

        return $this->response;
    }
}
