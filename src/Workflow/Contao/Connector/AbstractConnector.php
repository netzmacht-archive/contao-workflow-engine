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
	 * @return bool
	 */
	public function initialize($dc)
	{
		$this->initializeParameters();
		$this->initializeDefinition($dc);

		return $this->initializeController($dc);
	}


	/**
	 * Initialize dc parameters
	 */
	protected function initializeParameters()
	{
		// TODO: Check if all actions are recognised
		$this->action = \Input::get('key') == '' ? \Input::get('act') : 'key_' . \Input::get('key');

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
	 * Initialize workflow controller
	 *
	 * @return bool if initialisation was successful
	 */
	protected function initializeController()
	{
		$this->controller = $GLOBALS['container']['workflow.controller'];
		$this->controller->setRequestAction($this->action);

		$entity = $this->initializeEntity();

		if($entity)
		{
			$state = $this->controller->initialize($entity, false);

			if($state)
			{
				if(!$state->getSuccessFul())
				{
					$this->error($state->getErrors());
				}

				return $state->getSuccessful();
			}
		}

		return false;
	}


	/**
	 * @return \DcGeneral\Data\ModelInterface|null
	 */
	abstract protected function initializeEntity();


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
	 * @param $message
	 * @param bool $redirect
	 */
	public static function error($message, $redirect=true)
	{
		\DcaTools\Controller::error($message, $redirect);
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
