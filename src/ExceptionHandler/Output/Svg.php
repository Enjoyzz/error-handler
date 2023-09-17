<?php

declare(strict_types=1);


namespace Enjoys\ErrorHandler\ExceptionHandler\Output;


use Psr\Http\Message\ResponseInterface;

final class Svg extends OutputError
{

    public function getResponse(): ResponseInterface
    {
        $response = $this->getResponseFactory()->createResponse($this->getHttpStatusCode());
        $code = empty($this->getError()->code) ? "" : "[{$this->getError()->code}]";
        $type = $this->getError()->type;
        $response->getBody()->write(
            <<<SVG
<svg xmlns="http://www.w3.org/2000/svg" width="200">
    <text x="20" y="30" title="$type">
        $code $type
    </text>
    <text x="20" y="60"  title="{$this->getError()->message}">
        {$this->getError()->message}
    </text>
</svg>
SVG
        );
        return $response;
    }
}
