<?php

namespace WorkflowEngine\Entity;

use DcGeneral\Data\DCGE;
use DcGeneral\Data\ModelInterface;

/**
 * Class Entity is a wrapper for models using DcGeneral\Data\ModelInterface. The wrapper is required so that the
 * WorkflowEngine will stay independent on the used model class
 *
 * @package WorkflowEngine\Entity
 */
class Entity implements ModelInterface
{

	/**
	 * @var \DcGeneral\Data\ModelInterface
	 */
	private $model;


	/**
	 * @param ModelInterface $model
	 */
	public function __construct(ModelInterface $model)
	{
		$this->model = $model;
	}


	/**
	 * Get the id for this model.
	 *
	 * @return mixed The Id for this model.
	 */
	public function getId()
	{
		return $this->model->getId();
	}


	/**
	 * (PHP 5 &gt;= 5.0.0)<br/>
	 * Retrieve an external iterator
	 * @link http://php.net/manual/en/iteratoraggregate.getiterator.php
	 * @return \Traversable An instance of an object implementing <b>Iterator</b> or
	 * <b>Traversable</b>
	 */
	public function getIterator()
	{
		return $this->model->getIterator();
	}


	/**
	 * Fetch the property with the given name from the model.
	 *
	 * This method returns null if an unknown property is retrieved.
	 *
	 * @param string $strPropertyName The property name to be retrieved.
	 *
	 * @return mixed The value of the given property.
	 */
	public function getProperty($strPropertyName)
	{
		return $this->model->getProperty($strPropertyName);
	}


	/**
	 * Fetch all properties from the model as an name => value array.
	 *
	 * @return array
	 */
	public function getPropertiesAsArray()
	{
		return $this->model->getPropertiesAsArray();
	}


	/**
	 * Fetch meta information from model.
	 *
	 * @param string $strMetaName The meta information to retrieve.
	 *
	 * @return mixed The set meta information or null if undefined.
	 */
	public function getMeta($strMetaName)
	{
		return $this->model->getMeta($strMetaName);
	}


	/**
	 * Set the id for this object.
	 *
	 * NOTE: when the Id has been set once to a non null value, it can NOT be changed anymore.
	 *
	 * Normally this should only be called from inside of the implementing provider.
	 *
	 * @param mixed $mixId Could be a integer, string or anything else - depends on the provider implementation.
	 *
	 * @return void
	 */
	public function setId($mixId)
	{
		if($this->getId() != $mixId)
		{
			$this->setMeta(DCGE::MODEL_IS_CHANGED, true);
		}

		$this->setId($mixId);
	}


	/**
	 * Update the property value in the model.
	 *
	 * @param string $strPropertyName
	 *
	 * @param mixed $varValue
	 *
	 * @return void
	 */
	public function setProperty($strPropertyName, $varValue)
	{
		if($this->getProperty($strPropertyName) != $varValue)
		{
			$this->setMeta(DCGE::MODEL_IS_CHANGED, true);
		}

		$this->setProperty($strPropertyName, $varValue);
	}


	/**
	 * Update all properties in the model.
	 *
	 * @param array $arrProperties The property values as name => value pairs.
	 *
	 * @return void
	 */
	public function setPropertiesAsArray($arrProperties)
	{
		$this->setPropertiesAsArray($arrProperties);
	}


	/**
	 * Update meta information in the model.
	 *
	 * @param string $strMetaName The meta information name.
	 *
	 * @param mixed $varValue The meta information value to store.
	 *
	 * @return void
	 */
	public function setMeta($strMetaName, $varValue)
	{
		$this->setMeta($strMetaName, $varValue);
	}


	/**
	 * Check if this model have any properties.
	 *
	 * @return boolean true if any property has been stored, false otherwise.
	 */
	public function hasProperties()
	{
		return $this->model->hasProperties();
	}


	/**
	 * Return the data provider name.
	 *
	 * @return string the name of the corresponding data provider.
	 */
	public function getProviderName()
	{
		return $this->model->getProviderName();
	}


	/**
	 * Copy this model, without the id.
	 *
	 * @return \DcGeneral\Data\ModelInterface
	 */
	public function __clone()
	{
		$this->model = clone $this->model;
	}

} 