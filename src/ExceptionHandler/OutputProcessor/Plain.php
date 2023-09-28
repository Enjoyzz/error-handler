<?php

declare(strict_types=1);

namespace Enjoys\Oophps\ExceptionHandler\OutputProcessor;

use Enjoys\Oophps\ExceptionHandler\ExceptionHandler;
use Enjoys\Oophps\ExceptionHandler\View\ErrorView;
use Enjoys\Tests\Oophps\CatchResponse;
use Enjoys\Tests\Oophps\Emitter;
use HttpSoft\Message\ServerRequestFactory;
use Psr\Http\Message\ResponseInterface;

final class Plain extends OutputError
{

    public function getResponse(): ResponseInterface
    {
        $body = $this->getView()?->getContent($this->getError(), $this->response) ?? sprintf(
            "%s%s\n%s",
            empty($this->getError()->code) ? "" : "[{$this->getError()->code}] ",
            $this->getError()->type,
            $this->getError()->message
        );

        $this->response->getBody()->write($body);
        return $this->response;
    }

}
