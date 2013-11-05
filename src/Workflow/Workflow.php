<?php
/**
 * Created by PhpStorm.
 * User: david
 * Date: 26.10.13
 * Time: 19:01
 */

namespace Workflow;

use DcaTools\Definition;
use DcaTools\Model\FilterBuilder;
use DcGeneral\DC_General;
use Workflow\Event\InitialisationEvent;
use Workflow\Handler\EnvironmentFactory;
use Workflow\Model\Model;
use Workflow\Entity\ModelState;
use Workflow\Exception\WorkflowException;

class Workflow
{

	/**
	 * store if parent view is used and not a single element is accessed
	 *
	 * @param bool
	 */
	protected $parentView;


	/**
	 * used int id
	 * @param int
	 */
	protected $id;


	/**
	 * current action
	 * @param string
	 */
	protected $action;


	/**
	 * @var \DcaTools\Definition\DataContainer
	 */
	protected $definition;


	/**
	 * @var \Workflow\Entity\ModelState
	 */
	protected $state;


	/**
	 * @var bool
	 */
	protected $active = true;


	/**
	 * @var bool
	 */
	protected $initialized = false;


	/**
	 * @var \Workflow\Handler\Environment
	 */
	protected $environment;


	/**
	 * @var bool[]
	 */
	protected $registered = array();


	/**
	 * Construct
	 */
	public function __construct()
	{
		$this->action = \Input::get('key') == '' ? \Input::get('act') : \Input::get('key');

		$this->parentView = in_array($this->action, array(null, 'select', 'create'))
			|| ($this->action == 'paste' && \Input::get('mode') == 'create');

		if(\Input::get('tid') != null)
		{
			$this->action = 'toggle';
			$this->id = \Input::get('tid');
		}
		else
		{
			$this->id = \Input::get('id');
		}
	}


	/**
	 * Singleton pattern used for Contao compatibility, calls dependency injection
	 *
	 * @return $this
	 */
	public static function getInstance()
	{
		return $GLOBALS['container']['workflow'];
	}


	/**
	 * Initialize Workflow will be triggered
	 * @param $dc
	 */
	public function initialize($dc)
	{
		if(!$this->initialized)
		{
			$this->initialized = true;
			$this->initializeDefinition($dc);

			if($this->initializeEnvironment())
			{
				$this->initializeState();
			}
		}
	}


	/**
	 * Initialize Definition of object
	 *
	 * @param \DC_Table $dc
	 */
	protected function initializeDefinition($dc)
	{
		if($dc instanceof DC_General)
		{
			// no need for own driver manager, use Dc_General
			$GLOBALS['container']['workflow.driver-manager'] = $GLOBALS['container']->share(function() use($dc) {
				return $dc;
			});

			$tableName = $dc->getName();
		}
		else {
			$tableName = $dc->table;
		}

		$this->definition = Definition::getDataContainer($tableName);

		if($this->parentView)
		{
			$tableName = $this->definition->getFromDefinition('config/ptable');
			$this->definition = Definition::getDataContainer($tableName);
		}
	}


	/**
	 * Initialize the workflow controller
	 *
	 * @return bool
	 */
	protected function initializeEnvironment()
	{
		global $container;

		/** @var \Workflow\Data\DriverManagerInterface $driverManager */
		$driverManager = $container['workflow.driver-manager'];
		$workflowDriver = $driverManager->getDataProvider('tl_workflow');

		$config = FilterBuilder::create()
			->addEquals('forTable', $this->definition->getName())
			->addEquals('forModule', \Input::get('do'))
			->getConfig($workflowDriver);

		$workflow = $workflowDriver->fetch($config);

		// there is no workflow defined
		if($workflow == null)
		{
			return false;
		}

		// get current model
		$dataProvider = $driverManager->getDataProvider($this->definition->getName());

		$config = $dataProvider->getEmptyConfig();
		$config->setId($this->id);

		$model = new Model($dataProvider->fetch($config), $this->environment->getEventDispatcher());

		try {
			$this->environment = EnvironmentFactory::create($model, $workflow);
		}
		catch(WorkflowException $e) {
			$this->error($e->getMessage());

			return false;
		}

		// register event listener and subscriber
		$this->initializeServices($this->environment);

		return true;
	}


	/**
	 * initialize current state
	 */
	protected function initializeState()
	{
		$model = $this->environment->getCurrentModel();
		$state = $this->environment->getCurrentState();

		if($state === null)
		{
			$state = $this->environment->getCurrentProcessHandler()->start($model);
		}

		$event = new InitialisationEvent($model, $state);
		$eventName = sprintf('workflow.%s.%s.initialize', $this->definition->getName(), $this->state->getProcessName());

		$this->environment->getEventDispatcher()->dispatch($eventName, $event);
	}


	/**
	 * Listener for get attribute key=workflow for handling workflow tasks
	 *
	 */
	public function workflow()
	{
		if(\Input::get('state'))
		{
			$this->reachNextState(\Input::get('state'));
		}
	}


	/**
	 * @param $stateName
	 */
	public function reachNextState($stateName)
	{
		$model = $this->environment->getCurrentModel();
		$handler = $this->environment->getCurrentProcessHandler();

		if($handler->isProcessComplete($model))
		{
			$this->error(sprintf('Can not reach next step. Process "%s" is already completed', $this->state->getProcessName()));
		}

		try {
			$state = $handler->reachNextState($model, $stateName);

			if(!$state->getSuccessful())
			{
				$this->displayStateErrors($state);
			}
		}
		catch(WorkflowException $e) {
			$this->error($e->getMessage());
		}

		// redirect to referrer by default. If another target is required, the listener has to redirect
		\Controller::redirect(\Controller::getReferer());
	}


	/**
	 * Register all events of a given data container
	 *
	 * @param \Workflow\Handler\Environment $environment
	 */
	public function initializeServices($environment)
	{
		$workflow = $environment->getCurrentWorkflow();

		if(isset($this->registered[$workflow->getId()]))
		{
			return;
		}

		$services = deserialize($workflow->getProperty('services'), true);
		$ids = array();

		foreach($services as $service)
		{
			if(!$service['disabled'])
			{
				$ids[] = $service['service'];
			}
		}

		$driver = $this->environment->getDriverManager()->getDataProvider('tl_workflow_service');
		$config = FilterBuilder::create()->addIn('id', $ids)->getConfig($driver);

		/** @var \DcGeneral\Data\ModelInterface $serviceModel */
		foreach($driver->fetchAll($config) as $serviceModel)
		{
			$namespace = $GLOBALS['TL_WORKFLOW_SERVICES'][$serviceModel->getProperty('service')];
			$serviceClass = $namespace . '\Service';

			/** @var \Workflow\Service\ServiceInterface $service */
			$service = new $serviceClass($serviceModel, $environment);
			$service->initialize();
		}

		$this->environment->getEventDispatcher()->addListener('workflow.check_credentials', array('Workflow\Process\CheckCredentials', 'execute'));
		$this->registered[$workflow->getId()] = true;
	}


	/**
	 * @param $strMessage
	 */
	public static function error($strMessage)
	{
		$arrDebug = debug_backtrace();
		$strCall = $arrDebug[1]['class'] . ' ' .$arrDebug[1]['function'];

		\Controller::log($strMessage, $strCall, 'TL_ERROR');
		\Controller::redirect('contao/main.php?act=error');
	}


	/**
	 * @param ModelState $state
	 */
	public static function displayStateErrors(ModelState $state)
	{
		foreach($state->getErrors() as $error)
		{
			\Message::add($error, TL_ERROR);
		}
	}

}
