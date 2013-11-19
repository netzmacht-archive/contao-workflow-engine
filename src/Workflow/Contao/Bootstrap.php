<?php

namespace Workflow\Contao;

use DcaTools\Definition;


/**
 * Class Bootstrap
 * @package Workflow\Contao
 * @author David Molineus <molineus@netzmacht.de>
 */
class Bootstrap
{

	/**
	 * @param $name
	 */
	public function hookLoadDataContainer($name)
	{
		/** @var \Pimple $container */
		global $container;

		if($name == \Input::get('table') &&
			!in_array(\Input::get('do'), $GLOBALS['TL_CONFIG']['workflow_disabledModules']) &&
			!in_array($name, $GLOBALS['TL_CONFIG']['workflow_disabledTables'])
		) {
			try {
				$definition = Definition::getDataContainer($name);
				$dataContainer = $definition->get('config/dataContainer');

				// TODO: Should we allow other driver as well?
				if($dataContainer == 'Table' || $dataContainer == 'General')
				{
					/** @var \Workflow\Contao\Connector\ConnectorInterface $class */
					$class = sprintf('Workflow\Contao\Connector\%sConnector', $dataContainer);
					$class::bootstrap($definition, $container['event-dispatcher']);

					$GLOBALS['container']['workflow.connector'] = $container->share(function () use($class) {
						return new $class;
					});
				}
			}
			catch(\Exception $e) {
				\Controller::log($e->getMessage(), '\Workflow\Contao\Connector hookLoadDataContainer()', TL_ERROR);
			}
		}
	}


	/**
	 * We have to assign workflow module for every module which is not disabled because it's not possible to do this
	 * before Contao 3.2 in hookLoadDataContainer
	 *
	 * @see https://github.com/contao/core/issues/5915
	 */
	public function hookInitializeSystem()
	{
		foreach($GLOBALS['BE_MOD'] as $group => $modules)
		{
			foreach($modules as $module => $config)
			{
				if($module == \Input::get('do'))
				{
					$GLOBALS['BE_MOD'][$group][$module]['workflow'] = array('Workflow\Contao\WorkflowModule', 'execute');
					return;
				}
			}
		}
	}
} 