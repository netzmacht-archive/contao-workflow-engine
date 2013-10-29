<?php

namespace WorkflowEngine;


use DcaTools\Event\EventDispatcher;
use DcGeneral\Data\DefaultDriver;
use DcGeneral\Data\DriverInterface;
use WorkflowEngine\Flow\Process;
use WorkflowEngine\Flow\Step;
use WorkflowEngine\Handler\ProcessHandler;
use WorkflowEngine\Model\ModelManager;
use WorkflowEngine\Model\ModelStorage;

class Registry
{

	/**
	 * @var ProcessHandler[]
	 */
	protected $handlers = array();


	/**
	 * @var Process[]
	 */
	protected $processes = array();


	/**
	 * @var ModelStorage
	 */
	protected $stateStorage;


	/**
	 * @var ModelManager
	 */
	protected $modelManager;


	/**
	 * @var EventDispatcher[]
	 */
	protected $dispatchers = array();


	/**
	 * @var \DcGeneral\Data\DriverInterface
	 */
	protected $drivers = array();


	/**
	 * @param $processName
	 * @return ProcessHandler
	 */
	public function getProcessHandler($processName)
	{
		if(!isset($this->handlers[$processName]))
		{
			$this->handlers[$processName] = new ProcessHandler($this->getProcess($processName), $this);
		}

		return $this->handlers[$processName];
	}


	/**
	 * @param $dataContainer
	 * @return EventDispatcher
	 */
	public function getEventDispatcher($dataContainer)
	{
		if(!isset($this->dispatchers[$dataContainer]))
		{
			$this->dispatchers[$dataContainer] = new EventDispatcher();
		}

		return $this->dispatchers[$dataContainer];
	}


	/**
	 * @param $processName
	 * @return Process
	 */
	public function getProcess($processName)
	{
		if(!isset($this->processes[$processName]))
		{
			$steps = array();
			$config = $GLOBALS['TL_WORKFLOW'][$processName];

			foreach($config['steps'] as $stepName => $stepConfig)
			{
				$nextStates  = isset($stepConfig['next_states']) ? $stepConfig['next_states'] : array();
				$modelStatus = isset($stepConfig['model_status']) ? $stepConfig['model_status'] : array();
				$roles       = isset($stepConfig['roles']) ? $stepConfig['roles'] : array();
				$onInvalid   = isset($stepConfig['on_invalid']) ? $stepConfig['on_invalid'] : array();

				$steps[$stepName] = new Step($stepName, $stepConfig['label'], $nextStates, $modelStatus, $roles, $onInvalid);
			}

			$this->processes[$processName] = new Process($processName, $steps, $config['start'], $config['end']);
		}

		return $this->processes[$processName];
	}


	/**
	 * @return ModelStorage
	 */
	public function getStateStorage()
	{
		if(!$this->stateStorage)
		{
			$driver = $this->getDataProvider('tl_workflow_state');
			$this->stateStorage = new ModelStorage($this->getModelManager(), $driver);
		}

		return $this->stateStorage;
	}


	/**
	 * @return ModelManager
	 */
	public function getModelManager()
	{
		if(!$this->modelManager)
		{
			$this->modelManager = new ModelManager($this);
		}

		return $this->modelManager;
	}


	/**
	 * @param $dataContainer
	 * @return \DcGeneral\Data\DriverInterface
	 */
	public function getDataProvider($dataContainer)
	{
		if(!isset($this->drivers[$dataContainer]))
		{
			// FIXME: Check the config and fetch from dc general if used
			$driver = new DefaultDriver();
			$driver->setBaseConfig(array('source' => $dataContainer));

			$this->drivers[$dataContainer] = $driver;
		}

		return $this->drivers[$dataContainer];
	}


	/**
	 * @param $dataContainer
	 * @param EventDispatcher $dispatcher
	 */
	public function registerEventDispatcher($dataContainer, EventDispatcher $dispatcher)
	{
		$this->dispatchers[$dataContainer] = $dispatcher;
	}


	/**
	 * @param $dataContainer
	 * @param DriverInterface $driver
	 */
	public function registerDataProvider($dataContainer, DriverInterface $driver)
	{
		$this->drivers[$dataContainer] = $driver;
	}


	/**
	 * @param $processName
	 * @param ProcessHandler $handler
	 */
	public function registerProcessHandlers($processName, ProcessHandler $handler)
	{
		$this->handlers[$processName] = $handler;
	}


	/**
	 * @param ModelManager $modelManager
	 */
	public function registerModelManager(ModelManager $modelManager)
	{
		$this->modelManager = $modelManager;
	}


	/**
	 * @param Process $process
	 */
	public function registerProcess(Process $process)
	{
		$this->processes[$process->getName()] = $process;
	}


	/**
	 * @param \WorkflowEngine\Model\ModelStorage $stateStorage
	 */
	public function registerStateStorage($stateStorage)
	{
		$this->stateStorage = $stateStorage;
	}

} 