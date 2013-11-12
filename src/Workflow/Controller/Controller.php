<?php
/**
 * Created by PhpStorm.
 * User: david
 * Date: 08.11.13
 * Time: 12:53
 */

namespace Workflow\Controller;


use DcaTools\Data\ConfigBuilder;
use DcGeneral\Data\DCGE;
use DcGeneral\Data\ModelInterface;
use Symfony\Component\EventDispatcher\EventDispatcher;
use Workflow\Entity\Workflow;
use Workflow\Event\InitialisationEvent;
use Workflow\Handler\ProcessHandler;
use Workflow\Handler\ProcessHandlerInterface;
use Workflow\Model\Model;
use Workflow\Service\ServiceFactory;


/**
 * Class Controller allow to run workflow tasks for an assigned Model and provides access to several objects
 *
 * @package Workflow\Controller
 */
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
	 * @var \DcaTools\Data\DriverManagerInterface
	 */
	protected $driverManager;

	/**
	 * @var Workflow
	 */
	protected $workflow;

	/**
	 * @var \Workflow\Handler\ProcessHandlerInterface
	 */
	protected $handler;



	/**
	 * @param ModelInterface $model
	 * @param Workflow $workflow
	 * @param ProcessHandlerInterface $handler
	 * @param EventDispatcher $dispatcher
	 * @param \DcaTools\Data\DriverManagerInterface $driverManager
	 */
	public function __construct(ModelInterface $model, Workflow $workflow, ProcessHandlerInterface $handler, EventDispatcher $dispatcher, $driverManager)
	{
		$this->model = new Model($model, $this);
		$this->handler = $handler;
		$this->workflow = $workflow;
		$this->dispatcher = $dispatcher;
		$this->driverManager = $driverManager;
	}


	/**
	 * Initialize the controller
	 */
	public function initialize()
	{
		$this->initializeServices();

		$state = $this->initializeModel();
		$event = new InitialisationEvent($this->model, $state);
		$eventName = sprintf('workflow.%s.initialized', $state->getProcessName());

		$this->dispatcher->dispatch($eventName, $event);
	}


	protected function initializeModel()
	{
		$state = $this->getCurrentState();

		if(!$state)
		{
			$state = $this->getProcessHandler()->start($this->model);
		}

		return $state;
	}


	protected function initializeServices()
	{
		$coreService = ServiceFactory::create('core', $this);
		$coreService->initialize();

		$this->workflow->addService($coreService);

		$driver = $this->getDriverManager()->getDataProvider('tl_workflow_service');
		$config = ConfigBuilder::create($driver)
			->filterEquals('pid', $this->workflow->getId())
			->sorting('sorting')
			->getConfig();

		foreach($driver->fetchAll($config) as $entity)
		{
			$service = ServiceFactory::create($entity, $this);
			$service->initialize();

			$this->workflow->addService($service);
		}
	}


	/**
	 * @param $stateName
	 *
	 * @return \Workflow\Entity\ModelState
	 */
	public function reachNextState($stateName)
	{
		$state = $this->getProcessHandler()->reachNextState($this->model, $stateName);

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
	 * @param ModelInterface $model
	 */
	public function setModel(ModelInterface $model)
	{
		$this->model = $model;
		$this->initializeModel();
	}


	/**
	 * @return \Workflow\Entity\Workflow
	 */
	public function getWorkflow()
	{
		return $this->workflow;
	}


	/**
	 * @return \DcaTools\Data\DriverManagerInterface
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
		return $this->handler;
	}


	/**
	 * @return EventDispatcher
	 */
	public function getEventDispatcher()
	{
		return $this->dispatcher;
	}

}
