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


	public function getProcessName()
	{
		return $this->getProperty('process');
	}


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

}
