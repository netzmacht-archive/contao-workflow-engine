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

		if(!in_array(\Input::get('do'), $GLOBALS['TL_CONFIG']['workflow_disabledModules']) &&
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
} 