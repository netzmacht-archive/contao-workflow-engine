<?php
/**
 * Created by PhpStorm.
 * User: david
 * Date: 14.11.13
 * Time: 08:52
 */

namespace Workflow\Controller;

use DcaTools\Data\ConfigBuilder;
use DcGeneral\Data\ModelInterface as EntityInterface;
use Workflow\Event\WorkflowTypeEvent;
use Workflow\Exception\WorkflowException;

class WorkflowManager
{

	/**
	 * @var WorkflowInterface[]
	 */
	protected $workflows = array();

	/**
	 * @var array
	 */
	protected $entityWorkflows = array();

	/**
	 * @var Controller
	 */
	protected $controller;

	/**
	 * @var \DcGeneral\Data\DriverInterface
	 */
	protected $driver;


	/**
	 * @param Controller $controller
	 */
	public function setController(Controller $controller)
	{
		$this->controller = $controller;
		$this->driver     = $controller->getDataProvider('tl_workflow');
	}


	/**
	 * Bootstrap all available workflows
	 */
	public function bootstrap()
	{
		/** @var \Workflow\Controller\WorkflowInterface $workflow */
		foreach($GLOBALS['TL_WORKFLOWS'] as $workflow)
		{
			$workflow::bootstrap($this->controller);
		}
	}


	/**
	 * Get assigned workflow for en entity
	 *
	 * If no workflow is found return null
	 *
	 * @param EntityInterface $entity
	 * @return null|WorkflowInterface
	 */
	public function getAssignedWorkflow(EntityInterface $entity)
	{
		$table = $entity->getProviderName();

		if(isset($this->entityWorkflows[$table]) && array_key_exists($entity->getId(), $this->entityWorkflows[$table]))
		{
			return $this->entityWorkflows[$table][$entity->getId()];
		}

		$types    = $this->getWorkflowTypes($entity);
		$active   = null;
		$priority = null;

		if(!count($types))
		{
			return $active;
		}

		/** @var EntityInterface $workflowEntity */
		foreach($this->loadWorkflows($types) as $workflowEntity)
		{
			$id = $workflowEntity->getId();

			if(!isset($this->workflows[$id]))
			{
				$workflow = $this->createInstance($workflowEntity);
				$workflow->setController($this->controller);
				$this->workflows[$id] = $workflow;
			}

			if($this->workflows[$id]->isAssigned($entity))
			{
				if(!$active || $priority === null || $priority > $this->workflows[$id]->getPriority($entity))
				{
					$active   = $this->workflows[$id];
					$priority = $this->workflows[$id]->getPriority($entity);
				}
			}
		}

		if($active)
		{
			$this->controller->setCurrentWorkflow($active);
			$active->initialize();
		}

		$this->entityWorkflows[$entity->getProviderName()][$entity->getId()] = $active;

		return $active;
	}


	/**
	 * @param EntityInterface $entity
	 *
	 * @return \Workflow\Controller\WorkflowInterface
	 * @throws \Workflow\Exception\WorkflowException
	 */
	public function createInstance(EntityInterface $entity)
	{
		$type = $entity->getProperty('workflow');

		if(isset($GLOBALS['TL_WORKFLOWS'][$type]))
		{
			$class = $GLOBALS['TL_WORKFLOWS'][$type];
			return new $class($entity);
		}

		throw new WorkflowException(sprintf('Invalid workflow type "%s"', $type));
	}


	/**
	 * @param $workflowIdentifier
	 * @return mixed
	 * @throws \Workflow\Exception\WorkflowException
	 */
	public static function GetWorkflowClass($workflowIdentifier)
	{
		if(isset($GLOBALS['TL_WORKFLOWS'][$workflowIdentifier]))
		{
			return $GLOBALS['TL_WORKFLOWS'][$workflowIdentifier];
		}

		throw new WorkflowException(sprintf('Unkown workflow identifier "%s"', $workflowIdentifier));
	}


	/**
	 * Get all available workflow identifiers
	 * @return array
	 */
	public static function getAvailableWorkflowIdentifiers()
	{
		return array_keys($GLOBALS['TL_WORKFLOWS']);
	}


	/**
	 * Get all supported data containers
	 *
	 * @return array
	 */
	public static function getSupportedDataContainers()
	{
		$names = array();

		/** @var \Workflow\Controller\WorkflowInterface $workflow */
		foreach($GLOBALS['TL_WORKFLOWS'] as $workflow)
		{
			$names = array_merge($names, $workflow::getSupportedDataContainers());
		}

		return array_values(array_unique($names));
	}


	/**
	 * Get all available workflows for the data container.
	 *
	 * @param $name
	 * @return array
	 */
	public static function getAvailableWorkflowsForDataContainer($name)
	{
		$workflows = array();

		/** @var \Workflow\Controller\WorkflowInterface $workflow */
		foreach($GLOBALS['TL_WORKFLOWS'] as $workflow)
		{
			if(in_array($name, $workflow::getSupportedDataContainers()))
			{
				$workflows[] = $workflow::getIdentifier();
			}
		}

		return $workflows;
	}


	/**
	 * @param EntityInterface $entity
	 * @return array
	 */
	protected function getWorkflowTypes(EntityInterface $entity)
	{
		$eventName = 'workflow.get-workflow-types';
		$event     = new WorkflowTypeEvent($entity);

		$this->controller->getEventDispatcher()->dispatch($eventName, $event);
		return $event->getTypes();
	}


	/**
	 * @param $types
	 *
	 * @return \DcGeneral\Data\CollectionInterface
	 */
	protected function loadWorkflows(array $types)
	{
		return ConfigBuilder::create($this->driver)
			->filterIn('workflow', $types)
			->fetchAll();
	}

}
