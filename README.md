# Event dispatcher

[![Build Status](https://img.shields.io/travis/php-lab/event/master.svg)](https://travis-ci.org/php-lab/event)
[![Scrutinizer Code Quality](https://img.shields.io/scrutinizer/g/php-lab/event.svg)](https://scrutinizer-ci.com/g/php-lab/event/)
[![Total Downloads](https://img.shields.io/packagist/dt/php-lab/event.svg)](https://packagist.org/packages/php-lab/event)
[![License](https://img.shields.io/packagist/l/php-lab/event.svg)](https://packagist.org/packages/php-lab/event)

PhpLab\Event requires PHP 7.

## Usage
```php
use PhpLab\Event\Dispatcher;
use App\Logger;

$this->app = new Dispatcher();

$logger = new Logger();
$this->app->subscribe('payment.error', 'logger', function () use ($logger) {
    $logger->log('error', 'Payment error');
});

$this->app->dispatch('payment.error');
```

## License
PhpLab\Di is licensed under the MIT license.
