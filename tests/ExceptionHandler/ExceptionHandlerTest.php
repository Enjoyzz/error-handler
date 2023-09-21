<?php

declare(strict_types=1);

namespace Enjoys\Tests\ErrorHandler\ExceptionHandler;

use Enjoys\ErrorHandler\ErrorLogger\ErrorLogger;
use Enjoys\ErrorHandler\ExceptionHandler\ExceptionHandler;
use Enjoys\Tests\ErrorHandler\CatchResponse;
use Enjoys\Tests\ErrorHandler\Emitter;
use Enjoys\Tests\ErrorHandler\TestLogger;
use Enjoys\Tests\ErrorHandler\TestLoggerInvalidWithName;
use Enjoys\Tests\ErrorHandler\TestLoggerWithName;
use PHPUnit\Framework\TestCase;
use Psr\Log\LogLevel;

class ExceptionHandlerTest // extends TestCase
{

    protected function tearDown(): void
    {
        TestLogger::reset();
        TestLoggerInvalidWithName::reset();
        TestLoggerWithName::reset();
    }

//    public function testSetLoggerTypeMapInConstruct()
//    {
//        $exh = new ExceptionHandler(
//            loggerTypeMap: [
//                500 =>  [LogLevel::CRITICAL]
//            ],
//            logger: new ErrorLogger($psrLogger = new TestLogger()),
//            emitter: new Emitter()
//        );
//
//        $exh->handle(new \Exception());
//        $this->assertSame(500, CatchResponse::getResponse()->getStatusCode());
//        $this->assertCount(1, $psrLogger->getLogs()[LogLevel::CRITICAL] ?? []);
//
//    }


}
