<?php
/**
 * Created by PhpStorm.
 * User: david
 * Date: 04.11.13
 * Time: 14:04
 */

namespace Workflow\Contao\Dca;


use DcaTools\Definition;
use DcaTools\Model\FilterBuilder;
use DcaTools\Translator;
use DcGeneral\Data\DCGE;

class Service extends Generic
{
	protected $model;

	/**
	 * @var \Workflow\Service\ConfigInterface
	 */
	protected $config;


	protected $workflow;

	protected $tableIndex = 0;

	protected $tableValue;

	protected $dc;


	public function initialize($dc)
	{
		global $container;

		if(\Input::get('act') == '')
		{
			return;
		}

		$this->dc = $dc;

		/** @var \Workflow\Data\DriverManagerInterface $manager */
		$manager = $container['workflow.driver-manager'];
		$driver = $manager->getDataProvider($dc->table);

		$config = FilterBuilder::create()->addEquals('id', $dc->id)->getConfig($driver);
		$this->model = $driver->fetch($config);

		if($this->model && $this->model->getProperty('service'))
		{
			/** @var \Workflow\Service\ServiceInterface $serviceClass */
			$serviceClass = $GLOBALS['TL_WORKFLOW_SERVICES'][$this->model->getProperty('service')];

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


		$driver = $manager->getDataProvider('tl_workflow');
		$model  = $driver->getEmptyModel();


		$result = \Database::getInstance()
			->prepare('SELECT * FROM tl_workflow where id=(SELECT pid FROM tl_workflow_service WHERE id=?)')
			->limit(1)
			->execute($dc->id);

		$model->setPropertiesAsArray($result->row());
		$model->setId($result->id);

		$this->workflow = $model;
	}

	public function getUsers()
	{
		global $container;

		/** @var \Workflow\Data\DriverManagerInterface $manager */
		$manager = $container['workflow.driver-manager'];
		$driver = $manager->getDataProvider('tl_user');

		$config = $driver->getEmptyConfig();
		$config->setFields(array('id', 'username', 'name'));
		$config->setSorting(array('name' => DCGE::MODEL_SORTING_ASC));

		$users = array();

		/** @var \DcGeneral\Data\ModelInterface $user */
		foreach($driver->fetchAll($config) as $user)
		{
			$users[$user->getId()] = sprintf('%s [%s]', $user->getProperty('name'), $user->getProperty('username'));
		}

		return $users;
	}

	public function getServices()
	{
		$services = array();

		foreach($GLOBALS['TL_WORKFLOW_SERVICES'] as $name => $serviceClass)
		{
			/** @var \Workflow\Service\ServiceInterface $serviceClass */
			$configClass = $serviceClass::getConfig();

			$services[$name] = sprintf('%s (%s)', $configClass->getName(), $configClass->getVersion());
		}

		return $services;
	}


	public function getEvents()
	{
		$serviceConfig = $this->config;

		return $serviceConfig->getEvents();
	}


	public function getRestrictTables($dc)
	{
		$tables = array($this->workflow->getProperty('forTable'));
		$children = Definition::getDataContainer($this->workflow->getProperty('forTable'))->get('config/ctable') ?: array();

		return array_merge($tables, $children);
	}


	public function getRestrictOperations()
	{
		$operations = array();
		$tables = deserialize($this->dc->activeRecord->restrict_tables, true);

		foreach($tables as $table)
		{
			$definition = Definition::getDataContainer($table);
			$translator = Translator::instantiate($table);

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