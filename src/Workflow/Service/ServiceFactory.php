<?php

namespace Workflow\Service;

use DcaTools\Data\ConfigBuilder;
use DcGeneral\Data\ModelInterface;
use Workflow\Controller\Controller;
use Workflow\Exception\WorkflowException;


/**
 * Class ServiceFactory
 * @package Workflow\Service
 */
class ServiceFactory
{

	/**
	 * Create a service
	 *
	 * @param ModelInterface|int $service service model or id
	 * @param Controller $controller
	 *
	 * @return ServiceInterface
	 * @throws \Workflow\Exception\WorkflowException
	 */
	public static function create($service, Controller $controller)
	{
		global $container;

		if(!$service instanceof ModelInterface)
		{
			/** @var \DcaTools\Data\DriverManagerInterface $driverManager */
			$driverManager = $container['dcatools.driver-manager'];
			$driver = $driverManager->getDataProvider('tl_workflow_service');

			if($service == 'core')
			{
				$model = $driver->getEmptyModel();
				$model->setId($service);

				$service = new CoreService($model, $controller);

				return $service;
			}

			$builder = ConfigBuilder::create($driver);

			if(is_numeric($service))
			{
				$builder->setId($service);
			}
			else
			{
				$builder
					->filterEquals('service', $service)
					->filterEquals('pid', $controller->getCurrentWorkflow()->getEntity()->getId());
			}

			$service = $builder->fetch();

			if($service === null)
			{
				throw new WorkflowException('Unknown service with ID');
			}
			elseif(!$service->getProperty('active'))
			{
				throw new WorkflowException('Service with ID is not active');
			}
		}

		$name = $service->getProperty('service');

		if(!isset($GLOBALS['TL_WORKFLOW_SERVICES'][$name]))
		{
			throw new WorkflowException(sprintf('Unknown service identifier "%s"', $name));
		}

		$serviceClass = $GLOBALS['TL_WORKFLOW_SERVICES'][$name];

		/** @var \Workflow\Service\ServiceInterface $service */
		$service = new $serviceClass($service, $controller);

		return $service;
	}


	/**
	 * @param ModelInterface|int $workflow workflow model or id
	 * @param Controller $controller
	 *
	 * @return array
	 * @throws \Workflow\Exception\WorkflowException
	 */
	public static function forWorkflow($workflow, Controller $controller)
	{
		if($workflow instanceof ModelInterface)
		{
			$workflow = $workflow->getId();
		}

		$driver = $controller->getDataProvider('tl_workflow_service');
		$builder = ConfigBuilder::create($driver)
			->filterEquals('pid', $workflow)
			->filterEquals('active', 1);

		$services = array();
		$services[] = static::create('core', $controller);

		/** @var \DcGeneral\Data\ModelInterface $serviceModel */
		foreach($builder->fetchAll() as $serviceModel)
		{
			$services[] = static::create($serviceModel, $controller);
		}

		return $services;
	}

}
