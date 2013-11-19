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


	public function execute($dc)
	{
		//$this->connector->initialize($dc);
		//var_dump('module');
		//var_dump($this->connector);
		$this->connector->workflow();
	}

} 