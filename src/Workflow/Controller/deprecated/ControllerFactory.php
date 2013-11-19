<?php

namespace Workflow\Controller;

use DcaTools\Data\ConfigBuilder;
use DcGeneral\Data\DCGE;
use DcGeneral\Data\ModelInterface;
use Workflow\Entity\Workflow;
use Workflow\Exception\WorkflowException;
use Workflow\Handler\ProcessFactory;
use Workflow\Handler\ProcessHandler;


/**
 * Class ControllerFactory
 * @package Workflow\Controller
 */
class ControllerFactory
{
	/**
	 * Create a controller
	 *
	 * @param string $tableName
	 *
	 * @return Controller
	 * @throws
	 */
	public static function create($tableName)
	{
		global $container;

		/** @var \DcaTools\Data\DriverManagerInterface $driverManager */
		$driverManager   = $container['dcatools.driver-manager'];
		$eventDispatcher = $container['event-dispatcher'];

		return new Controller($tableName, $eventDispatcher, $driverManager);
	}


	protected static function findWorkflow(ModelInterface $entity)
	{
		global $container;

		/** @var \DcaTools\Data\DriverManagerInterface $driverManager */
		$driverManager = $container['dcatools.driver-manager'];

		$driver  = $driverManager->getDataProvider('tl_workflow');
		$table   = $entity->getProviderName();

		if($GLOBALS['TL_WORKFLOW_CONDITIONS'][$table])
		{
			/** @var \Workflow\Contao\WorkflowCondition\WorkflowConditionInterface $condition */
			$condition = $GLOBALS['TL_WORKFLOW_CONDITIONS'][$table];

			$builder = ConfigBuilder::create($driver)
				->filterEquals('forTable', $entity->getProviderName())
				->filterEquals('active', '1')
				->sorting('addCondition', DCGE::MODEL_SORTING_DESC);

			$model = $condition::selectWorkflow($builder->fetchAll(), $entity);
		}
		else {
			$model = ConfigBuilder::create($driver)
				->filterEquals('forTable', $entity->getProviderName())
				->filterEquals('active', '1')
				->fetch();
		}

		if(!$model)
		{
			throw new WorkflowException(sprintf('Undefined workflow for "%s"', $entity->getProviderName()));
		}

		return new Workflow($model);
	}

}
