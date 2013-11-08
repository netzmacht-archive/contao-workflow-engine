<?php
/**
 * Created by PhpStorm.
 * User: david
 * Date: 26.10.13
 * Time: 19:01
 */

namespace Workflow;

use DcaTools\Definition;
use DcGeneral\DC_General;
use Workflow\Controller\WorkflowFactory;
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
	 * @var bool
	 */
	protected $active = true;


	/**
	 * @var bool
	 */
	protected $initialized = false;


	/**
	 * @var bool[]
	 */
	protected $registered = array();


	/**
	 * @var \Workflow\Controller\Controller
	 */
	protected $controller;


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

			/** @var \Workflow\Data\DriverManager $manager */
			$manager = $GLOBALS['container']['workflow.driver-manager'];
			$driver  = $manager->getDataProvider($this->definition->getName());
			$config  = $driver->getEmptyConfig();
			$config->setId($this->id);

			try {
				$this->controller = WorkflowFactory::createController($driver->fetch($config));
			}
			catch(WorkflowException $e)
			{
				return;
			}


			try {
				$this->controller->initialize();
			}
			catch(WorkflowException $e)
			{
				$this->error($e->getMessage());
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
		try {
			$this->controller->reachNextState($stateName);
		}
		catch(WorkflowException $e) {
			$this->error($e->getMessage());
		}

		// redirect to referrer by default. If another target is required, the listener has to redirect
		\Controller::redirect(\Controller::getReferer());
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
