<?php

declare(strict_types=1);


namespace Enjoys\Oophps\ExceptionHandler\OutputProcessor;


use Psr\Http\Message\ResponseInterface;

final class Svg extends OutputError
{

    public function getResponse(): ResponseInterface
    {
        $code = empty($this->getError()->code) ? "" : "[{$this->getError()->code}]";
        $type = $this->getError()->type;
        $this->response->getBody()->write(
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
        return $this->response;
    }
}
