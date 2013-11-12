<?php
/**
 * Created by PhpStorm.
 * User: david
 * Date: 05.11.13
 * Time: 10:40
 */

namespace Workflow\Contao\Dca;

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
	protected function initializeDriver($dc)
	{
		if($dc instanceof DC_General)
		{
			$this->driver  = $dc->getDataProvider($dc->getTable());
			$this->manager = $dc;
		}
		else
		{
			/** @var \DcaTools\Data\DriverManagerInterface $manager */
			$manager = $GLOBALS['container']['dcatools.driver-manager'];

			$this->manager = $manager;
			$this->driver  = $manager->getDataProvider($dc->table);
		}
	}


	public function getTables($dc)
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