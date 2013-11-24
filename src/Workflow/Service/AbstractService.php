<?php

namespace Workflow\Service;

use DcGeneral\Data\ModelInterface as EntityInterface;
use Workflow\Controller\Controller;
use Workflow\Model\Model;


/**
 * Class AbstractService implements basic methods for workflow services
 *
 * @package Workflow\Service
 */
abstract class AbstractService implements ServiceInterface
{

	/**
	 * @var \DcGeneral\Data\ModelInterface
	 */
	protected $service;


	/**
	 * @var Controller
	 */
	protected $controller;


	/**
	 * @var array
	 */
	protected static $config;


	/**
	 * @param EntityInterface $service
	 * @param Controller $controller
	 */
	public function __construct(EntityInterface $service, Controller $controller)
	{
		$this->service = $service;
		$this->controller = $controller;

		$this->service->setProperty('actions', deserialize($this->service->getProperty('actions'), true));
		$this->service->setProperty('events', deserialize($this->service->getProperty('events'), true));
		$this->service->setProperty('filter', deserialize($this->service->getProperty('filter'), true));
		$this->service->setProperty('steps', deserialize($this->service->getProperty('steps'), true));
		$this->service->setProperty('roles', deserialize($this->service->getProperty('roles'), true));
	}


	/**
	 * Initialize the workflow service
	 *
	 * @inheritdoc
	 */
	abstract function initialize();


	/**
	 * Get config
	 *
	 * @return Config
	 */
	public static function getConfig()
	{
		return static::$config;
	}


	/**
	 * Apply filter which are
	 * @param EntityInterface $entity
	 * @return bool
	 */
	public function applyFilter(EntityInterface $entity)
	{
		if(!$this->service->getProperty('addFilter'))
		{
			return true;
		}

		$reference = $this->service->getProperty('filterReference');

		if($reference && $reference != $entity->getProviderName())
		{
			$entity = $this->controller->getCurrentWorkflow()->getParent($entity, $reference);

			if(!$entity)
			{
				return false;
			}
		}

		$filters = deserialize($this->service->getProperty('filter'), true);
		$match   = false;

		foreach($filters as $filter)
		{
			switch($filter['operation'])
			{
				case 'equals':
					$match = ($entity->getProperty($filter['property']) == $filter['value']);
					break;

				case 'lt':
					$match = ($entity->getProperty($filter['property']) < $filter['value']);
					break;

				case 'gt':
					$match = ($entity->getProperty($filter['property']) > $filter['value']);
					break;

				case 'not':
					$match = ($entity->getProperty($filter['property']) != $filter['value']);
					break;

				default:
					$match = false;
			}

			if(!$match && $this->service->getProperty('filterMode') == 'and')
			{
				return false;
			}
			elseif($match && $this->service->getProperty('filterMode') == 'or')
			{
				return true;
			}
		}

		return $match;
	}


	/**
	 * Apply roles
	 * @return bool
	 */
	public function applyRoles()
	{
		/** @var \BackendUser $user */
		$user   = \BackendUser::getInstance();
		$roles  = $this->service->getProperty('roles');
		$table  = $this->service->getProperty('tableName');
		$config = 'workflow_' . $this->controller->getCurrentWorkflow()->getProcessHandler($table)->getProcess()->getName();

		return $user->hasAccess($roles, $config);
	}


	/**
	 * Apply Step filter
	 */
	public function applySteps(EntityInterface $entity)
	{
		$table = $this->service->getProperty('tableName');
		$state = $this->controller->getCurrentWorkflow()->getProcessHandler($table)->getCurrentState(new Model($entity, $this->controller));

		return (!$state || in_array($state->getStepName(), $this->service->getProperty('steps')));
	}


	/**
	 * @return mixed|string
	 */
	public function applyRequestAction()
	{
		return in_array($this->controller->getRequestAction(), $this->service->getProperty('actions'));
	}


	/**
	 * @param EntityInterface $entity
	 * @return bool|mixed
	 */
	protected function isAssigned(EntityInterface $entity)
	{
		if($entity->getProviderName() == $this->service->getProperty('tableName'))
		{
			return $this->controller->getCurrentWorkflow()->isAssigned($entity);
		}

		return false;
	}

}
