<?php


namespace Workflow\Handler;

use Symfony\Component\EventDispatcher\EventDispatcher;
use Workflow\Data\DriverManagerInterface;
use Workflow\Entity\ModelState;
use Workflow\Handler\ProcessHandlerInterface;
use Workflow\Model\ModelInterface;
use Workflow\Process\ProcessManager;


/**
 * Class Environment provides access for relevant classes used for
 * @package Workflow\Process
 */
class Environment
{

	/**
	 * @var ModelInterface
	 */
	protected $model;


	/**
	 * @var \DcGeneral\Data\ModelInterface
	 */
	protected $workflow;


	/**
	 * @var DriverManagerInterface
	 */
	protected $driverManager;


	/**
	 * @var ProcessManager
	 */
	protected $processManager;


	/**
	 * @var EventDispatcher
	 */
	protected $dispatcher;


	/**
	 * @var ProcessHandlerInterface
	 */
	protected $handler;


	/**
	 * @var ModelState
	 */
	protected $state;


	/**
	 * @param EventDispatcher $dispatcher
	 */
	public function setEventDispatcher(EventDispatcher $dispatcher)
	{
		$this->dispatcher = $dispatcher;
	}


	/**
	 * @return EventDispatcher
	 */
	public function getEventDispatcher()
	{
		return $this->dispatcher;
	}


	/**
	 * @param DriverManagerInterface|\DcGeneral\DC_General $driverManager
	 */
	public function setDriverManager($driverManager)
	{
		$this->driverManager = $driverManager;
	}


	/**
	 * @return DriverManagerInterface
	 */
	public function getDriverManager()
	{
		return $this->driverManager;
	}


	/**
	 * @param ModelInterface $model
	 */
	public function setCurrentModel(ModelInterface $model)
	{
		$this->model = $model;
	}


	/**
	 * @return mixed
	 */
	public function getCurrentModel()
	{
		return $this->model;
	}


	/**
	 * @param ProcessManager $processManager
	 */
	public function setProcessManager(ProcessManager $processManager)
	{
		$this->processManager = $processManager;
	}


	/**
	 * @return ProcessManager
	 */
	public function getProcessManager()
	{
		return $this->processManager;
	}


	/**
	 * @param \DcGeneral\Data\ModelInterface $workflow
	 */
	public function setCurrentWorkflow(\DcGeneral\Data\ModelInterface $workflow)
	{
		$this->workflow = $workflow;
	}


	/**
	 * @return \DcGeneral\Data\ModelInterface
	 */
	public function getCurrentWorkflow()
	{
		return $this->workflow;
	}


	/**
	 * @param ProcessHandlerInterface $handler
	 */
	public function setCurrentProcessHandler(ProcessHandlerInterface $handler)
	{
		$this->handler = $handler;
	}


	/**
	 * @return ProcessHandlerInterface
	 */
	public function getCurrentProcessHandler()
	{
		return $this->handler;
	}


	/**
	 * @param ModelState $state
	 */
	public function setCurrentState(ModelState $state)
	{
		$this->state = $state;
	}


	/**
	 * @return ModelState
	 */
	public function getCurrentState()
	{
		return $this->state;
	}




}
