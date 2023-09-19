<?php

declare(strict_types=1);

namespace Enjoys\ErrorHandler\ExceptionHandler\OutputProcessor;

use Enjoys\ErrorHandler\ExceptionHandler\View\Html\SimpleHtmlErrorViewVeryVerbose;
use Psr\Http\Message\ResponseInterface;

final class Html extends OutputError
{


    public function getResponse(): ResponseInterface
    {
        $response = $this->getResponseFactory()->createResponse($this->getHttpStatusCode());
        $response->getBody()->write(
            $this->getView()?->getContent($this) ?? (new SimpleHtmlErrorViewVeryVerbose())->getContent($this)
        );
        return $response;
    }


}
