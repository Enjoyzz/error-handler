# error-handler

Быстрый запуск

```php
$exceptionHandler = new \Enjoys\ErrorHandler\ExceptionHandler\ExceptionHandler();
$errorLogger = new \Enjoys\ErrorHandler\ErrorLogger\ErrorLogger(\Psr\Log\LoggerInterface $psr3logger);
$errorHandler = new \Enjoys\ErrorHandler\ErrorHandler($exceptionHandler, $errorLogger);
$errorHandler->register();
```

Настройки и методы ErrorHandler

```php
/** @var Enjoys\ErrorHandler\ErrorHandler $errorHandler */

//
$errorHandler->register();

//
$errorHandler->unregister();

// Заменяет всю карту сопоставлений httpStatusCodeMap
$errorHandler->setHttpStatusCodeMap([
    403 => [
        UnAuthorizedException::class
    ],
    404 => [
        ResourceNotFoundException::class,
        NotFoundException::class,
        PageNotFoundException::class
    ]
]);

// Заменяет всю карту сопоставлений loggerTypeMap
$errorHandler->setLoggerTypeMap([
    500 => [
        \Psr\Log\LogLevel::ERROR
    ],
    CriticatlException::class => [
        \Psr\Log\LogLevel::CRITICAL,
        \Psr\Log\LogLevel::EMERGENCY
    ]
]);

//
$errorHandler->addFatalError(E_WARNING | E_USER_WARNING)

//
$errorHandler->resetFatalErrorLevel()
```

Настройки и методы ExceptionHandler

```php
/** @var Enjoys\ErrorHandler\ExceptionHandler\ExceptionHandler $exceptionHandler */

use Enjoys\ErrorHandler\ExceptionHandler\OutputProcessor\Html;
use Enjoys\ErrorHandler\ExceptionHandler\OutputProcessor\Json;
use Enjoys\ErrorHandler\ExceptionHandler\OutputProcessor\Xml;
use Enjoys\ErrorHandler\ExceptionHandler\View\Html\SimpleHtmlErrorViewVeryVerbose;

// Заменяет всю карту сопоставлений outputErrorViewMap
$exceptionHandler->setOutputErrorViewMap([
    Html::class => SimpleHtmlErrorViewVeryVerbose::class,
    Json::class => ImplementationOfErrorViewInterface::class
]);

// Добавляет или заменяет соответсвующее сопоставление в outputErrorViewMap
$exceptionHandler->setOutputErrorView(Xml::class, ImplementationOfErrorViewInterface::class);
```
Настройки и методы ErrorLogger
```php

/** @var \Enjoys\ErrorHandler\ErrorLogger\ErrorLogger $logger */

// Устанавливает дефолтный уровень лога, используется если не определено иное (по-умолчанию LogLevel::NOTICE)
$logger->setDefaultLogLevel(\Psr\Log\LogLevel::INFO);

// Переопределяет уровень лога для конкретного типа ошибок
$logger->setLogLevel([E_USER_WARNING, E_WARNING], \Psr\Log\LogLevel::NOTICE);


// Переопределяет формат сообщений в логах
$logger->setLoggerFormatMessage([E_DEPRECATED, E_USER_DEPRECATED], 'Deprecated message: %2$s in %3$s on line %4$s');

// Переопределяет названия каналов, если поддерживается, для конкретного типа ошибок
$logger->setLoggerName([E_DEPRECATED, E_USER_DEPRECATED], 'Deprecation');

// Возвращает \Psr\Log\LoggerInterface
$logger->getPsrLogger();
```
