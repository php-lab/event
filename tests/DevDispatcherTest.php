<?php
declare(strict_types=1);

/**
 * @author    Yuriy Davletshin <yuriy.davletshin@gmail.com>
 * @copyright 2015 Yuriy Davletshin
 * @license   MIT
 */
namespace PhpLab\Event;

class DevDispatcherTest extends DispatcherTest
{
    public function setUp()
    {
        $this->app = new DevDispatcher();
    }
}
