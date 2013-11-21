<?php

namespace Workflow\Contao\Dca;

use DcaTools\Data\ConfigBuilder;
use DcaTools\Definition;
use DcaTools\DynamicParent;
use DcaTools\Translator;
use Workflow\Handler\ProcessFactory;


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
	 * @var \Workflow\Controller\WorkflowInterface
	 */
	protected $workflow;


	/**
	 * @var \Workflow\Flow\Process[]
	 */
	protected $processes = array();


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
	public function initialize($dc)
	{
		parent::initialize($dc);

		if(\Input::get('act') == '' || !$this->entity)
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

				foreach($this->config['config'] as $legend => $properties)
				{
					foreach($properties as $property)
					{
						$palette->addProperty($property, $legend);
					}
				}
			}
		}

		// load workflow class
		$driver = $this->manager->getDataProvider('tl_workflow');

		$this->workflow = ConfigBuilder::create($driver)
			->setId($this->entity->getProperty('pid'))
			->fetch();


		if($this->workflow)
		{
			/** @var \Workflow\Controller\WorkflowManager $workflowManager */
			$workflowManager = $GLOBALS['container']['workflow.workflow-manager'];
			$this->workflow = $workflowManager->createInstance($this->workflow);
		}
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
			$config = $serviceClass::getConfig();

			$services[$name] = sprintf('%s (%s)', $GLOBALS['TL_LANG']['workflow']['services'][$config[$name]][0], $config['name']);
		}

		return $services;
	}


	/**
	 * Get all steps of current workflow
	 *
	 * @return array
	 */
	public function getSteps()
	{
		$processes = $this->workflow->getProcessConfiguration();
		$table     = $this->entity->getProperty('tableName');
		$steps     = array();

		if($processes[$table])
		{
			$process = $this->getProcess($processes[$table]);

			foreach($process->getSteps() as $step)
			{
				$steps[] = $step->getName();
			}
		}

		return $steps;
	}


	/**
	 * Get all defined tables of the workflow
	 * @return array
	 */
	public function getTables()
	{
		$processes = deserialize($this->workflow->getEntity()->getProperty('processes'), true);
		$tables    = array();

		foreach($processes as $config)
		{
			$tables[] = $config['table'];
		}

		return $tables;
	}


	/**
	 * @return array
	 */
	public function getAllStates()
	{
		$processes = $this->workflow->getProcessConfiguration();
		$table     = $this->entity->getProperty('tableName');
		$config    = $this->workflow->getConfig($table);
		$states    = array();

		if($processes[$table])
		{
			$process = $this->getProcess($processes[$config['parent']]);

			foreach($process->getSteps() as $step)
			{
				foreach($step->getNextStates() as $state)
				{
					$states[] = $state->getName();
				}
			}
		}

		$states = array_unique($states);
		sort($states);

		return $states;
	}


	public function getOperations()
	{
		$operations = array();

		if($this->entity->getProperty('tableName'))
		{
			$definition = Definition::getDataContainer($this->entity->getProperty('tableName'));

			foreach($definition->getOperations() as $operation)
			{
				$label = $operation->getLabel();
				$operations[$operation->getName()] = $label[0];
			}
		}

		return $operations;
	}


	/**
	 * @return array
	 */
	public function getGlobalOperations()
	{
		$operations = array();

		if($this->entity->getProperty('tableName'))
		{
			$definition = Definition::getDataContainer($this->entity->getProperty('tableName'));

			foreach($definition->getOperations('global') as $operation)
			{
				$label = $operation->getLabel();
				$operations[$operation->getName()] = $label[0] ?: $operation->getName();
			}
		}

		return $operations;
	}


	/**
	 * @param $process
	 * @return \Workflow\Flow\Process
	 */
	protected function getProcess($process)
	{
		if(!isset($this->processes[$process]))
		{
			$this->processes[$process] = ProcessFactory::create($process);
		}

		return $this->processes[$process];
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
		return;
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
	 * Get events which the service supports
	 *
	 * @return array
	 */
	public function getEvents()
	{
		return $this->config->getEvents();
	}


	public function getReferenceTables()
	{
		if($this->entity)
		{
			$table = ConfigBuilder::create($this->manager->getDataProvider('tl_workflow'))
				->field('tableName')
				->filterEquals('id', $this->entity->getProperty('pid'))
				->fetch()
				->getProperty('tableName');
		}

		if(!$table)
		{
			return array();
		}

		$definition = Definition::getDataContainer($table);
		$parents    = array($table);

		while($definition->get('config/ptable') !== null)
		{
			$table = DynamicParent::getDynamicParent($definition->getName(), $this->model->getProperty('limitModule'));

			if(!$table)
			{
				break;
			}

			$definition = Definition::getDataContainer($table);
			$parents[] = $table;
		}

		return $parents;
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
			if(!$table)
			{
				continue;
			}

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


	public function generateChildRecord($row)
	{
		return sprintf('%s <span class="tl_gray">[%s: %s]</span>', $row['name'], $row['tableName'], $row['service']);
	}

}
