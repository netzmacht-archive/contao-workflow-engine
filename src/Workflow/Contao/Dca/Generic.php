<?php

namespace Workflow\Contao\Dca;

use DcaTools\Data\ConfigBuilder;
use DcaTools\Definition;
use DcGeneral\DC_General;


/**
 * Class Generic provides basic helpers for getting access
 *
 * @package Workflow\Contao\Dca
 * @author David Molineus <molineus@netzmacht.de>
 */
class Generic
{

	/**
	 * @var static
	 */
	protected static $instances;


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
	protected $entity;


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
		$className = get_called_class();

		if(!static::$instances[$className])
		{
			static::$instances[$className] = new static();
		}

		return static::$instances[$className];
	}


	/**
	 * @param $dc
	 */
	public function initialize($dc)
	{
		if($dc instanceof DC_General)
		{
			$this->manager = $dc;
			$table         = $dc->getTable();
			$id            = $dc->getId();
		}
		else
		{
			/** @var \DcaTools\Data\DriverManagerInterface $manager */
			$this->manager = $GLOBALS['container']['dcatools.driver-manager'];
			$table         = $dc->table;
			$id            = $dc->id;
		}

		$this->driver     = $this->manager->getDataProvider($table);
		$this->definition = Definition::getDataContainer($table);

		if($id)
		{
			$this->entity = ConfigBuilder::create($this->driver)->setId($id)->fetch();
		}
	}


	/**
	 * Get all users
	 * @return array
	 */
	public function getAllUsers()
	{
		$users   = array();
		$driver  = $this->manager->getDataProvider('tl_user');
		$builder = ConfigBuilder::create($driver)
			->setFields(array('id', 'username', 'name'))
			->sorting('name');

		/** @var \DcGeneral\Data\ModelInterface $user */
		foreach($builder->fetchAll() as $user)
		{
			$users[$user->getId()] = sprintf('%s [%s]', $user->getProperty('name'), $user->getProperty('username'));
		}

		return $users;
	}

}
