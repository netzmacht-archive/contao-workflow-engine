<?php

namespace Workflow\Service;

use DcaTools\Definition;
use DcaTools\Event\Helper;
use DcaTools\Event\Listener\Operation;
use DcaTools\Event\Listener\DataContainer;
use DcaTools\Event\OperationEvent;
use DcaTools\Event\PermissionEvent;
use DcaTools\Event\Priority;
use DcaTools\Controller;
use Workflow\Model\Model;


/**
 * Class RestrictAccessService
 * @package Workflow\Service
 */
class RestrictAccessService extends AbstractService
{

	/**
	 * @var array|\Workflow\Service\Config
	 */
	protected static $config = array
	(
		'identifier' => 'restrict-access',
		'version'    => '1.0.0',
		'properties' => array
		(
			'scope'  => array('steps','roles'),
			'config' => array('restrictions', 'restrict_operations'),
		),
	);


	/**
	 * @var \BackendUser
	 */
	protected $user;


	/**
	 * @var array
	 */
	protected $roles;


	/**
	 * @inheritdoc
	 */
	function initialize()
	{
		$this->user  = \BackendUser::getInstance();
		$this->roles = deserialize($this->service->getProperty('roles'), true);

		$this->initializeOperations();
		$this->initializeSteps();
	}


	/**
	 * Initialize operation restrictions
	 */
	protected function initializeOperations()
	{
		$operations = deserialize($this->service->getProperty('restrict_operations'), true);

		foreach($operations as $operation)
		{
			list($table, $scope, $name) = explode('::', $operation['operation']);

			if($operation['mode'] == 'hide')
			{
				$listener = Helper::getListener(array($this, 'disableIcon'), Priority::BEFORE);
			}
			else {
				$listener = Helper::getListener(array($this, 'disableIcon'), Priority::BEFORE);
			}

			$eventName = sprintf('dcatools.%s.%s.%s', $table, $scope == 'global' ? 'global_operations' : 'operations', $name);
			$this->controller->getEventDispatcher()->addListener($eventName, $listener);

			if($scope == 'global')
			{
				Controller::getInstance($table)->enableGlobalOperationEvents($name);
			}
			else {
				Controller::getInstance($table)->enableOperationEvents($name);
			}
		}
	}


	/**
	 * Initialize step restrictions
	 */
	protected function initializeSteps()
	{
		$state = $this->controller->getCurrentState();

		if($state && in_array($state->getStepName(), $this->service->getProperty('steps')))
		{
			$restrictions = deserialize($this->service->getProperty('restrictions'), true);

			foreach($restrictions as $restriction)
			{
				// user has access, no limitation
				if($this->user->hasAccess($this->roles, sprintf('workflow_%s', $restriction['table'])))
				{
					continue;
				}

				$definition = Definition::getDataContainer($restriction['table']);

				if($restriction['notSortable'] && $definition->get('list/sorting/fields/0') == 'sorting')
				{
					$definition->set('list/sorting/fields', array('sorting '));

					if($restriction['table'] == \Input::get('table'))
					{
						$event = new PermissionEvent($this->controller->getModel()->getEntity(), array('error' => ''));
						DataContainer::forbidden($event, array('act' => 'paste'));
					}
				}

				$definition->set('config/closed', (bool) $restriction['closed']);
				$definition->set('config/notEditable', (bool) $restriction['notEditable']);
				$definition->set('config/notDeletable', (bool) $restriction['notDeletable']);
			}
		}
	}


	/**
	 * @param OperationEvent $event
	 */
	public function disableIcon(OperationEvent $event)
	{
		/** @var \BackendUser $user */
		$user    = \BackendUser::getInstance();
		$roles   = deserialize($this->service->getProperty('roles'), true);

		$model   = $this->getCurrentModel($event);
		$handler = $this->controller->getProcessHandler();
		$state   = $handler->getCurrentState($model);

		if(!$state)
		{
			$state = $handler->start($model);
		}

		if(!$state || in_array($state->getStepName(), $this->service->getProperty('steps')))
		{
			if(!$state || !$user->hasAccess($roles, sprintf('workflow_%s', $model->getEntity()->getProviderName())))
			{
				Operation::disableIcon($event, array('value' => true));
			}
		}
	}


	/**
	 * @param OperationEvent $event
	 */
	public function hideIcon(OperationEvent $event)
	{
		/** @var \BackendUser $user */
		$user    = \BackendUser::getInstance();
		$roles   = deserialize($this->service->getProperty('roles'), true);
		$handler = $this->controller->getProcessHandler();
		$model   = $this->getCurrentModel($event);
		$state   = $handler->getCurrentState($model);

		if($state && in_array($state->getStepName(), $this->service->getProperty('steps')))
		{
			if(!$state || !$user->hasAccess($roles, sprintf('workflow_%s', $model->getEntity()->getProviderName())))
			{
				$event->getSubject()->hide();
			}
		}
	}


	/**
	 * @param OperationEvent $event
	 * @return \Workflow\Model\ModelInterface
	 */
	protected function getCurrentModel(OperationEvent $event)
	{
		/** @var \DcGeneral\Data\ModelInterface $model */
		$model = $event->getSubject()->getModel();

		// only use current model if current table is the workflow table, otherwise use parent table
		if($model && $model->getProviderName() == $this->controller->getWorkflow()->getTable())
		{
			return new Model($model, $this->controller);
		}

		return $this->controller->getModel();
	}

}
