<?php

namespace Workflow\Controller;

use Workflow\Event\WorkflowTypeEvent;

interface GetWorkflowListenerInterface
{
	public static function listenerGetWorkflowType(WorkflowTypeEvent $event);
} 