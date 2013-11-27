<?php

namespace Workflow\Model;

use DcaTools\Definition;
use DcGeneral\Data\ModelInterface as EntityInterface;
use Workflow\Controller\Controller;


/**
 * Class Model
 * @package Workflow\Model
 */
class Model implements ModelInterface
{
	const STATUS_DELETED = 'deleted';

	const STATE_CHANGE  = 'change';

	const STATE_DELETE  = 'delete';

	const STATE_RESTORE = 'restore';


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
		$workflow = $this->controller->getCurrentWorkflow();
		return $workflow->getWorkflowData($this->entity);
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
