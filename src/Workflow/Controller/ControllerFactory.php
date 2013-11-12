<?php

namespace Workflow\Controller;

use DcaTools\Data\ConfigBuilder;
use DcGeneral\Data\ModelInterface;
use Workflow\Entity\Workflow;
use Workflow\Exception\WorkflowException;
use Workflow\Handler\ProcessFactory;
use Workflow\Handler\ProcessHandler;

class ControllerFactory
{
	/**
	 * @param ModelInterface $entity
	 *
	 * @return Controller
	 * @throws
	 */
	public static function create(ModelInterface $entity)
	{
		global $container;

		/** @var \DcaTools\Data\DriverManagerInterface $driverManager */
		$driverManager   = $container['dcatools.driver-manager'];
		$eventDispatcher = $container['event-dispatcher'];
		$stateStorage    = $container['workflow.model-state-storage'];

		$driver = $driverManager->getDataProvider('tl_workflow');
		$model  = ConfigBuilder::create($driver)->filterEquals('forTable', $entity->getProviderName())->fetch();

		if(!$model)
		{
			throw new WorkflowException(sprintf('Undefined workflow for "%s"', $entity->getProviderName()));
		}

		$workflow = new Workflow($model);
		$process  = ProcessFactory::create($workflow->getProcessName());
		$handler  = new ProcessHandler($process, $eventDispatcher, $stateStorage);

		return new Controller($entity, $workflow, $handler, $eventDispatcher, $driverManager);
	}

}
