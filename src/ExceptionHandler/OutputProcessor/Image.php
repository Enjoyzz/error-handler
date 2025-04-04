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
        $this->response->getBody()->write(
            $this->getView()?->getContent($this->getError(), $this->response) ?? $this->getDefaultBody()
        );
        return $this->response;
    }

    /**
     * @infection-ignore-all
     */
    private function createImage(): \GdImage|false
    {
        $type = $this->getError()->type;
        $code = empty($this->getError()->code) ? "" : "[{$this->getError()->code}]";
        $message = $this->getError()->message;

        $size = 200;
        $image = imagecreatetruecolor($size, $size);
        if ($image === false) {
            return false;
        }
        $textColor = imagecolorallocate($image, 255, 255, 255);
        imagestring($image, 5, 10, 10, "$type $code", $textColor === false ? 0 : $textColor);

        foreach (str_split($message, max(1, intval($size / 10))) as $line => $text) {
            imagestring($image, 5, 10, ($line * 18) + 28, $text, $textColor === false ? 0 : $textColor);
        }

        return $image;
    }

    private function getDefaultBody(): string
    {
        ob_start();
        $image = $this->createImage();
        if ($image === false) {
            throw new \RuntimeException('Image could not be created');
        }
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
        $result = ob_get_clean();
        return $result === false ? throw new \RuntimeException('Image could not be created') : $result;
    }

}
