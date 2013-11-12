<?php
/**
 * Created by PhpStorm.
 * User: david
 * Date: 26.10.13
 * Time: 19:01
 */

namespace Workflow\Contao;

use DcaTools\Definition;
use DcGeneral\DC_General;
use Workflow\Controller\ControllerFactory;
use Workflow\Entity\ModelState;
use Workflow\Exception\WorkflowException;
use Workflow\Model\Model;

class Connector
{

	/**
	 * Store if parent view is used and not a single element is accessed
	 *
	 * @param bool
	 */
	protected $parentView;


	/**
	 * Used int id
	 *
	 * @param int
	 */
	protected $id;


	/**
	 * Current action
	 *
	 * @param string
	 */
	protected $action;


	/**
	 * DataContainer definition
	 *
	 * @var \DcaTools\Definition\DataContainer
	 */
	protected $definition;


	/**
	 * Initialisation state
	 *
	 * @var bool
	 */
	protected $initialized = false;


	/**
	 * Workflow controller
	 *
	 * @var \Workflow\Controller\Controller
	 */
	protected $controller;


	/**
	 * Track changes with save callback
	 *
	 * @var bool
	 */
	protected $reachedChanged = false;


	/**
	 * Cached process state names
	 *
	 * @var array
	 */
	protected $states;


	/**
	 * Construct
	 */
	public function __construct()
	{
		$this->action = \Input::get('key') == '' ? \Input::get('act') : \Input::get('key');

		$this->parentView = in_array($this->action, array('select', 'create'))
			|| ($this->action === null && \Input::get('table') != '' && $GLOBALS['TL_DCA'][\Input::get('table')]['config']['ptable'])
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
		return $GLOBALS['container']['workflow.connector'];
	}


	/**
	 * @param $name
	 */
	public function hookLoadDataContainer($name)
	{
		if(!in_array($name, $GLOBALS['TL_CONFIG']['workflow_disabledTables']))
		{
			try {
				$definition = Definition::getDataContainer($name);
				$definition->registerCallback('onload', array('Workflow\Contao\Connector', 'initialize'));
			}
			catch(\Exception $e) {
				\Controller::log($e->getMessage(), '\Workflow\Contao\Connector hookLoadDataContainer()', TL_ERROR);
			}
		}
	}


	/**
	 * Initialize the Workflow connector
	 *
	 * @param $dc
	 */
	public function initialize($dc)
	{
		if(!$this->initialized)
		{
			$this->initialized = true;

			if(!$this->initializeDefinition($dc))
			{
				return;
			}

			if(!$this->initializeController())
			{
				return;
			}

			$this->registerCallbacks();
		}
	}


	/**
	 * Initialize workflow controller
	 *
	 * @return bool if initialisation was successful
	 */
	protected function initializeController()
	{
		/** @var \DcaTools\Data\DriverManager $manager */
		$manager = $GLOBALS['container']['dcatools.driver-manager'];
		$driver  = $manager->getDataProvider($this->definition->getName());
		$config  = $driver->getEmptyConfig();

		$config->setId($this->id);
		$entity = $driver->fetch($config);

		if($entity)
		{
			try {
				$this->controller = ControllerFactory::create($entity);
			}
			catch(WorkflowException $e)
			{
				// no workflow found, do not show an error
				return false;
			}

			try {
				$this->controller->initialize();
				return true;
			}
			catch(WorkflowException $e)
			{
				$this->error($e->getMessage());
				return false;
			}
		}

		return false;
	}


	/**
	 * Initialize Definition of object
	 *
	 * @param \DC_Table $dc
	 *
	 * @return bool
	 */
	protected function initializeDefinition($dc)
	{
		if($dc instanceof DC_General)
		{
			// no need for own driver manager, use Dc_General
			$GLOBALS['container']['dcatools.driver-manager'] = $GLOBALS['container']->share(function() use($dc) {
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

			if(!$tableName)
			{
				return false;
			}

			$this->definition = Definition::getDataContainer($tableName);
		}

		return true;
	}


	/**
	 * Register callbacks
	 */
	protected function registerCallbacks()
	{
		$class = get_class($this);

		$this->definition->registerCallback('onsubmit', array($class, 'callbackOnSubmit'));
		$this->definition->registerCallback('oncreate', array($class, 'callbackOnCreate'));
		$this->definition->registerCallback('ondelete', array($class, 'callbackOnDelete'));

		foreach($this->definition->getProperties() as $property)
		{
			if($property->isEditable())
			{
				$property->registerCallback('save', array($class, 'callbackSave'));
			}
		}
	}


	/**
	 * Listener for get attribute key=workflow for handling workflow tasks
	 *
	 */
	public function workflow()
	{
		if(\Input::get('state'))
		{
			$this->reachNextState(\Input::get('state'), true);
		}
	}


	/**
	 * Reach next step
	 *
	 * @param string $stateName
	 * @param bool $redirect if true redirect to referer
	 */
	public function reachNextState($stateName, $redirect=false)
	{
		try {
			$this->controller->reachNextState($stateName);
		}
		catch(WorkflowException $e) {
			$this->error($e->getMessage());
		}

		// redirect to referrer by default. If another target is required, the listener has to redirect
		if($redirect)
		{
			\Controller::redirect(\Controller::getReferer());
		}
	}


	/**
	 * Save callback is used to track changes and try to reach the next step
	 *
	 * @param $value
	 *
	 * @return mixed
	 */
	public function callbackSave($value)
	{
		if(!$this->reachedChanged)
		{
			if($this->hasState('change'))
			{
				$this->reachNextState('change');
			}

			$this->reachedChanged = true;
		}

		return $value;
	}


	/**
	 * If next step is reached we have to update the workflow model data because DC_Table does not provide a
	 * callback for getting validated record before storing it
	 */
	public function callbackOnSubmit()
	{
		if($this->reachedChanged)
		{
			$state = $this->controller->getCurrentState();
			$state->setData($this->controller->getModel()->getWorkflowData());

			$driver = $this->controller->getDriverManager()->getDataProvider('tl_workflow_state');
			$driver->save($state);
		}
	}


	/**
	 * Initialize workflow after creating element
	 *
	 * @param $table
	 * @param $insertID
	 * @param $set
	 */
	public function callbackOnCreate($table, $insertID, $set)
	{
		$driver = $this->controller->getDriverManager()->getDataProvider($table);

		$entity = $driver->getEmptyModel();
		$entity->setPropertiesAsArray($set);
		$entity->setId($insertID);

		$model = new Model($entity, $this->controller);
		$this->controller->getProcessHandler()->start($model);
	}


	/**
	 * Trigger delete action if action is defined in process steps
	 */
	public function callbackOnDelete()
	{
		if($this->hasState('delete'))
		{
			$this->reachNextState('delete');
		}
	}


	/**
	 * Trigger an error will create log message and redirect to error page
	 *
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
	 * Add state errors as error messages
	 *
	 * @param ModelState $state
	 */
	public static function displayStateErrors(ModelState $state)
	{
		foreach($state->getErrors() as $error)
		{
			\Message::add($error, TL_ERROR);
		}
	}


	/**
	 * Check if a state action exists in process steps
	 *
	 * @param $name
	 * @return bool
	 */
	protected function hasState($name)
	{
		if(!$this->states)
		{
			$this->states = array();

			$steps = $this->controller->getProcessHandler()->getProcess()->getSteps();

			foreach($steps as $step)
			{
				foreach($step->getNextStates() as $state)
				{
					$this->states[] = $state->getName();
				}
			}
		}

		return in_array($name, $this->states);
	}

}
