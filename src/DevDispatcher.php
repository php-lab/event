<?php
declare(strict_types=1);

/**
 * @author    Yuriy Davletshin <yuriy.davletshin@gmail.com>
 * @copyright 2015 Yuriy Davletshin
 * @license   MIT
 */
namespace PhpLab\Event;

/**
 * Event dispatcher for development.
 */
class DevDispatcher implements DispatcherInterface
{
    /**
     * @see DevDispatcherTrait
     */
    use DevDispatcherTrait;
}
