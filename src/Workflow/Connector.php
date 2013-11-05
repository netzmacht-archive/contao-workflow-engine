<?php


namespace Workflow;


use DcaTools\Definition;

class Connector
{
	/**
	 * @param $name
	 */
	public function hookLoadDataContainer($name)
	{
		$definition = Definition::getDataContainer($name);
		$definition->registerCallback('onload', array('Workflow\Workflow', 'initialize'));
	}

}
