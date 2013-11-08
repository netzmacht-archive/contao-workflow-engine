<?php

namespace Workflow\Model;

use DcaTools\Definition;
use DcaTools\Model\FilterBuilder;
use Workflow\Controller\Controller;
use DcGeneral\Data\ModelInterface as EntityInterface;


/**
 * Class Model
 * @package Workflow\Model
 */
class Model implements ModelInterface
{

	/**
	 * @var \DcGeneral\Data\ModelInterface
	 */
	protected $entity;


	/**
	 * @var Controller
	 */
	protected $controller;


	/**
	 * @param \DcGeneral\Data\ModelInterface $entity
	 * @param Controller $controller
	 */
	public function __construct(EntityInterface $entity, Controller $controller)
	{
		$this->entity = $entity;
		$this->controller = $controller;
	}


	/**
	 * Returns a unique identifier.
	 *
	 * @return mixed
	 */
	public function getWorkflowIdentifier()
	{
		return md5($this->getEntity()->getProviderName() . '-' . $this->getEntity()->getId());
	}


	/**
	 * Returns data to store in the ModelState.
	 *
	 * @return array
	 */
	public function getWorkflowData()
	{
		$data = array();
		$workflow = $this->controller->getWorkflow();
		$properties = $workflow->getDataProperties();
		$children = array();

		// render property list, because properties are stored as table::property
		foreach($properties as $property)
		{
			list($table, $property) = explode('::', $property);

			// table is table of workflow, lets get the data
			if($table == $workflow->getTable())
			{
				if($property == 'id')
				{
					$data[$property] = $this->getEntity()->getId();
				}
				else {
					$data[$property] = $this->getEntity()->getProperty($property);
				}
			}

			// children data
			elseif($workflow->getStoreChildren())
			{
				$children[$table][] = $property;
			}
		}

		foreach($children as $table => $properties)
		{
			$definition = Definition::getDataContainer($table);
			$driver = $this->controller->getDriverManager()->getDataProvider($table);

			$builder = FilterBuilder::create()
				->addEquals('pid', $this->getEntity()->getId());

			if($definition->get('config/dynamicPtable'))
			{
				$builder->addEquals('ptable', $this->getEntity()->getProviderName());
			}

			$config = $builder->getConfig($driver);
			$config->setFields($properties);

			foreach($driver->fetchAll($config) as $child)
			{
				/** @var \DcGeneral\Data\ModelInterface $child */
				$data['_children'][$table][$child->getId()] = $child->getPropertiesAsArray();
			}
		}

		return $data;
	}


	/**
	 * @return \Workflow\Entity\Entity
	 */
	public function getEntity()
	{
		return $this->entity;
	}


	/**
	 * @param $status
	 */
	public function setStatus($status)
	{
		$this->entity->setProperty('workflow_status', $status);
	}


	/**
	 * @return string
	 */
	public function getStatus()
	{
		return $this->entity->getProperty('workflow_status');
	}

}
