<?php

namespace Enjoys\Tests\Oophps;

use HttpSoft\Emitter\EmitterInterface;
use Psr\Http\Message\ResponseInterface;

class Emitter  implements EmitterInterface {
    public function emit(ResponseInterface $response, bool $withoutBody = false): void
    {
        CatchResponse::throw($response);
    }
}
