<?php
/**
 * Created by PhpStorm.
 * User: david
 * Date: 29.10.13
 * Time: 23:32
 */

namespace Workflow\Dca;

use DcaTools\Definition;
use DcaTools\Model\FilterBuilder;
use DcGeneral\Data\DCGE;

class Workflow extends Generic
{

	/**
	 * @param array $row
	 * @return string
	 */
	public function callbackLabel(array $row)
	{
		$module = isset($GLOBALS['TL_LANG']['MOD'][$row['forModule']]) ? $GLOBALS['TL_LANG']['MOD'][$row['forModule']][0] : $row['forModule'];

		return sprintf('%s <span class="tl_class">%s, %s</span>', $row['title'], $module, $row['forTable']);
	}


	/**
	 * @param $dc
	 * @return array
	 */
	public function getModules($dc)
	{
		$modules = array();
		//$table = $dc->getEnvironment()->getCurrentModel()->getProperty('forTable');
		$table = $dc->activeRecord->forTable;

		foreach($GLOBALS['BE_MOD'] as $groupModules)
		{
			foreach($groupModules as $module => $config)
			{
				if(isset($config['tables']) && in_array($table, $config['tables']) && !in_array($module, $GLOBALS['TL_CONFIG']['workflow_disabledModules']))
				{
					$modules[] = $module;
				}
			}
		}

		return $modules;
	}


	public function getServices($dc)
	{
		global $container;

		/** @var \Workflow\Data\DriverManagerInterface $manager */
		$manager = $container['workflow.driver-manager'];
		$driver = $manager->getDataProvider('tl_workflow_service');

		$config = $driver->getEmptyConfig();
		$config->setFields(array('id', 'name', 'service'));
		$config->setSorting(array('name' => DCGE::MODEL_SORTING_ASC));

		$services = array();

		/** @var \DcGeneral\Data\ModelInterface $service */
		foreach($driver->fetchAll($config) as $service)
		{
			/** @var \Workflow\Service\ServiceInterface $serviceClass */
			$serviceClass = $GLOBALS['TL_WORKFLOW_SERVICES'][$service->getProperty('service')];

			if(class_exists($serviceClass))
			{
				$name = $serviceClass::getConfig()->getName();

				$services[$name][$service->getId()] = $service->getProperty('name');
			}
		}

		ksort($services);

		return $services;
	}

	/**
	 * @param $dc
	 * @return array
	 */
	public function getStorageProperties($dc)
	{
		$properties = array();
		$tables = array();
		$table = $dc->activeRecord->forTable;

		if($table)
		{
			$tables = array($table);
		}

		if($dc->activeRecord->store_children)
		{
			$children = Definition::getDataContainer($table)->get('config/ctable') ?: array();
			$tables = array_merge($tables, $children);
		}

		foreach($tables as $table)
		{
			$definition = Definition::getDataContainer($table);

			foreach($definition->getProperties() as $name => $property)
			{
				$properties[$table][specialchars($table .'::'. $name)] = $property->getLabel()[0] ?: $name;
			}
		}

		return $properties;
	}

} 