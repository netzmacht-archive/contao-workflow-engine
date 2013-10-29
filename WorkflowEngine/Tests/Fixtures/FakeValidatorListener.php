<?php

namespace WorkflowEngine\Tests\Fixtures;

use WorkflowEngine\Event\ValidateStepEvent;

class FakeValidatorListener
{
    public function valid(ValidateStepEvent $event)
    {
    }

    public function invalid(ValidateStepEvent $event)
    {
        $event->addViolation('Validation error!');
    }
}
