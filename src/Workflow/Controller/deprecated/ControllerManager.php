<?php

namespace Workflow\Controller;


use DcaTools\Data\ConfigBuilder;
use DcaTools\Data\DriverManagerInterface;
use DcGeneral\Data\ModelInterface;
use Symfony\Component\EventDispatcher\EventDispatcher;
use Workflow\Entity\Workflow;
use Workflow\Exception\WorkflowException;
use Workflow\Handler\ProcessFactory;
use Workflow\Handler\ProcessHandler;

/**
 * Class ControllerManager
 *
 * @package Workflow\Controller
 */
class ControllerManager
{

	/**
	 * @var \Workflow\Handler\ProcessHandlerInterface[]
	 */
	protected $handlers = array();

	/**
	 * @var Controller[]
	 */
	protected $controllers = array();

	/**
	 * @var DriverManagerInterface
	 */
	protected $driverManager;

	/**
	 * @var \Symfony\Component\EventDispatcher\EventDispatcher
	 */
	protected $eventDispatcher;

	/**
	 * @var WorkflowManager
	 */
	protected $workflowManager;


	/**
	 * @param EventDispatcher $eventDispatcher
	 * @param WorkflowManager $workflowManager
	 * @param DriverManagerInterface $driverManager
	 */
	public function __construct(EventDispatcher $eventDispatcher, WorkflowManager $workflowManager, $driverManager)
	{
		$this->eventDispatcher = $eventDispatcher;
		$this->driverManager   = $driverManager;
		$this->workflowManager = $workflowManager;
	}


	/**
	 * @param ModelInterface $entity
	 *
	 * @return true
	 */
	public function isEnabled(ModelInterface $entity)
	{
		return !in_array($entity->getProviderName(), $GLOBALS['TL_CONFIG']['workflow_disabledTables']);
	}


	/**
	 * @param ModelInterface $entity
	 *
	 * @throws
	 * @return Controller
	 */
	public function getController(ModelInterface $entity)
	{
		if(!$this->isEnabled($entity))
		{
			$message = sprintf('Workflow is disabled for datacontainer "%s"', $entity->getProviderName());
			throw new WorkflowException($message);
		}

		$workflow = $this->workflowManager->getWorkflow($entity);

		if(!$workflow)
		{
			$message = sprintf('No workflow found for entity "%s: %s"', $entity->getProviderName(), $entity->getId());
			throw new WorkflowException($message);
		}

		if(!isset($this->controllers[$workflow->getId()]))
		{
			$handler    = $this->getProcessHandler($workflow->getProperty('process'));
			$controller = new Controller($workflow, $handler, $this->eventDispatcher, $this->driverManager);

			$this->controllers[$workflow->getId()] = $controller;
		}

		return $this->controllers[$workflow->getId()];
	}


	/**
	 * Get a process handler
	 *
	 * @param $processName
	 *
	 * @return \Workflow\Handler\ProcessHandlerInterface
	 */
	protected function getProcessHandler($processName)
	{
		if(!isset($this->handlers[$processName]))
		{
			$stateStorage = $GLOBALS['container']['workflow.model-state-storage'];
			$process      = ProcessFactory::create($processName);

			$this->handlers[$processName] = new ProcessHandler($process, $this->eventDispatcher, $stateStorage);
		}

		return $this->handlers[$processName];
	}


	/**
	 * @return EventDispatcher
	 */
	public function getEventDispatcher()
	{
		return $this->eventDispatcher;
	}


	/**
	 * @return DriverManagerInterface
	 */
	public function getDriverManager()
	{
		return $this->driverManager;
	}

}
