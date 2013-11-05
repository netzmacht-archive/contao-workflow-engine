<?php
/**
 * Created by PhpStorm.
 * User: david
 * Date: 26.10.13
 * Time: 18:00
 */

namespace Workflow\Model;

use Symfony\Component\EventDispatcher\EventDispatcher;
use Workflow\Event\WorkflowDataEvent;
use Workflow\Model\ModelInterface;


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
	 * @var \Symfony\Component\EventDispatcher\EventDispatcher
	 */
	protected $dispatcher;


	/**
	 * @param \DcGeneral\Data\ModelInterface $entity
	 * @param EventDispatcher $dispatcher
	 */
	public function __construct(\DcGeneral\Data\ModelInterface $entity, EventDispatcher $dispatcher)
	{
		$this->entity = $entity;
		$this->dispatcher = $dispatcher;
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
		$event = new WorkflowDataEvent($this);
		$eventName = sprintf('workflow.%s.get_data', $this->getEntity()->getProviderName());

		$this->dispatcher->dispatch($eventName, $event);

		return $event->getDataArray();
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
