<?php

namespace Workflow\Service;

use DcaTools\Translator;
use DcGeneral\Data\ModelInterface;
use Workflow\Controller\Controller;


/**
 * Class AbstractService implements basic methods for workflow services
 *
 * @package Workflow\Service
 */
abstract class AbstractService implements ServiceInterface
{

	/**
	 * @var \DcGeneral\Data\ModelInterface
	 */
	protected $service;


	/**
	 * @var Controller
	 */
	protected $controller;


	/**
	 * @var array|Config
	 */
	protected static $config;


	/**
	 * @param ModelInterface $service
	 * @param Controller $controller
	 */
	public function __construct(ModelInterface $service, Controller $controller)
	{
		$this->service = $service;
		$this->controller = $controller;

		$this->service->setProperty('steps', deserialize($this->service->getProperty('steps'), true));
		$this->service->setProperty('events', deserialize($this->service->getProperty('events'), true));
	}


	/**
	 * Initialize the workflow service
	 *
	 * @inheritdoc
	 */
	abstract function initialize();


	/**
	 * @param \Workflow\Model\ModelInterface $model
	 * @return array
	 */
	protected function getModelProperties(\Workflow\Model\ModelInterface $model)
	{
		$entity = $model->getEntity();
		$translator = Translator::create($entity->getProviderName());
		$properties = array();

		foreach($this->service->getProperty('model_properties') as $property)
		{
			if($entity->getProperty($property) !== null)
			{
				$properties[$property]['label'] = $translator->property($property);
				$properties[$property]['value'] = $translator->value($property, $entity->getProperty($property), '-');
			}
		}

		return $properties;
	}


	/**
	 * Get config
	 *
	 * @return Config
	 */
	public static function getConfig()
	{
		if(!static::$config instanceof Config)
		{
			static::$config = new Config(static::$config);
		}

		return static::$config;
	}

}
