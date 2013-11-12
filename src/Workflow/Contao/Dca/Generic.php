<?php
/**
 * Created by PhpStorm.
 * User: david
 * Date: 05.11.13
 * Time: 10:40
 */

namespace Workflow\Contao\Dca;

use DcaTools\Data\ConfigBuilder;
use DcaTools\Definition;
use DcGeneral\Data\DCGE;
use DcGeneral\DC_General;


class Generic
{

	/**
	 * @var static
	 */
	protected static $instance;


	/**
	 * @var \DcGeneral\Data\DriverInterface
	 */
	protected $driver;


	/**
	 * @var \DcaTools\Data\DriverManagerInterface
	 */
	protected $manager;


	/**
	 * @var \DcGeneral\Data\ModelInterface
	 */
	protected $model;


	/**
	 * @var \DcaTools\Definition\DataContainer
	 */
	protected $definition;


	/**
	 * Singleton
	 * @return static
	 */
	public static function getInstance()
	{
		if(!static::$instance)
		{
			static::$instance = new static;
		}

		return static::$instance;
	}


	/**
	 * @param $dc
	 */
	public function initialize($dc)
	{
		if($dc instanceof DC_General)
		{
			$this->manager = $dc;

			$table = $dc->getTable();
			$id    = $dc->getId();
		}
		else
		{
			/** @var \DcaTools\Data\DriverManagerInterface $manager */
			$manager = $GLOBALS['container']['dcatools.driver-manager'];
			$this->manager = $manager;

			$table = $dc->table;
			$id    = $dc->id;
		}

		$this->driver     = $this->manager->getDataProvider($table);
		$this->definition = Definition::getDataContainer($table);
		$this->model      = ConfigBuilder::create($this->driver)->setId($id)->fetch();
	}


	/**
	 * @return array
	 */
	public function getTables()
	{
		// TODO: support non database data containers
		return array_values(array_diff(\Database::getInstance()->listTables(), $GLOBALS['TL_CONFIG']['workflow_disabledTables']));
	}


	/**
	 * Get all users
	 * @return array
	 */
	public function getUsers()
	{
		global $container;

		/** @var \DcaTools\Data\DriverManagerInterface $manager */
		$manager = $container['dcatools.driver-manager'];
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

} 