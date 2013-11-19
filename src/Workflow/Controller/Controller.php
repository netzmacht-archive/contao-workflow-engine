<?php

namespace Workflow\Controller;

use DcGeneral\Data\ModelInterface as EntityInterface;
use Symfony\Component\EventDispatcher\EventDispatcher;
use Workflow\Entity\Registry;
use Workflow\Event\WorkflowTypeEvent;
use Workflow\Model\Model;
use Workflow\Model\ModelInterface;


class Controller
{
	/**
	 * @var \DcaTools\Data\DriverManagerInterface
	 */
	protected $driverManager;

	/**
	 * @var \Symfony\Component\EventDispatcher\EventDispatcher
	 */
	protected $eventDispatcher;

	/**
	 * @var \Workflow\Controller\WorkflowManager
	 */
	protected $workflowManager;

	/**
	 * @var \Workflow\Model\ModelInterface
	 */
	protected $currentModel;

	/**
	 * @var \Workflow\Controller\WorkflowInterface
	 */
	protected $currentWorkflow;

	/**
	 * @var \Workflow\Model\ModelInterface[]
	 */
	protected $models = array();

	/**
	 * @var \Workflow\Controller\WorkflowInterface[]
	 */
	protected $workflows = array();


	/**
	 * @param WorkflowManager $workflowManager
	 * @param \DcaTools\Data\DriverManagerInterface $driverManager
	 * @param Registry $registry
	 * @param EventDispatcher $eventDispatcher
	 */
	public function __construct(WorkflowManager $workflowManager, $driverManager, Registry $registry, EventDispatcher $eventDispatcher)
	{
		$this->workflowManager = $workflowManager;
		$this->driverManager   = $driverManager;
		$this->eventDispatcher = $eventDispatcher;
		$this->registry        = $registry;

		$this->workflowManager->bootstrap($this);
	}


	/**
	 * @param EntityInterface $entity
	 * @return bool
	 */
	public function initialize(EntityInterface $entity)
	{
		if(!isset($this->models[$entity->getProviderName()][$entity->getId()]))
		{
			$this->currentModel = new Model($entity, $this);

			if(!$this->initializeWorkflow($this->currentModel))
			{
				return false;
			}

			$state = $this->getProcessHandler()->getCurrentState($this->currentModel);

			if(!$state)
			{
				$this->getProcessHandler()->start($this->currentModel);
			}
		}

		$this->currentModel    = $this->models[$entity->getProviderName()][$entity->getId()]['model'];
		$this->currentWorkflow = $this->models[$entity->getProviderName()][$entity->getId()]['workflow'];

		return true;
	}


	/**
	 * @return \Workflow\Entity\ModelState
	 */
	public function getCurrentState()
	{
		return $this->getProcessHandler()->getCurrentState($this->currentModel);
	}


	/**
	 * @return WorkflowInterface
	 */
	public function getCurrentWorkflow()
	{
		return $this->currentWorkflow;
	}


	/**
	 * @return ModelInterface
	 */
	public function getCurrentModel()
	{
		return $this->currentModel;
	}


	/**
	 * @param $stateName
	 * @return \Workflow\Entity\ModelState
	 */
	public function reachNextState($stateName)
	{
		return $this->getProcessHandler()->reachNextState($this->currentModel, $stateName);
	}


	/**
	 * @return \Workflow\Handler\ProcessHandlerInterface
	 */
	public function getProcessHandler()
	{
		return $this->currentWorkflow->getProcessHandler($this->currentModel->getEntity()->getProviderName());
	}


	/**
	 * @param $tableName
	 * @return \DcGeneral\Data\DriverInterface
	 */
	public function getDataProvider($tableName)
	{
		return $this->driverManager->getDataProvider($tableName);
	}


	/**
	 * @return EventDispatcher
	 */
	public function getEventDispatcher()
	{
		return $this->eventDispatcher;
	}


	/**
	 * Initialize all matched workflows
	 *
	 * @param ModelInterface $model
	 * @return bool
	 */
	protected function initializeWorkflow(ModelInterface $model)
	{
		$tableName  = $model->getEntity()->getProviderName();
		$tableId    = $model->getEntity()->getId();
		$workflow   = $this->getAssignedWorkflow($model->getEntity());

		if($workflow)
		{
			$workflowId = $workflow->getEntity()->getId();

			$this->models[$tableName][$tableId] = array
			(
				'model'    => $model,
				'workflow' => $workflow,
			);

			$this->currentWorkflow = $workflow;

			if(!isset($this->workflows[$workflowId]))
			{
				$this->workflows[$workflowId] = $workflow;

				$workflow->initialize();
			}

			return true;
		}

		return false;
	}


	/**
	 * @param EntityInterface $entity
	 * @return \Workflow\Controller\WorkflowInterface
	 */
	protected function getAssignedWorkflow(EntityInterface $entity)
	{
		$types    = $this->getWorkflowTypes($entity);
		$active   = null;
		$priority = null;

		if(!count($types))
		{
			return $active;
		}

		foreach($this->workflowManager->loadWorkflows($types) as $workflow)
		{
			$workflow = $this->workflowManager->create($workflow);
			$workflow->setController($this);

			if($workflow->isAssigned($entity))
			{
				if(!$active || $priority === null || $priority > $workflow->getPriority($entity))
				{
					$active = $workflow;
					$priority = $workflow->getPriority($entity);
				}
			}
		}

		return $active;
	}


	/**
	 * @param EntityInterface $entity
	 * @return array
	 */
	protected function getWorkflowTypes(EntityInterface $entity)
	{
		$eventName = 'workflow.controller.get-workflow-types';
		$event     = new WorkflowTypeEvent($entity);

		$this->eventDispatcher->dispatch($eventName, $event);
		return $event->getTypes();
	}

}
