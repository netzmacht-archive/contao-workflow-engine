<?php

namespace Workflow\Contao\Connector;

use DcaTools\Definition\DataContainer;
use Symfony\Component\EventDispatcher\EventDispatcher;

/**
 * Interface ConnectorInterface connects an DC driver to the workflow engine
 *
 * @author David Molineus <molineus@netzmacht.de>
 * @package Workflow\Contao\Connector
 */
interface ConnectorInterface
{

	/**
	 * Singleton
	 *
	 * @return ConnectorInterface
	 */
	public static function getInstance();


	/**
	 * Bootstrap the connector has to define method for registering the initialize method
	 *
	 * @param DataContainer $definition
	 * @param EventDispatcher $eventDispatcher
	 * @return mixed
	 */
	public static function bootstrap(DataContainer $definition, EventDispatcher $eventDispatcher);


	/**
	 * Initialize the connector
	 *
	 * Will be called by the onload_callback
	 *
	 * @param $dc
	 */
	public function initialize($dc);


	/**
	 * @return \Workflow\Controller\Controller
	 */
	public function getController();


	/**
	 * @param $stateName
	 * @param bool $redirect
	 * @return \Workflow\Entity\ModelState
	 */
	public function reachNextState($stateName, $redirect=false);

}
