<?php

declare(strict_types=1);


namespace Enjoys\ErrorHandler\ExceptionHandler\OutputProcessor;


use Psr\Http\Message\ResponseInterface;

use function imagecolorallocate;
use function imagecreatetruecolor;
use function imagegif;
use function imagejpeg;
use function imagepng;
use function imagestring;
use function imagewebp;

final class Image extends OutputError
{

    public function getResponse(): ResponseInterface
    {
        $response = $this->getResponseFactory()->createResponse($this->getHttpStatusCode());
        ob_start();
        $image = $this->createImage();
        switch ($this->getMimeType()) {
            case 'image/gif':
                imagegif($image);
                break;
            case 'image/jpeg':
                imagejpeg($image);
                break;
            case 'image/png':
                imagepng($image);
                break;
            case 'image/webp':
                imagewebp($image);
                break;
        }

        $response->getBody()->write((string)ob_get_clean());

        return $response;
    }

    private function createImage(): \GdImage
    {
        $type = $this->getError()->type;
        $code = empty($this->getError()->code) ? "" : "[{$this->getError()->code}]";
        $message = $this->getError()->message;

        $size = 200;
        $image = imagecreatetruecolor($size, $size);
        $textColor = imagecolorallocate($image, 255, 255, 255);
        imagestring($image, 5, 10, 10, "$type $code", $textColor);

        foreach (str_split($message, max(1, intval($size / 10))) as $line => $text) {
            imagestring($image, 5, 10, ($line * 18) + 28, $text, $textColor);
        }

        return $image;
    }

}
