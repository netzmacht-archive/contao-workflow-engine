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
					->filterEquals('pid', $controller->getWorkflow()->getId());
			}

			$service = $builder->fetch();

			if($service === null)
			{
				throw new WorkflowException('Unknown service with ID');
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
		if(!$workflow instanceof ModelInterface)
		{
			$driver = $controller->getDriverManager()->getDataProvider('tl_workflow');

			$config = $driver->getEmptyConfig();
			$config->setId($workflow);

			$workflow = $driver->fetch($config);

			if($workflow === null)
			{
				throw new WorkflowException(sprintf('Unknown workflow with ID "%s', $config->getId()));
			}
		}

		$services = deserialize($workflow->getProperty('services'), true);
		$ids = array();

		foreach($services as $service)
		{
			if(!$service['disabled'])
			{
				$ids[] = $service['service'];
			}
		}

		$driver = $controller->getDriverManager()->getDataProvider('tl_workflow_service');
		$builder = ConfigBuilder::create($driver)->filterIn('id', $ids);
		$services = array();

		/** @var \DcGeneral\Data\ModelInterface $serviceModel */
		foreach($builder->fetchAll() as $serviceModel)
		{
			$services[] = static::create($serviceModel, $controller);
		}

		return $services;
	}

}
