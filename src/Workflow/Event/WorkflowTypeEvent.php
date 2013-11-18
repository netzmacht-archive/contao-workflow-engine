<?php
/**
 * Created by PhpStorm.
 * User: david
 * Date: 15.11.13
 * Time: 11:08
 */

namespace Workflow\Event;

use DcGeneral\Data\ModelInterface as EntityInterface;
use Symfony\Component\EventDispatcher\Event;

class WorkflowTypeEvent extends Event
{

	/**
	 * @var \DcGeneral\Data\ModelInterface
	 */
	protected $entity;

	/**
	 * @var array
	 */
	protected $types = array();


	/**
	 * @param EntityInterface $entity
	 */
	public function __construct(EntityInterface $entity)
	{
		$this->entity = $entity;
	}


	/**
	 * @return \DcGeneral\Data\ModelInterface
	 */
	public function getEntity()
	{
		return $this->entity;
	}


	/**
	 * @return array
	 */
	public function getTypes()
	{
		return $this->types;
	}


	/**
	 * @param string $typeName
	 */
	public function addType($typeName)
	{
		$this->types[] = $typeName;
	}

} 