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
		if(!in_array($name, $GLOBALS['TL_CONFIG']['workflow_disabledTables']))
		{
			$definition = Definition::getDataContainer($name);
			$definition->registerCallback('onload', array('Workflow\Workflow', 'initialize'));
		}
	}

}
