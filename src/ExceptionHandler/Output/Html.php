<?php

declare(strict_types=1);

namespace Enjoys\ErrorHandler\ExceptionHandler\Output;

use Enjoys\ErrorHandler\ExceptionHandler\View\SimpleHtmlViewVeryVerbose;
use Enjoys\ErrorHandler\ExceptionHandler\View\ViewInterface;
use Psr\Http\Message\ResponseInterface;

final class Html extends OutputError
{


    public function getResponse(): ResponseInterface
    {
        $response = $this->getResponseFactory()->createResponse($this->getHttpStatusCode());
        $template = $this->getView() ?? new SimpleHtmlViewVeryVerbose();


        $response->getBody()->write(
            $template->getContent($this->getError(), $this->getHttpStatusCode())
        );
        return $response;
    }



}
