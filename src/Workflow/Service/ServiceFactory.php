<?php

namespace Workflow\Service;

use DcaTools\Model\FilterBuilder;
use DcGeneral\Data\ModelInterface;
use Workflow\Controller\Controller;
use Workflow\Exception\WorkflowException;

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
			/** @var \Workflow\Data\DriverManagerInterface $driverManager */
			$driverManager = $container['workflow.driver-manager'];
			$driver = $driverManager->getDataProvider('tl_workflow_service');

			if($service == 'core')
			{
				$model = $driver->getEmptyModel();
				$model->setId($service);

				$service = new CoreService($model, $controller);
				$service->initialize();

				return $service;
			}

			if(is_numeric($service))
			{
				$config = $driver->getEmptyConfig();
				$config->setId($service);
			}
			else
			{
				$config = FilterBuilder::create()
					->addEquals('service', $service)
					->addEquals('pid', $controller->getWorkflow()->getId())
					->getConfig($driver);
			}

			$service = $driver->fetch($config);

			if($service === null)
			{
				throw new WorkflowException(sprintf('Unknown service with ID "%s', $config->getId()));
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
		$service->initialize();

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
		$config = FilterBuilder::create()->addIn('id', $ids)->getConfig($driver);
		$services = array();

		/** @var \DcGeneral\Data\ModelInterface $serviceModel */
		foreach($driver->fetchAll($config) as $serviceModel)
		{
			$services[] = static::create($serviceModel, $controller);
		}

		return $services;
	}

}
