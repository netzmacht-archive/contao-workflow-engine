<?php

namespace Workflow\Contao;

use DcaTools\Definition;
use Workflow\Controller\WorkflowManager;


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

		if($name == \Input::get('table') && WorkflowManager::getAvailableWorkflowsForDataContainer($name))
		{
			try {
				$definition    = Definition::getDataContainer($name);
				$dataContainer = $definition->get('config/dataContainer');

				if(isset($GLOBALS['TL_WORKFLOW_CONNECTORS'][$dataContainer]))
				{
					/** @var \Workflow\Contao\Connector\ConnectorInterface $class */
					$class = $GLOBALS['TL_WORKFLOW_CONNECTORS'][$dataContainer];
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
					break 2;
				}
			}
		}

		if(TL_MODE == 'BE')
		{
			// we have to load user first
			\BackendUser::getInstance();

			$result = \Database::getInstance()->execute('SELECT name FROM tl_workflow_process');

			foreach($result->fetchEach('name') as $name)
			{
				$GLOBALS['TL_PERMISSIONS'][] = 'workflow_' . $name;
			}
		}
	}
} 