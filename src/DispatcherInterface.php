<?php
declare(strict_types=1);

/**
 * @author    Yuriy Davletshin <yuriy.davletshin@gmail.com>
 * @copyright 2015 Yuriy Davletshin
 * @license   MIT
 */
namespace PhpLab\Event;

/**
 * Interface of an event dispatcher.
 */
interface DispatcherInterface
{
    /**
     * @var int Max number of subscriptions.
     */
    const SUBSCRIPTION_LIMIT = 1000000;

    /**
     * @var string Delimiter for a subscription key.
     */
    const KEY_DELIMITER = '///';

    /**
     * @var string Key for stopping event propagation.
     */
    const STOP_KEY = 'stop';

    /**
     * Adds a subscription.
     *
     * @param string   $event    The unique ID of the event.
     * @param string   $listener The unique ID of the listener.
     * @param callable $action   The closure or invokable object.
     * @param int      $priority The priority for the subscription.
     */
    public function subscribe(string $event, string $listener, callable $action, int $priority = 0);

    /**
     * Removes a subscription.
     *
     * @param string $event    The unique ID of the event.
     * @param string $listener The unique ID of the listener.
     */
    public function unsubscribe(string $event, string $listener);

    /**
     * Notifies listeners about an event.
     *
     * @param string $event The unique ID of the event.
     */
    public function dispatch(string $event);
}
