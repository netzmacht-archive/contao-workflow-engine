<?php

namespace Workflow\Entity;

use DcGeneral\Data\ModelInterface;
use Workflow\Service\ServiceInterface;


/**
 * Class Workflow
 * @package Workflow\Entity
 */
class Workflow extends Entity
{
	protected $services;


	/**
	 * @param ModelInterface $entity
	 */
	public function __construct(ModelInterface $entity)
	{
		parent::__construct($entity);

		$this->setProperty('data_properties', deserialize($this->getProperty('data_properties'), true));
	}


	/**
	 * @return mixed
	 */
	public function getTable()
	{
		return $this->getProperty('forTable');
	}


	/**
	 * @param ServiceInterface $service
	 */
	public function addService(ServiceInterface $service)
	{
		$this->services[] = $service;
	}


	/**
	 * @param array $services
	 */
	public function setServices(array $services)
	{
		$this->services = $services;
	}


	/**
	 * @return mixed
	 */
	public function getProcessName()
	{
		return $this->getProperty('process');
	}


	/**
	 * @param $name
	 */
	public function setProcessName($name)
	{
		$this->setProperty('process', $name);
	}


	/**
	 * @return mixed
	 */
	public function getServices()
	{
		return $this->services;
	}


	/**
	 * @return mixed
	 */
	public function getDataProperties()
	{
		return $this->getProperty('data_properties');
	}


	/**
	 * @return bool
	 */
	public function getStoreChildren()
	{
		return (bool) $this->getProperty('store_children');
	}


	/**
	 * @return bool
	 */
	public function getHasAuthorColumn()
	{
		return (bool) $this->getProperty('hasAuthorColumn');
	}


	/**
	 * @return string
	 */
	public function getAuthorColumn()
	{
		return $this->getProperty('authorColumn');
	}


	/**
	 * @return bool
	 */
	public function getHasPublishColumn()
	{
		return (bool) $this->getProperty('hasPublishColumn');
	}


	/**
	 * @return string
	 */
	public function getPublishColumn()
	{
		return $this->getProperty('publishColumn');
	}


	/**
	 * @return bool
	 */
	public function getInvertPublishValue()
	{
		return (bool) $this->getProperty('invertPublishValue');
	}

}
