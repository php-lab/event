<?php
declare(strict_types=1);

/**
 * @author    Yuriy Davletshin <yuriy.davletshin@gmail.com>
 * @copyright 2015 Yuriy Davletshin
 * @license   MIT
 */
namespace PhpLab\Event;

use PhpLab\Event\Fake\{Logger, Counter};

class DispatcherTest extends \PHPUnit_Framework_TestCase
{
    protected $app;

    public function setUp()
    {
        $this->app = new Dispatcher();
    }

    public function testShouldNotifyListener()
    {
        $logger = new Logger();
        $this->app->subscribe('payment.failure', 'logger', function () use ($logger) {
            $logger->log('Payment failure');
        });
        $this->app->dispatch('payment.failure');
        $log = $logger->export();
        $this->assertEquals('Payment failure', $log[0]);
    }

    public function testShouldIgnoreOtherEvent()
    {
        $logger = new Logger();
        $this->app->subscribe('payment.failure', 'logger', function () use ($logger) {
            $logger->log('Payment failure');
        });
        $this->app->dispatch('app.after_response');
        $log = $logger->export();
        $this->assertTrue(empty($log));
    }

    public function testShouldNotifyListenerAboutDifferentEvents()
    {
        $logger = new Logger();
        $this->app->subscribe('payment.failure', 'logger', function () use ($logger) {
            $logger->log('Payment failure');
        });
        $this->app->subscribe('app.critical_error', 'logger', function () use ($logger) {
            $logger->log('Critical error');
        });
        $this->app->dispatch('payment.failure');
        $this->app->dispatch('app.critical_error');
        $log = $logger->export();
        $this->assertEquals(['Payment failure', 'Critical error'], $log);
    }

    public function testShouldDispatchEventForDifferentListeners()
    {
        $logger = new Logger();
        $this->app->subscribe('payment.failure', 'logger', function () use ($logger) {
            $logger->log('Payment failure');
        });
        $counter = new Counter();
        $this->app->subscribe('payment.failure', 'errorCounter', function () use ($counter) {
            $counter->increment();
        });
        $this->app->dispatch('payment.failure');
        $log = $logger->export();
        $this->assertEquals('Payment failure', $log[0]);
        $this->assertEquals(1, $counter->total());
    }

    public function testShouldDispatchEventFromOtherSubscription()
    {
        $logger = new Logger();
        $this->app->subscribe(
            'payment.failure',
            'logger',
            function (DispatcherInterface $dispatcher) use ($logger) {
                $logger->log('Payment failure');
                $dispatcher->dispatch('payment.attempt');
            }
        );
        $counter = new Counter();
        $this->app->subscribe('payment.attempt', 'paymentCounter', function () use ($counter) {
            $counter->increment();
        });
        $this->app->dispatch('payment.failure');
        $this->assertEquals(1, $counter->total());
    }

    public function testShouldRemoveSubscription()
    {
        $counter = new Counter();
        $this->app->subscribe('payment.failure', 'errorCounter', function () use ($counter) {
            $counter->increment();
        });
        $this->app->unsubscribe('payment.failure', 'errorCounter');
        $this->app->dispatch('payment.failure');
        $this->assertEquals(0, $counter->total());
    }

    public function testShouldStopPropagation()
    {
        $logger = new Logger();
        $this->app->subscribe('payment.failure', 'logger', function () use ($logger) {
            $logger->log('Payment failure');

            return ['stop' => true];
        });
        $counter = new Counter();
        $this->app->subscribe('payment.failure', 'errorCounter', function () use ($counter) {
            $counter->increment();
        });
        $this->app->dispatch('payment.failure');
        $this->assertEquals(0, $counter->total());
    }

    public function testShouldNotifyListenersByPriorities()
    {
        $logger = new Logger();
        $this->app->subscribe('payment.failure', 'logger', function () use ($logger) {
            $logger->log('Payment failure');

            return ['stop' => true];
        }, 10);
        $counter = new Counter();
        $this->app->subscribe('payment.failure', 'errorCounter', function () use ($counter) {
            $counter->increment();
        }, 20);
        $this->app->dispatch('payment.failure');
        $this->assertEquals(1, $counter->total());
    }

    public function testShouldThrowExceptionIfRedefineSubscription()
    {
        $this->setExpectedException('\PhpLab\Event\AlreadyExistsException');
        $logger = new Logger();
        $this->app->subscribe('payment.failure', 'logger', function () use ($logger) {
            $logger->log('Payment failure');
        });
        $this->app->subscribe('payment.failure', 'logger', function () use ($logger) {
            $logger->log('Payment failure again');
        });
    }
}
