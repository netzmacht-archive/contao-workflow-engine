<?php

namespace WorkflowEngine\Tests\Fixtures;

class FakeProcessListener
{
    public static $call = 0;

    public function handleSucccess()
    {
        self::$call++;
    }
}
