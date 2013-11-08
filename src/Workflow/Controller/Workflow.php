<?php
/**
 * Created by PhpStorm.
 * User: david
 * Date: 08.11.13
 * Time: 13:50
 */

namespace Workflow\Controller;

use DcaTools\Model\FilterBuilder;
use DcGeneral\Data\ModelInterface;
use Workflow\Handler\ProcessHandlerInterface;
use Workflow\Service\ServiceFactory;

class Workflow
{
	protected $controller;

	protected $entity;

	protected $services = array();

	public function __construct(ModelInterface $entity, ProcessHandlerInterface $handler)
	{
		$this->entity = $entity;
		$this->handler = $handler;

		$this->entity->setProperty('services', deserialize($this->entity->getProperty('services'), true));
		$this->entity->setProperty('data_properties', deserialize($this->entity->getProperty('data_properties'), true));
	}


	/**
	 * @param Controller $controller
	 */
	public function initialize(Controller $controller)
	{
		$driver = $controller->getDriverManager()->getDataProvider('tl_workflow_service');
		$ids    = array();

		foreach($this->entity->getProperty('services') as $service)
		{
			if(!$service['disabled'])
			{
				$ids[] = $service['service'];
			}
		}

		$config = FilterBuilder::create()
			->addIn('id', $ids)
			->getConfig($driver);

		$this->services[] = ServiceFactory::create('core', $controller);

		foreach($driver->fetchAll($config) as $service)
		{
			$this->services[] = ServiceFactory::create($service, $controller);
		}
	}

	public function getProcessHandler()
	{
		return $this->handler;
	}

	public function getTable()
	{
		return $this->entity->getProperty('forTable');
	}

	public function getProcessName()
	{
		return $this->handler->getProcessName();
	}


	public function getServices()
	{
		return $this->services;
	}

	public function getDataProperties()
	{
		return $this->entity->getProperty('data_properties');
	}

	public function getStoreChildren()
	{
		return (bool) $this->entity->getProperty('store_children');
	}


	public function getId()
	{
		return $this->entity->getId();
	}

}
