<?php

namespace Workflow\Controller\Deprecated;


use DcaTools\Data\ConfigBuilder;
use DcGeneral\Data\DCGE;
use DcGeneral\Data\ModelInterface;
use Symfony\Component\EventDispatcher\EventDispatcher;
use Workflow\Handler\ProcessHandler;
use Workflow\Handler\ProcessHandlerInterface;
use Workflow\Service\ServiceFactory;


/**
 * Class Controller allow to run workflow tasks for an assigned Model and provides access to several objects
 *
 * @package Workflow\Controller
 */
class Controller
{

	/**
	 * @var \Symfony\Component\EventDispatcher\EventDispatcher
	 */
	protected $dispatcher;


	/**
	 * @var \DcaTools\Data\DriverManagerInterface
	 */
	protected $driverManager;


	/**
	 * @var ModelInterface
	 */
	protected $workflow;


	/**
	 * @var \Workflow\Handler\ProcessHandlerInterface
	 */
	protected $handler;


	/**
	 * @var \DcGeneral\Data\CollectionInterface|\DcGeneral\Data\ModelInterface[]
	 */
	protected $workflows = array();



	/**
	 * @param ModelInterface $workflow
	 * @param ProcessHandlerInterface $handler
	 * @param EventDispatcher $dispatcher
	 * @param \DcaTools\Data\DriverManagerInterface $driverManager
	 */
	public function __construct(ModelInterface $workflow, ProcessHandlerInterface $handler, EventDispatcher $dispatcher, $driverManager)
	{
		$this->workflow      = $workflow;
		$this->handler       = $handler;
		$this->dispatcher    = $dispatcher;
		$this->driverManager = $driverManager;
	}


	/**
	 * Initialize the controller
	 */
	public function initialize()
	{
		// initialize registered services
		$coreService = ServiceFactory::create('core', $this);
		$coreService->initialize();

		$driver  = $this->getDriverManager()->getDataProvider('tl_workflow_service');
		$builder = ConfigBuilder::create($driver)
			->filterEquals('pid', $this->workflow->getId())
			->sorting('sorting');

		foreach($builder->fetchAll() as $entity)
		{
			$service = ServiceFactory::create($entity, $this);
			$service->initialize();
		}
	}


	/**
	 * @param \Workflow\Model\ModelInterface $model
	 * @return \Workflow\Entity\ModelState
	 */
	public function initializeModel(\Workflow\Model\ModelInterface $model)
	{
		$state = $this->getCurrentState($model);

		if(!$state)
		{
			$state = $this->getProcessHandler()->start($model);
		}

		return $state;
	}


	/**
	 * @param \Workflow\Model\ModelInterface $model
	 * @param $stateName
	 *
	 * @return \Workflow\Entity\ModelState
	 */
	public function reachNextState(\Workflow\Model\ModelInterface $model, $stateName)
	{
		$state = $this->getProcessHandler()->reachNextState($model, $stateName);

		if($state->getSuccessful())
		{
			if($state->getMeta(DCGE::MODEL_IS_CHANGED))
			{
				$driver = $this->driverManager->getDataProvider($state->getProviderName());
				$driver->save($state);
			}

			if($model->getEntity()->getMeta(DCGE::MODEL_IS_CHANGED))
			{
				$driver = $this->driverManager->getDataProvider($model->getEntity()->getProviderName());
				$driver->save($model->getEntity());
			}
		}

		return $state;
	}


	/**
	 * @param \Workflow\Model\ModelInterface $model
	 *
	 * @return \Workflow\Entity\ModelState
	 */
	public function getCurrentState(\Workflow\Model\ModelInterface $model)
	{
		return $this->getProcessHandler()->getCurrentState($model);
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
