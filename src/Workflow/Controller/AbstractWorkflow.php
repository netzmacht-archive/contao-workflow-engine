<?php

namespace Workflow\Controller;

use DcaTools\Data\ConfigBuilder;
use DcGeneral\Data\ModelInterface as EntityInterface;
use Workflow\Handler\ProcessFactory;
use Workflow\Handler\ProcessHandler;
use Workflow\Service\ServiceFactory;

abstract class AbstractWorkflow implements WorkflowInterface
{

	/**
	 * @var \DcGeneral\Data\ModelInterface
	 */
	protected $workflow;

	/**
	 * @var \Workflow\Controller\Controller
	 */
	protected $controller;

	/**
	 * @var \Workflow\Handler\ProcessHandlerInterface[]
	 */
	protected $handlers = array();

	/**
	 * @var array
	 */
	protected $processes = array();

	/**
	 * @var array
	 */
	protected $storeData = array();

	/**
	 * @var bool
	 */
	protected $initialized = false;


	/**
	 * Construct
	 *
	 * @param EntityInterface $entity
	 */
	public function __construct(EntityInterface $entity)
	{
		$this->workflow   = $entity;

		$processes = deserialize($this->workflow->getProperty('processes'), true);

		foreach($processes as $process)
		{
			$this->processes[$process['table']] = $process['process'];
			$this->storeData[$process['table']] = (int) $process['data'];
		}
	}


	/**
	 * Initialize the workflow
	 *
	 * Will return false if workflow is already initialized
	 *
	 * @return bool
	 */
	public function initialize()
	{
		if(!$this->initialized)
		{
			$this->initializeServices();
			$this->initialized = true;

			return true;
		}

		return false;
	}


	/**
	 * @return EntityInterface
	 */
	public function getEntity()
	{
		return $this->workflow;
	}


	/**
	 * @return array
	 */
	public function getProcessConfiguration()
	{
		return $this->processes;
	}


	/**
	 * Set controller
	 *
	 * @param Controller $controller
	 */
	public function setController(Controller $controller)
	{
		$this->controller = $controller;
	}


	/**
	 * @return \Workflow\Controller\Controller
	 */
	public function getController()
	{
		return $this->controller;
	}


	/**
	 * @param $tableName
	 * @return bool
	 */
	public function hasProcess($tableName)
	{
		return isset($this->processes[$tableName]);
	}


	/**
	 * Get current process handler
	 *
	 * @param $tableName
	 *
	 * @return \Workflow\Handler\ProcessHandlerInterface
	 */
	public function getProcessHandler($tableName)
	{
		if(!isset($this->handlers[$tableName]))
		{
			$process = ProcessFactory::create($this->processes[$tableName]);
			$storage = $GLOBALS['container']['workflow.model-state-storage'];
			$handler = new ProcessHandler($process, $this->controller->getEventDispatcher(), $storage);

			$this->handlers[$tableName] = $handler;
		}

		return $this->handlers[$tableName];
	}


	/**
	 * @param EntityInterface $entity
	 * @param $tableName
	 * @return EntityInterface|null
	 */
	public function getParent(EntityInterface $entity, $tableName=null)
	{
		$config = $this->getConfig($entity->getProviderName());

		if($config && $config['parent'])
		{
			$driver = $this->controller->getDataProvider($config['parent']);

			$parent = ConfigBuilder::create($driver)
				->filterEquals('id', $entity->getProperty('pid'))
				->fetch();

			if($tableName == null || $config['parent'] == $tableName)
			{
				return $parent;
			}

			$config = $this->getConfig($config['parent']);

			if($config)
			{
				return $this->getParent($parent, $tableName);
			}
		}

		return null;
	}


	/**
	 * Consider whether model is assigned to workflow
	 *
	 * @param EntityInterface $entity
	 * @return bool
	 */
	public function isAssigned(EntityInterface $entity)
	{
		$config = static::getConfig($entity->getProviderName());

		if($config)
		{
			if($config['assignment'])
			{
				return $this->isEntityAssigned($entity);
			}

			$parent = $this->getParent($entity);

			if($parent)
			{
				return $this->isAssigned($parent);
			}
		}

		return false;
	}


	/**
	 * @param EntityInterface $entity
	 * @return bool
	 */
	protected function isEntityAssigned(EntityInterface $entity)
	{
		return ($entity->getProperty('addWorkflow') && $entity->getProperty('workflow') == $this->workflow->getId());
	}


	/**
	 * Initialize workflow services
	 */
	protected function initializeServices()
	{
		$services = ServiceFactory::forWorkflow($this->workflow, $this->controller);

		/** @var \Workflow\Service\ServiceInterface $service */
		foreach($services as $service)
		{
			$service->initialize();
		}
	}


	/**
	 * @return \Workflow\Entity\Registry
	 */
	protected function getEntityRegistry()
	{
		return $this->controller->getEntityRegistry();
	}


	/**
	 * Load parent if possible from the registry
	 *
	 * @param EntityInterface $entity
	 * @return EntityInterface|mixed
	 */
	protected function loadParent(EntityInterface $entity)
	{
		$config = $this->getConfig($entity->getProviderName());
		$table  = $config['parent'];
		$driver = $this->controller->getDataProvider($table);

		if($this->getEntityRegistry()->hasEntity($table, $entity->getProperty('pid')))
		{
			$parent = $this->getEntityRegistry()->getEntity($table, $entity->getProperty('pid'));
		}
		else
		{
			$parent = ConfigBuilder::create($driver)->setId($entity->getProperty('pid'))->fetch();

			if($parent)
			{
				$this->getEntityRegistry()->addEntity($parent);
			}
		}

		return $parent;
	}

}
