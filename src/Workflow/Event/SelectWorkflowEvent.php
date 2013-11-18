<?php

namespace Workflow\Event;

use DcaTools\Definition;
use DcGeneral\Data\CollectionInterface;
use DcGeneral\Data\ModelInterface;
use Symfony\Component\EventDispatcher\Event;


class SelectWorkflowEvent extends Event
{

	/**
	 * @var \DcGeneral\Data\ModelInterface
	 */
	protected $entity;


	/**
	 * @var \DcaTools\Definition\DataContainer
	 */
	protected $definition;


	/**
	 * @var
	 */
	protected $workflows = array();


	/**
	 * @var array
	 */
	protected $priorities = array();


	/**
	 * @param CollectionInterface $workflows
	 * @param ModelInterface $entity
	 */
	public function __construct(CollectionInterface $workflows, ModelInterface $entity)
	{
		$this->entity     = $entity;
		$this->definition = Definition::getDataContainer($entity->getProviderName());

		/** @var ModelInterface $workflow */
		foreach($workflows as $workflow)
		{
			$this->workflows[$workflow->getId()] = $workflow;
			$this->priorities[$workflow->getId()] = 0;
		}
	}


	/**
	 * @return ModelInterface
	 */
	public function getEntity()
	{
		return $this->entity;
	}


	/**
	 * @return mixed
	 */
	public function getWorkflows()
	{
		return $this->workflows;
	}


	/**
	 * Select a workflow will increase its priority
	 *
	 * @param ModelInterface|int $workflow workflow entity or id
	 */
	public function selectWorkflow($workflow)
	{
		if($workflow instanceof ModelInterface)
		{
			$workflow = $workflow->getId();
		}

		$this->priorities[$workflow]++;
	}


	/**
	 * Get selected workflow
	 *
	 * @return mixed|null
	 */
	public function getSelectedWorkflow()
	{
		if(count($this->priorities))
		{
			arsort($this->priorities);

			if(reset($this->priorities) > 0)
			{
				$id = key($this->priorities);

				return $this->workflows[$id];
			}
		}

		return null;
	}


	/**
	 * @return Definition\DataContainer
	 */
	public function getDefinition()
	{
		return $this->definition;
	}

}
