<?php
declare(strict_types=1);

/**
 * @author    Yuriy Davletshin <yuriy.davletshin@gmail.com>
 * @copyright 2015 Yuriy Davletshin
 * @license   MIT
 */
namespace PhpLab\Event;

/**
 * Event dispatcher trait.
 * Classes that use this trait MUST implement the \PhpLab\Event\DispatcherInterface.
 */
trait DispatcherTrait
{
    /**
     * @var string[] Contains subscription keys.
     */
    private $events = [];

    /**
     * @var callable[] Contains subscriptions.
     */
    private $subscriptions = [];

    /**
     * @var bool[] Were there priorities sorted in reverse order?
     */
    private $reverseOrder = [];

    /**
     * @var int $subscriptionId Last subscription number.
     */
    private $subscriptionId = 0;

    /**
     * Adds a subscription.
     *
     * @param string   $event    The unique ID of the event.
     * @param string   $listener The unique ID of the listener.
     * @param callable $action   The closure or invokable object.
     * @param int      $priority The priority for the subscription.
     *
     * @throws AlreadyExistsException If the subscription already exists.
     */
    public function subscribe(string $event, string $listener, callable $action, int $priority = 0)
    {
        $actionKey = $event . static::KEY_DELIMITER . $listener;
        if (! isset($this->subscriptions[$actionKey])) {
            $priorityKey = $this->convertNumber($priority, ++$this->subscriptionId);
            $this->events[$event][$priorityKey] = $actionKey;
            $this->subscriptions[$actionKey] = $action;
            $this->reverseOrder[$event] = false;

            return;
        }
        throw new AlreadyExistsException(
            sprintf('Subscription "event: %s, listener: %s" already exists.', $event, $listener)
        );
    }

    /**
     * Removes a subscription.
     *
     * @param string $event    The unique ID of the event.
     * @param string $listener The unique ID of the listener.
     */
    public function unsubscribe(string $event, string $listener)
    {
        if (isset($this->events[$event])) {
            $actionKey = $event . static::KEY_DELIMITER . $listener;
            $priorityKey = array_search($actionKey, $this->events[$event]);
            if (false !== $priorityKey) {
                unset($this->subscriptions[$actionKey]);
                unset($this->events[$event][$priorityKey]);
            }
        }
    }

    /**
     * Notifies listeners about an event.
     *
     * @param string $event The unique ID of the event.
     */
    public function dispatch(string $event, array $args = [])
    {
        if (isset($this->events[$event])) {
            if (! $this->reverseOrder[$event]) {
                $this->reverseOrder[$event] = krsort($this->events[$event], SORT_NUMERIC);
            }
            foreach ($this->events[$event] as $priorityKey => $actionKey) {
                $output = call_user_func($this->subscriptions[$actionKey], $this, $args);
                if (isset($output[static::STOP_KEY]) && true === $output[static::STOP_KEY]) {
                    break;
                }
            }
        }
    }

    /**
     * Returns a sortable priority key.
     *
     * @param int $priority The priority
     * @param int $number   Number of the subscription
     *
     * @return int The priority key
     */
    private function convertNumber(int $priority, int $number)
    {
        return ($priority * static::SUBSCRIPTION_LIMIT) + static::SUBSCRIPTION_LIMIT - $number;
    }
}
