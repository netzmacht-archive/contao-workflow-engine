<?php
/**
 * Created by PhpStorm.
 * User: david
 * Date: 08.11.13
 * Time: 14:22
 */

namespace Workflow\Controller;

use DcaTools\Model\FilterBuilder;
use DcGeneral\Data\ModelInterface;
use Workflow\Exception\WorkflowException;
use Workflow\Handler\ProcessFactory;
use Workflow\Handler\ProcessHandler;

class WorkflowFactory
{
	/**
	 * @param ModelInterface $entity
	 * @return Controller
	 */
	public static function createController(ModelInterface $entity)
	{
		global $container;

		$driverManager   = $container['workflow.driver-manager'];
		$eventDispatcher = $container['event-dispatcher'];

		$workflow = static::createWorkflow($entity->getProviderName(), $driverManager);
		return new Controller($entity, $workflow, $eventDispatcher, $driverManager);
	}


	/**
	 * @param $tableName
	 *
	 * @throws
	 * @return Workflow
	 */
	public static function createWorkflow($tableName)
	{
		global $container;

		/** @var \Workflow\Data\DriverManagerInterface $driverManager */
		$driverManager = $container['workflow.driver-manager'];
		$driver        = $driverManager->getDataProvider('tl_workflow');

		$config = FilterBuilder::create()
			->addEquals('forTable', $tableName)
			->getConfig($driver);

		$model = $driver->fetch($config);

		if(!$model)
		{
			throw new WorkflowException(sprintf('Undefined workflow for "%s"', $tableName));
		}

		/** @var $handlerManager */
		$handler = static::createProcessHandler($model->getProperty('process'));
		return new Workflow($model, $handler);
	}


	/**
	 * Create the process handler
	 *
	 * @param string|int|\DcGeneral\Data\ModelInterface $process
	 *
	 * @throws
	 * @return ProcessHandler
	 */
	public static function createProcessHandler($process)
	{
		global $container;

		$process = ProcessFactory::create($process);
		return new ProcessHandler($process, $container['event-dispatcher'], $container['workflow.model-state-storage']);
	}

}
