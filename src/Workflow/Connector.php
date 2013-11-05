<?php
/**
 * Created by PhpStorm.
 * User: david
 * Date: 30.10.13
 * Time: 12:12
 */

namespace Workflow\Contao;


use DcaTools\Definition;

class Connector
{

	/**
	 *
	 */
	public function __construct()
	{
	}


	/**
	 * @param $name
	 */
	public function hookLoadDataContainer($name)
	{
		/** @var \Workflow\Registry $registry */
		$registry = $GLOBALS['container']['workflow.registry'];
		$definition = Definition::getDataContainer($name);

		$config = $registry->getConfig();
		$module = \Input::get('do');

		if($config->hasDataProvider($module, $name))
		{
			$provider = $config->getDataProvider($module, $name);
			$definition->set('workflow/process', $provider->getProcessName());
		}

		// initialize workflow
		if($definition->get('workflow/process') != '')
		{
			$definition->registerCallback('onload', array('Workflow\Workflow', 'initialize'));
		}
	}
}
