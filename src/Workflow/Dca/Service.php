<?php
/**
 * Created by PhpStorm.
 * User: david
 * Date: 04.11.13
 * Time: 14:04
 */

namespace Workflow\Dca;


use DcaTools\Definition;
use DcaTools\Model\FilterBuilder;
use DcGeneral\Data\DCGE;

class Service
{
	protected $model;

	/**
	 * @var \Workflow\Service\ConfigInterface
	 */
	protected $config;

	protected static $instance;


	public static function getInstance()
	{
		if(!static::$instance)
		{
			static::$instance = new static;
		}

		return static::$instance;
	}


	public function initialize($dc)
	{
		global $container;

		if(\Input::get('act') == '')
		{
			return;
		}

		/** @var \Workflow\Data\DriverManagerInterface $manager */
		$manager = $container['workflow.driver-manager'];
		$driver = $manager->getDataProvider($dc->table);

		$config = FilterBuilder::create()->addEquals('id', $dc->id)->getConfig($driver);
		$this->model = $driver->fetch($config);

		if($this->model && $this->model->getProperty('service'))
		{
			/** @var \Workflow\Service\ServiceInterface $serviceClass */
			$serviceClass = $GLOBALS['TL_WORKFLOW_SERVICES'][$this->model->getProperty('service')];
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

		return $serviceConfig::getEvents();
	}

} 