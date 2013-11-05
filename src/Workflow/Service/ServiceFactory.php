<?php

namespace Workflow\Service;

use DcaTools\Model\FilterBuilder;
use DcGeneral\Data\ModelInterface;
use Workflow\Exception\WorkflowException;
use Workflow\Handler\Environment;

class ServiceFactory
{

	/**
	 * Create a service
	 *
	 * @param ModelInterface|int $service service model or id
	 * @param Environment $environment
	 *
	 * @return ServiceInterface
	 * @throws \Workflow\Exception\WorkflowException
	 */
	public static function create($service, Environment $environment)
	{
		if(!$service instanceof ModelInterface)
		{
			$driver = $environment->getDriverManager()->getDataProvider('tl_workflow_service');

			$config = $driver->getEmptyConfig();
			$config->setId($service);

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
		return static::instantiate($service, $serviceClass, $environment);
	}


	/**
	 * Instantiate a service
	 *
	 * @param ModelInterface $service
	 * @param $class
	 * @param Environment $environment
	 *
	 * @return ModelInterface|ServiceInterface
	 */
	public static function instantiate(ModelInterface $service, $class, Environment $environment)
	{
		/** @var \Workflow\Service\ServiceInterface $service */
		$service = new $class($service, $environment);
		$service->initialize();

		return $service;
	}


	/**
	 * @param Environment $environment
	 *
	 * @return ServiceInterface
	 */
	public static function forEnvironment(Environment $environment)
	{
		$workflow = $environment->getCurrentWorkflow();

		return static::forWorkflow($workflow, $environment);
	}


	/**
	 * @param ModelInterface|int $workflow workflow model or id
	 * @param Environment $environment
	 *
	 * @return array
	 * @throws \Workflow\Exception\WorkflowException
	 */
	public static function forWorkflow($workflow, Environment $environment)
	{
		if(!$workflow instanceof ModelInterface)
		{
			$driver = $environment->getDriverManager()->getDataProvider('tl_workflow');

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

		$driver = $environment->getDriverManager()->getDataProvider('tl_workflow_service');
		$config = FilterBuilder::create()->addIn('id', $ids)->getConfig($driver);
		$services = array();

		/** @var \DcGeneral\Data\ModelInterface $serviceModel */
		foreach($driver->fetchAll($config) as $serviceModel)
		{
			$services[] = static::create($serviceModel, $environment);
		}

		return $services;
	}

}
