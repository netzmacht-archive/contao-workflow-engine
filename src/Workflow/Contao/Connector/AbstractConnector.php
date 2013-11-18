<?php

namespace Workflow\Contao\Connector;

use Workflow\Entity\ModelState;
use Workflow\Exception\WorkflowException;

abstract class AbstractConnector implements ConnectorInterface
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
	 * @var \Workflow\Controller\Controller
	 */
	protected $controller;


	/**
	 * Singleton pattern is used so that Contao will use the dependency injection
	 *
	 * @return ConnectorInterface
	 */
	public static function getInstance()
	{
		return $GLOBALS['container']['workflow.connector'];
	}


	/**
	 * Initialize the connector
	 *
	 * Will be called by the onload_callback
	 *
	 * @param $dc
	 */
	public function initialize($dc)
	{
		$this->initializeParameters();
		$this->initializeDefinition($dc);
	}


	/**
	 * Initialize dc parameters
	 */
	protected function initializeParameters()
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
	 * Initialize definition
	 *
	 * @param $dc
	 */
	abstract protected function initializeDefinition($dc);


	/**
	 * Route workflow tasks to the workflow process
	 *
	 * Listens to key=workflow
	 *
	 * @return mixed
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

}
