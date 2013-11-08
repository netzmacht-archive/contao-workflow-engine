<?php

namespace Workflow\Controller;

use DcaTools\Model\FilterBuilder;
use DcGeneral\Data\ModelInterface;
use Workflow\Exception\WorkflowException;
use Workflow\Handler\ProcessHandler;

class ControllerFactory
{




	public static function createWorkflow($table)
	{
		global $container;

		$driverManager   = $container['workflow.driver-manager'];
	}


	/**
	 * @param $processId
	 * @return ProcessHandler
	 */
	public static function createProcessHandler($processId)
	{
		global $container;

		/** @var \Workflow\Handler\ProcessManager $processManager */
		$processManager = $container['workflow.process-manager'];

		$process  = $processManager->getProcess($processId);
		$handler  = new ProcessHandler($process, $container['event-dispatcher'], $container['workflow.model-state-storage']);

		return $handler;
	}


	/**
	 * @param ModelInterface $model
	 * @param \Workflow\Data\DriverManagerInterface $driverManager
	 *
	 * @throws WorkflowException
	 * @return ModelInterface
	 */
	protected static function loadWorkflow(ModelInterface $model, $driverManager)
	{
		$driver = $driverManager->getDataProvider('tl_workflow');

		$config = FilterBuilder::create()->addEquals('forTable', $model->getProviderName())->getConfig($driver);
		$workflow = $driver->fetch($config);

		if(!$workflow)
		{
			throw new WorkflowException(sprintf('No workflow found for model of "%s" with ID "%s', $model->getProviderName(), $model->getId()));
		}

		return $workflow;
	}

}
