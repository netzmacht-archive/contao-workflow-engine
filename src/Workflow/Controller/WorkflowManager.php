<?php
/**
 * Created by PhpStorm.
 * User: david
 * Date: 14.11.13
 * Time: 08:52
 */

namespace Workflow\Controller;

use DcaTools\Data\ConfigBuilder;
use DcGeneral\Data\DriverInterface;
use DcGeneral\Data\ModelInterface as EntityInterface;
use Symfony\Component\EventDispatcher\EventDispatcher;
use Workflow\Entity\Registry;
use Workflow\Entity\Workflow;
use Workflow\Event\SelectWorkflowEvent;
use Workflow\Exception\WorkflowException;

class WorkflowManager
{

	/**
	 * @var Workflow[]
	 */
	protected $workflows = array();

	/**
	 * @var \DcGeneral\Data\DriverInterface
	 */
	protected $driver;


	/**
	 * @var \Workflow\Entity\Registry
	 */
	protected $registry;


	/**
	 * @var \Symfony\Component\EventDispatcher\EventDispatcher
	 */
	protected $eventDispatcher;


	/**
	 * @param DriverInterface $driver
	 * @param Registry $registry
	 * @param EventDispatcher $eventDispatcher
	 */
	public function __construct(DriverInterface $driver, Registry $registry, EventDispatcher $eventDispatcher)
	{
		$this->driver = $driver;
		$this->registry = $registry;
		$this->eventDispatcher = $eventDispatcher;
	}


	public function bootstrap(Controller $controller)
	{
		/** @var \Workflow\Controller\WorkflowInterface $workflow */
		foreach($GLOBALS['TL_WORKFLOWS'] as $workflow)
		{
			$workflow::bootstrap($controller);
		}
	}


	/**
	 * @param EntityInterface $entity
	 *
	 * @return \Workflow\Controller\WorkflowInterface
	 * @throws \Workflow\Exception\WorkflowException
	 */
	public function create(EntityInterface $entity)
	{
		$type = $entity->getProperty('workflow');

		if(isset($GLOBALS['TL_WORKFLOWS'][$type]))
		{
			$class = $GLOBALS['TL_WORKFLOWS'][$type];
			return new $class($entity);
		}

		throw new WorkflowException(sprintf('Invalid workflow type "%s"', $type));
	}


	/**
	 * @param EntityInterface $entity
	 *
	 * @return EntityInterface
	 */
	public function getWorkflow(EntityInterface $entity)
	{
		$workflows = $this->loadWorkflows($entity->getProviderName());

		$event = new SelectWorkflowEvent($workflows, $entity);
		$this->eventDispatcher->dispatch('workflow.selectWorkflow', $event);

		$workflow = $event->getSelectedWorkflow();

		if($workflow)
		{
			return new Workflow($workflow);
		}

		return null;
	}


	/**
	 * @param $types
	 *
	 * @return \DcGeneral\Data\CollectionInterface
	 */
	public function loadWorkflows(array $types)
	{
		return ConfigBuilder::create($this->driver)
			->filterIn('workflow', $types)
			->fetchAll();
	}

}
