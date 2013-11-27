<?php

namespace Workflow\Entity;

use DcaTools\Data\ConfigBuilder;


/**
 * Class WorkflowDataHelper supports handling for worflow data
 *
 * @package Workflow\Entity
 */
class WorkflowDataHelper
{

	/**
	 * Encode given data
	 *
	 * @param $data
	 * @return mixed
	 */
	public static function encode($data)
	{
		if(is_string($data)) {
			return $data;
		}
		elseif($GLOBALS['TL_CONFIG']['worflow_dataEncoding'] == 'php')
		{
			return serialize($data);
		}
		else
		{
			return json_encode($data);
		}
	}


	/**
	 * Decode given Data
	 *
	 * @param $data
	 * @return mixed
	 */
	public static function decode($data)
	{
		if(!is_string($data)) {
			return $data;
		}
		elseif($GLOBALS['TL_CONFIG']['worflow_dataEncoding'] == 'php')
		{
			return deserialize($data, true);
		}
		else
		{
			return json_decode($data, true);
		}
	}


	/**
	 * Restore given data
	 *
	 * @param $tableName
	 * @param $data
	 * @param string $idColumn
	 *
	 * @return \DcGeneral\Data\ModelInterface
	 */
	public static function restore($tableName, $data, $idColumn='id')
	{
		$data   = static::prepareData($tableName, $data);
		$driver = static::getDataProvider($tableName);
		$exists = (bool) ConfigBuilder::create($driver)->filterEquals('id', $data[$idColumn])->getCount();

		$model  = $driver->getEmptyModel();

		if($exists && isset($data[$idColumn])) {
			$model->setId($data[$idColumn]);
		}

		$model->setPropertiesAsArray($data);
		$driver->save($model);

		if(!$exists && isset($data[$idColumn])) {
			// FIXME: This limit the whole restore feature to only work with database files
			// FIXME: DefaultDriver does not allow to update the id column
			$result = \Database::getInstance()
				->prepare(sprintf('UPDATE %s %s WHERE id=?', $tableName, '%s'))
				->set(array('id' => $data[$idColumn]))
				->execute($model->getId());

			// have to clone it, otherwise ID changing is not possible
			$model = clone $model;
			$model->setId($data[$idColumn]);
		}

		return $model;
	}


	/**
	 * Remove all fields which does not exists
	 *
	 * @param $tableName
	 * @param array $data
	 * @return array
	 */
	public static function prepareData($tableName, $data)
	{
		if(is_string($data))
		{
			$data = static::decode($data);
		}

		unset($data['_children']);

		$driver = static::getDataProvider($tableName);

		// remove not existing properties
		foreach(array_keys($data) as $name)
		{
			if(!$driver->fieldExists($name))
			{
				unset($data[$name]);
			}
		}

		return $data;
	}


	/**
	 * @param $tableName
	 * @return \DcGeneral\Data\DriverInterface
	 */
	protected static function getDataProvider($tableName)
	{
		/** @var \DcaTools\Data\DriverManagerInterface $manager */
		$manager = $GLOBALS['container']['dcatools.driver-manager'];

		return $manager->getDataProvider($tableName);
	}

}
