<?php
/**
 * Created by PhpStorm.
 * User: david
 * Date: 19.11.13
 * Time: 09:07
 */

namespace Workflow\Contao;


class WorkflowModule
{

	/**
	 * @var \Workflow\Contao\Connector\ConnectorInterface
	 */
	protected $connector;


	/**
	 * Construct
	 */
	public function __construct()
	{
		$this->connector = $GLOBALS['container']['workflow.connector'];
	}


	/**
	 * Execute workflow module
	 *
	 * Workflow module listens to key=workflow. Supported actions
	 *  - state: reach next state
	 */
	public function execute()
	{
		if(\Input::get('state'))
		{
			$this->connector->reachNextState(\Input::get('state'), true, true);
		}
	}

} 