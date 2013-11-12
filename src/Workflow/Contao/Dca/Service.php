<?php

namespace Workflow\Contao\Dca;

use DcaTools\Definition;
use DcaTools\Model\FilterBuilder;
use DcaTools\Translator;


/**
 * Class Service provides callbacks used by tl_workflow_service
 *
 * @package Workflow\Contao\Dca
 */
class Service extends Generic
{

	/**
	 * @var \DcGeneral\Data\ModelInterface
	 */
	protected $entity;


	/**
	 * @var \Workflow\Service\Config
	 */
	protected $config;


	/**
	 * @var \DcGeneral\Data\ModelInterface
	 */
	protected $workflow;


	/**
	 * Reference to the used DC driver
	 *
	 * @var
	 */
	protected $dc;


	/**
	 * Initialize workflow service callbacks
	 * @param $dc
	 */
	public function callbackOnLoad($dc)
	{
		global $container;

		if(\Input::get('act') == '')
		{
			return;
		}

		$this->dc = $dc;

		/** @var \DcaTools\Data\DriverManagerInterface $manager */
		$manager = $container['dcatools.driver-manager'];
		$driver  = $manager->getDataProvider($dc->table);
		$config  = FilterBuilder::create()->addEquals('id', $dc->id)->getConfig($driver);
		
		$this->entity = $driver->fetch($config);

		if(!$this->entity)
		{
			return;
		}

		if($this->entity->getProperty('service'))
		{

			/** @var \Workflow\Service\ServiceInterface $serviceClass */
			$serviceClass = $GLOBALS['TL_WORKFLOW_SERVICES'][$this->entity->getProperty('service')];

			// initialize service configuration
			if(class_exists($serviceClass))
			{
				$this->config = $serviceClass::getConfig();
				$palette = Definition::getPalette($dc->table);

				foreach($this->config->getConfigProperties() as $legend => $properties)
				{
					foreach($properties as $property)
					{
						$palette->addProperty($property, $legend);
					}
				}
			}
		}

		// load workflow class
		$driver = $manager->getDataProvider('tl_workflow');

		$config = $driver->getEmptyConfig();
		$config->setId($this->entity->getProperty('pid'));

		$this->workflow = $driver->fetch($config);
	}


	/**
	 * Create default value for restrictions tables
	 *
	 * @param string $tableName
	 * @param $insertID
	 * @param $set
	 * @param $dc
	 */
	public function callbackOnCreate($tableName, $insertID, $set, $dc)
	{
		global $container;

		/** @var \DcaTools\Data\DriverManagerInterface $manager */
		$manager = $container['dcatools.driver-manager'];
		$driver  = $manager->getDataProvider('tl_workflow');

		$config = $driver->getEmptyConfig();
		$config->setFields(array('forTable'));
		$config->setId($set['pid']);

		$workflow = $driver->fetch($config);

		$tables = array($workflow->getProperty('forTable'));
		$children = Definition::getDataContainer($workflow->getProperty('forTable'))->get('config/ctable') ?: array();

		$tables =  array_merge($tables, $children);
		$default = array();

		foreach($tables as $table)
		{
			$default[]['table'] = $table;
		}

		$driver = $manager->getDataProvider($dc->table);
		$model  = $driver->getEmptyModel();

		$model->setId($insertID);
		$model->setProperty('restrictions', $default);

		$driver->save($model);
	}


	/**
	 * Get all available services
	 *
	 * @return array
	 */
	public function getServices()
	{
		$services = array();

		foreach($GLOBALS['TL_WORKFLOW_SERVICES'] as $name => $serviceClass)
		{
			/** @var \Workflow\Service\ServiceInterface $serviceClass */
			$configClass = $serviceClass::getConfig();

			$services[$name] = sprintf('%s (%s)', $configClass->getName(), $configClass->getIdentifier());
		}

		return $services;
	}


	/**
	 * Get events which the service supports
	 *
	 * @return array
	 */
	public function getEvents()
	{
		return $this->config->getEvents();
	}


	/**
	 * Get tables for restrictions
	 *
	 * @return array
	 */
	public function getRestrictTables()
	{
		$tables = array($this->workflow->getProperty('forTable'));
		$children = Definition::getDataContainer($this->workflow->getProperty('forTable'))->get('config/ctable') ?: array();

		return array_merge($tables, $children);
	}


	/**
	 * Get all operations for restriction tables
	 *
	 * @return array
	 */
	public function getRestrictOperations()
	{
		$operations = array();
		$tables = $this->getRestrictTables($this->dc);

		foreach($tables as $table)
		{
			$definition = Definition::getDataContainer($table);
			$translator = Translator::create($table);

			foreach($definition->getOperationNames('global') as $operation)
			{
				$operations[$table . ' (global)'][$table . '::global::' . $operation] = $translator->globalOperation($operation);
			}

			foreach($definition->getOperationNames() as $operation)
			{
				$operations[$table . ' (local)'][$table . '::local::' . $operation] = $translator->operation($operation);
			}
		}

		return $operations;
	}

}
