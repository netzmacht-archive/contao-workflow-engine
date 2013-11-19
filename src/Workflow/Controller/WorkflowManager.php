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
	public function __construct(Controller $controller)
	{
		$this->controller = $controller;
		$this->driver     = $controller->getDataProvider('tl_worfklow');
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


	public function getAssignedWorkflow(EntityInterface $entity)
	{
		if(array_key_exists($entity->getId(), $this->entityWorkflows[$entity->getProviderName()]))
		{
			return $this->entityWorkflows[$entity->getProviderName()][$entity->getId()];
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

			if(!isset($workflows[$id]))
			{
				$workflow = $this->createInstance($workflowEntity);
				$workflow->setController($this->controller);
				$workflow->initialize();

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

		$this->entityWorkflows[$entity->getProviderName()][$entity->getId()] = $active;

		return $active;
	}


	/**
	 * @param EntityInterface $entity
	 *
	 * @return \Workflow\Controller\WorkflowInterface
	 * @throws \Workflow\Exception\WorkflowException
	 */
	protected function createInstance(EntityInterface $entity)
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
	 * @param EntityInterface $entity
	 * @return array
	 */
	protected function getWorkflowTypes(EntityInterface $entity)
	{
		$eventName = 'workflow.controller.get-workflow-types';
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
