<?php
/**
 * Created by PhpStorm.
 * User: david
 * Date: 05.11.13
 * Time: 07:12
 */

namespace Workflow\Handler;


use Workflow\Handler\ProcessHandler;
use Workflow\Model\ModelInterface;


/**
 * Workflow factory creates the environment for a given work
 * Class EnvironmentFactory
 *
 * @package Workflow\Handler
 */
class EnvironmentFactory
{

	/**
	 * Create the environment for a given Workflow model and workflow configuration
	 *
	 * @param ModelInterface $model
	 * @param \DcGeneral\Data\ModelInterface $workflow
	 * @return Environment
	 */
	public static function create(ModelInterface $model, \DcGeneral\Data\ModelInterface $workflow)
	{
		global $container;

		$environment = new Environment();

		$environment->setEventDispatcher($container['event-dispatcher']);
		$environment->setProcessManager($container['workflow.process-manager']);
		$environment->setDriverManager($container['workflow.driver-manager']);

		$process = $environment->getProcessManager()->getProcess($workflow->getProperty('process'));
		$handler = new ProcessHandler($process, $environment, $container['workflow.model-state-storage']);

		$environment->setCurrentModel($model);
		$environment->setCurrentWorkflow($workflow);
		$environment->setCurrentProcessHandler($handler);
		$environment->setCurrentState($handler->getCurrentState($model));

		return $environment;
	}

}
