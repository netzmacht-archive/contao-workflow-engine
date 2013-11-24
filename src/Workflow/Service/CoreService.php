<?php

namespace Workflow\Service;

use Workflow\Controller\UserInterface;
use Workflow\Event\SecurityEvent;
use Workflow\Event\StepEvent;
use Workflow\Model\Model;

/**
 * Class CoreService provides is assigned to every workflow providing core features like authentication
 *
 * @package Workflow\Service
 */
class CoreService extends AbstractService
{
	/**
	 * @var array
	 */
	protected static $config = array
	(
		'name' => 'core',
	);

	/**
	 * @var array
	 */
	protected $listeners = array();


	/**
	 * Initialize the core
	 *
	 * @inheritdoc
	 */
	function initialize()
	{
		$dispatcher = $this->controller->getEventDispatcher();
		$dispatcher->addListener('workflow.check_credentials', array($this, 'checkCredentials'));
	}


	/**
	 * @throws \RuntimeException
	 */
	public static function getConfig()
	{
		throw new \RuntimeException('Core service is not configurable');
	}


	/**
	 * Basic security listener checking by user BackendUser::hasAccess
	 *
	 * @param SecurityEvent $event
	 */
	public function checkCredentials(SecurityEvent $event)
	{
		if(empty($roles) ||
			$this->controller->getUser()->hasRole($event->getProcessName(), UserInterface::ROLE_ADMIN) ||
			$this->controller->getUser()->hasRole($event->getProcessName(), $event->getStep()->getRoles()))
		{
			$event->grantAccess(true);
		}
		else
		{
			$event->grantAccess(false);
		}
	}


	/**
	 * Notify parent that child has changed
	 *
	 * @param StepEvent $event
	 */
	public function notifyParent(StepEvent $event)
	{
		$entity = $event->getModel()->getEntity();
		$parent = $this->controller->getCurrentWorkflow()->getParent($entity);

		if($parent)
		{
			$model   = new Model($parent, $this->controller);
			$handler = $this->controller->getCurrentWorkflow()->getProcessHandler($parent->getProviderName());

			if(!$handler->getCurrentState($model))
			{
				$handler->start($model);
			}
			else {
				$handler->reachNextState($model, 'change');
			}
		}
	}

}
