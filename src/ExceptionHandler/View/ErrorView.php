<?php

declare(strict_types=1);


namespace Enjoys\ErrorHandler\ExceptionHandler\View;


use Enjoys\ErrorHandler\ExceptionHandler\OutputProcessor\OutputError;

interface ErrorView
{
    public function getContent(OutputError $processor): string;
}
