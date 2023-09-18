<?php

declare(strict_types=1);


namespace Enjoys\Tests\ErrorHandler;


use Psr\Http\Message\ResponseInterface;

final class CatchResponse
{
    private static ?ResponseInterface $response = null;

    public static function throw(ResponseInterface $response): void
    {
        self::$response = $response;
    }

    public static function getResponse(): ?ResponseInterface
    {
        try {
            return self::$response;
        } finally {
            self::$response = null;
        }
    }
}
