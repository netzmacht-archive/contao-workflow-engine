<?php
/**
 * Created by PhpStorm.
 * User: david
 * Date: 19.11.13
 * Time: 06:56
 */

namespace Workflow\Entity;


use DcGeneral\Data\CollectionInterface;
use DcGeneral\Data\ModelInterface as EntityInterface;
use Workflow\Exception\WorkflowException;


/**
 * Simple entity registry
 *
 * Class Registry
 * @package Workflow\Entity
 */
class Registry
{

	/**
	 * @var array
	 */
	protected $cache = array();


	/**
	 * @param EntityInterface $entity
	 * @throws WorkflowException
	 */
	public function addEntity(EntityInterface $entity)
	{
		if(isset($this->cache[$entity->getProviderName()][$entity->getId()]))
		{
			$message = sprintf('Entity "%s ID %s" is already registered', $entity->getProviderName(), $entity->getId());
			throw new WorkflowException($message);
		}

		$this->cache[$entity->getProviderName()][$entity->getId()] = $entity;
	}


	/**
	 * @param CollectionInterface $collection
	 */
	public function addCollection(CollectionInterface $collection)
	{
		foreach($collection as $entity)
		{
			if(!$this->hasEntity($entity))
			{
				$this->addEntity($entity);
			}
		}
	}


	/**
	 * @param $tableName
	 * @param $id
	 * @return mixed
	 * @throws \Workflow\Exception\WorkflowException
	 */
	public function getEntity($tableName, $id)
	{
		if($this->hasEntity($tableName, $id))
		{
			return $this->cache[$tableName][$id];
		}

		$message = sprintf('Entity "%s ID %s" is already registered', $tableName, $id);
		throw new WorkflowException(sprintf('Entity "%s ID %s" is not registered'));
	}


	/**
	 * @param $entityOrTable
	 * @param null $id
	 * @return bool
	 */
	public function hasEntity($entityOrTable, $id=null)
	{
		if($entityOrTable instanceof EntityInterface)
		{
			$id = $entityOrTable->getId();
			$entityOrTable = $entityOrTable->getProviderName();
		}

		return isset($this->cache[$entityOrTable][$id]);
	}

}
