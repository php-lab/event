<?php
declare(strict_types=1);

/**
 * @author    Yuriy Davletshin <yuriy.davletshin@gmail.com>
 * @copyright 2015 Yuriy Davletshin
 * @license   MIT
 */
namespace PhpLab\Event;

/**
 * Event dispatcher trait for development.
 * Classes that use this trait MUST implement the \PhpLab\Event\DispatcherInterface.
 */
trait DevDispatcherTrait
{
    use DispatcherTrait;

    /**
     * Notifies listeners about an event.
     *
     * @param string $event The unique ID of the event.
     *
     * @return array
     */
    public function dispatch(string $event): array
    {
        $notified = [];
        if (isset($this->events[$event])) {
            if (! $this->reverseOrder[$event]) {
                $this->reverseOrder[$event] = krsort($this->events[$event], SORT_NUMERIC);
            }
            foreach ($this->events[$event] as $priorityKey => $actionKey) {
                $output = call_user_func($this->subscriptions[$actionKey], $this);
                $notified[] = $this->getSubscriptionInfo($actionKey, $priorityKey, $output);
                if (isset($output[static::STOP_KEY]) && true === $output[static::STOP_KEY]) {
                    break;
                }
            }
        }

        return $notified;
    }

    /**
     * Returns information about subscription.
     *
     * @param string $actionKey   The action key.
     * @param int    $priorityKey The priority key.
     * @param mixed  $output      The additional data.
     *
     * @return array
     */
    private function getSubscriptionInfo(string $actionKey, int $priorityKey, $output): array
    {
        $info = [];
        $info['priority'] = (int) ($priorityKey / static::SUBSCRIPTION_LIMIT);
        $info['number'] = $this->convertNumber($info['priority'], $priorityKey);
        list($info['event'], $info['listener']) = explode(static::KEY_DELIMITER, $actionKey);
        $info['output'] = $output;

        return $info;
    }
}
