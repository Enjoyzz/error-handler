<?php

declare(strict_types=1);

namespace Enjoys\Tests\ErrorHandler\ExceptionHandler;

use Enjoys\ErrorHandler\ErrorLogger\ErrorLogger;
use Enjoys\ErrorHandler\ExceptionHandler\ExceptionHandler;
use Enjoys\ErrorHandler\ExceptionHandler\OutputProcessor\Html;
use Enjoys\ErrorHandler\ExceptionHandler\View\Html\SimpleHtmlErrorView;
use Enjoys\Tests\ErrorHandler\CatchResponse;
use Enjoys\Tests\ErrorHandler\Emitter;
use Enjoys\Tests\ErrorHandler\TestLogger;
use HttpSoft\Message\ServerRequestFactory;
use PHPUnit\Framework\TestCase;
use Psr\Log\LogLevel;

class ExceptionHandlerTest extends TestCase
{

//    public function testSetHttpStatusCodeMap(): void
//    {
//
//        $exh = new ExceptionHandler(httpStatusCodeMap: $startedHttpStatusMap = [
//            500 => [\RuntimeException::class]
//        ]);
//        $this->assertSame($startedHttpStatusMap, $exh->getHttpStatusCodeMap());
//
//        $httpCodeStatusMap = [
//            401 => [\Exception::class],
//            402 => [\ErrorException::class]
//        ];
//        $exh->setHttpStatusCodeMap($httpCodeStatusMap);
//        $this->assertSame($httpCodeStatusMap, $exh->getHttpStatusCodeMap());
//    }
//
//    public function testSetLoggerTypeMap()
//    {
//        $exh = new ExceptionHandler(loggerTypeMap: $startedLoggerTypeMap = [
//            500 => [LogLevel::ERROR]
//        ]);
//        $this->assertSame($startedLoggerTypeMap, $exh->getLoggerTypeMap());
//
//        $loggerTypeMap = [
//            500 => [LogLevel::CRITICAL]
//        ];
//        $exh->setLoggerTypeMap($loggerTypeMap);
//        $this->assertSame($loggerTypeMap, $exh->getLoggerTypeMap());
//    }

//    public function testSetOutputErrorView()
//    {
//    }
//
//    public function testSetErrorLogger()
//    {
//    }

    public function testHttpStatusCodeMap()
    {
        $exh = new ExceptionHandler(
            httpStatusCodeMap: [
                405 => [\DivisionByZeroError::class]
            ],
            emitter: new Emitter()
        );

        $exh->handle(new \DivisionByZeroError());
        $this->assertSame(405, CatchResponse::getResponse()->getStatusCode());

        $exh->handle(new \ArithmeticError());
        $this->assertSame(500, CatchResponse::getResponse()->getStatusCode());

        $exh->setHttpStatusCodeMap([
            405 => [\DivisionByZeroError::class, \ArithmeticError::class]
        ]);

        $exh->handle(new \ArithmeticError());
        $this->assertSame(405, CatchResponse::getResponse()->getStatusCode());
    }

    public function testLoggerTypeMap()
    {
        $exh = new ExceptionHandler(
            emitter: new Emitter()
        );
        $exh->setErrorLogger(new ErrorLogger($psrLogger = new TestLogger()));
        $exh->setLoggerTypeMap([
            405 => [LogLevel::ERROR],
            \ArithmeticError::class => [LogLevel::CRITICAL]
        ]);

        $exh->handle(new \DivisionByZeroError());

        $response = CatchResponse::getResponse();
        $this->assertSame(500, $response->getStatusCode());


        $exh->setHttpStatusCodeMap([
            405 => [\DivisionByZeroError::class]
        ]);
        $exh->handle(new \DivisionByZeroError());

        $response = CatchResponse::getResponse();
        $this->assertSame(405, $response->getStatusCode());

        $exh->setHttpStatusCodeMap([
            405 => [\DivisionByZeroError::class, \ArithmeticError::class]
        ]);
        $exh->handle(new \ArithmeticError());

        $response = CatchResponse::getResponse();
        $this->assertSame(405, $response->getStatusCode());

        $this->assertCount(1, $psrLogger->getLogs()[LogLevel::ERROR] ?? []);
        $this->assertCount(1, $psrLogger->getLogs()[LogLevel::CRITICAL] ?? []);
    }

    public function testOutputErrorViewMap()
    {
        $exh = new ExceptionHandler(
            outputErrorViewMap: [
                Html::class => new SimpleHtmlErrorView()
            ],
            request: (new ServerRequestFactory())->createServerRequest('get', '/')->withAddedHeader(
                'Accept',
                'application/json'
            ),
            emitter: new Emitter()
        );

        $exh->handle(new \Exception());
        $response = CatchResponse::getResponse();

        $this->assertSame('{"error":{"type":"Exception","code":0,"message":""}}', $response->getBody()->__toString());
    }
}
