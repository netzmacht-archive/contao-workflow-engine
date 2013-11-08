<?php
/**
 * Created by PhpStorm.
 * User: david
 * Date: 08.11.13
 * Time: 12:53
 */

namespace Workflow\Controller;


use DcGeneral\Data\DCGE;
use DcGeneral\Data\ModelInterface;
use Symfony\Component\EventDispatcher\EventDispatcher;
use Workflow\Event\InitialisationEvent;
use Workflow\Handler\ProcessHandler;
use Workflow\Model\Model;


class Controller
{

	/**
	 * @var \Workflow\Model\ModelInterface
	 */
	protected $model;

	/**
	 * @var \Symfony\Component\EventDispatcher\EventDispatcher
	 */
	protected $dispatcher;

	/**
	 * @var \Workflow\Data\DriverManagerInterface
	 */
	protected $driverManager;

	/**
	 * @var Workflow
	 */
	protected $workflow;


	/**
	 * @param ModelInterface $model
	 * @param Workflow $workflow
	 * @param EventDispatcher $dispatcher
	 * @param \Workflow\Data\DriverManagerInterface $driverManager
	 */
	public function __construct(ModelInterface $model, Workflow $workflow, EventDispatcher $dispatcher, $driverManager)
	{
		$this->model = new Model($model, $this);
		$this->workflow = $workflow;
		$this->dispatcher = $dispatcher;
		$this->driverManager = $driverManager;
	}


	/**
	 * Initialize the controller
	 */
	public function initialize()
	{
		$this->workflow->initialize($this);

		$state = $this->getCurrentState();

		if(!$state)
		{
			$state = $this->getProcessHandler()->start($this->model);
		}

		$event = new InitialisationEvent($this->model, $state);
		$eventName = sprintf('workflow.%s.initialized', $state->getProcessName());

		$this->dispatcher->dispatch($eventName, $event);
	}


	/**
	 * @param $stateName
	 *
	 * @return \Workflow\Entity\ModelState
	 */
	public function reachNextState($stateName)
	{
		$state = $this->workflow->getProcessHandler()->reachNextState($this->model, $stateName);

		if($state->getSuccessful())
		{
			if($state->getMeta(DCGE::MODEL_IS_CHANGED))
			{
				$driver = $this->driverManager->getDataProvider($state->getProviderName());
				$driver->save($state);
			}

			if($this->model->getEntity()->getMeta(DCGE::MODEL_IS_CHANGED))
			{
				$driver = $this->driverManager->getDataProvider($this->model->getEntity()->getProviderName());
				$driver->save($this->model->getEntity());
			}
		}

		return $state;
	}


	/**
	 * @return \Workflow\Entity\ModelState
	 */
	public function getCurrentState()
	{
		return $this->getProcessHandler()->getCurrentState($this->model);
	}


	/**
	 * @return \Workflow\Model\ModelInterface
	 */
	public function getModel()
	{
		return $this->model;
	}


	/**
	 * @return Workflow
	 */
	public function getWorkflow()
	{
		return $this->workflow;
	}


	/**
	 * @return \Workflow\Data\DriverManagerInterface
	 */
	public function getDriverManager()
	{
		return $this->driverManager;
	}


	/**
	 * @return ProcessHandler
	 */
	public function getProcessHandler()
	{
		return $this->workflow->getProcessHandler();
	}


	/**
	 * @return EventDispatcher
	 */
	public function getEventDispatcher()
	{
		return $this->dispatcher;
	}

}
