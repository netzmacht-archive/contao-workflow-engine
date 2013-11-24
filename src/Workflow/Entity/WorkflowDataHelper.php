<?php

namespace Workflow\Entity;


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
		if($GLOBALS['TL_CONFIG']['worflow_dataEncoding'] == 'php')
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
		if($GLOBALS['TL_CONFIG']['worflow_dataEncoding'] == 'php')
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

		$model  = $driver->getEmptyModel();
		$model->setPropertiesAsArray($data);

		if(isset($data[$idColumn]))
		{
			$model->setId($data[$idColumn]);
		}

		$driver->save($model);

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
