<?php

namespace Workflow\Contao\Connector;

use DcaTools\Definition;
use DcaTools\Definition\DataContainer;
use Symfony\Component\EventDispatcher\EventDispatcher;

// TODO Implement General connector after reworked DC_Geranal is published
trigger_error('GeneralConnector is not implemented yet', E_ERROR);


/**
 * Class GeneralConnector
 *
 * @author David Molineus <molineus@netzmacht.de>
 * @package Workflow\Contao\Connector
 */
class GeneralConnector extends AbstractConnector
{

	/**
	 * Bootstrap the connector has to define method for registering the initialize method
	 *
	 * @param DataContainer $definition
	 * @param EventDispatcher $eventDispatcher
	 * @return mixed
	 */
	public static function bootstrap(DataContainer $definition, EventDispatcher $eventDispatcher)
	{
		// TODO: Implement bootstrap() method.
	}


	/**
	 * Initialize Definition of object
	 *
	 * @param \DcGeneral\DC_General $dc
	 *
	 * @return bool
	 */
	protected function initializeDefinition($dc)
	{
		//$this->definition = Definition::getDataContainer($dc->getName());
	}


}
