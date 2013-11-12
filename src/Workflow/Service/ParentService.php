<?php

namespace Workflow\Service;

use DcaTools\Definition;
use DcGeneral\Data\ModelInterface;
use Workflow\Controller\Controller;
use Workflow\Controller\ControllerFactory;
use Workflow\Exception\WorkflowException;


class ParentService extends AbstractService
{

	/**
	 * @var Controller
	 */
	protected static $parentController;


	/**
	 * Store instance so the callbacks get the initialized version
	 * @var $this
	 */
	protected static $instance;


	/**
	 * @var array
	 */
	protected static $config = array
	(
		'identifier' => 'parent',
		'version'    => '1.0.0',
		'properties' => array(),
	);


	/**
	 * @param ModelInterface $service
	 * @param Controller $controller
	 */
	public function __construct(ModelInterface $service, Controller $controller)
	{
		parent::__construct($service, $controller);

		static::$instance = $this;
	}


	/**
	 * Get instance for Contao
	 *
	 * @return mixed
	 */
	public static function getInstance()
	{
		return static::$instance;
	}


	/**
	 * @inheritdoc
	 */
	public function initialize()
	{
		$table = $this->controller->getWorkflow()->getTable();

		$definition = Definition::getDataContainer($table);

		$parentId = $this->controller->getModel()->getEntity()->getProperty('pid');
		$parentTable = $definition->get('config/ptable');
		$driver = $this->controller->getDriverManager()->getDataProvider($parentTable);

		$config = $driver->getEmptyConfig();
		$config->setId($parentId);

		$entity = $driver->fetch($config);

		if(!$entity)
		{
			throw new WorkflowException('Huh, could not load parent');
		}

		$controller = ControllerFactory::create($entity);
		$controller->initialize();

		static::$parentController = $controller;

		$class = get_class($this);
		$definition->registerCallback('ondelete', array($class, 'callbackRouter'));
		$definition->registerCallback('onsubmit', array($class, 'callbackRouter'));
		$definition->registerCallback('oncreate', array($class, 'callbackRouter'));
		//$definition->registerCallback('oncut', array($class, 'callbackRouter'));
	}


	/**
	 * @return \Workflow\Controller\Controller
	 */
	public static function getParentController()
	{
		return static::$parentController;
	}


	/**
	 * Route to parent events
	 */
	public function callbackRouter()
	{
		static::getParentController()->reachNextState('change');
	}

}
