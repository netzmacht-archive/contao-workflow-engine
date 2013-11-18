<?php

namespace Workflow\Service;

use DcaTools\Data\ModelFactory;
use DcaTools\Definition;
use DcGeneral\Data\ModelInterface as EntityInterface;
use Workflow\Contao\Connector\AbstractConnector;
use Workflow\Controller\Controller;
use Workflow\Exception\WorkflowException;


/**
 * Class ParentService
 *
 * Parent service triggers a next state of the parent when entity has changed
 *
 * @package Workflow\Service
 * @author David Molineus <molineus@netzmacht.de>
 */
class ParentService extends AbstractService
{

	/**
	 * @var string
	 */
	protected $parent;


	/**
	 * Store instance so the callbacks get the initialized version
	 * @var $this
	 */
	protected static $instance;


	/**
	 * Track changes of element
	 *
	 * @var bool
	 */
	protected $changed = false;


	/**
	 * @var EntityInterface
	 */
	protected $entity;


	/**
	 * @var array
	 */
	protected static $config = array
	(
		'name'      => 'parent',
		'drivers'   => array('Table'),
		'config'    => array
		(
			'scope' => array('state', 'steps'),
		),
	);


	/**
	 * @param EntityInterface $service
	 * @param Controller $controller
	 */
	public function __construct(EntityInterface $service, Controller $controller)
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
		$table      = $this->service->getProperty('tableName');
		$definition = Definition::getDataContainer($table);
		$config     = $this->controller->getCurrentWorkflow()->getConfig($table);

		if($config['parent'])
		{
			$class = get_class($this);
			$this->parent = $config['parent'];

			foreach($definition->getProperties() as $property)
			{
				if($property->isEditable())
				{
					$property->registerCallback('save', array($class, 'callbackSave'));
				}
			}

			$definition->registerCallback('ondelete', array($class, 'callbackRouter'));
			$definition->registerCallback('onsubmit', array($class, 'callbackOnSubmit'));
			$definition->registerCallback('oncreate', array($class, 'callbackRouter'));

			// TODO: which callbacks do we need?
		}
	}


	/**
	 * Route to parent next state
	 *
	 * @param $dc
	 */
	public function callbackRouter($dc)
	{
		$entity = $this->getEntity($dc);

		if($this->isAssigned($entity))
		{
			$workflow = $this->controller->getCurrentWorkflow();
			$parent   = $workflow->getParent($entity);

			if($parent)
			{
				$this->controller->initialize($parent);

				try {
					$this->controller->reachNextState($this->service->getProperty('state'));
				}
				catch(WorkflowException $e)
				{
					AbstractConnector::error($e->getMessage(), false);
				}
			}
		}
	}


	/**
	 * Rout to parent next state if changes were tracked
	 *
	 * @param $dc
	 */
	public function callbackOnSubmit($dc)
	{
		if($this->changed)
		{
			$this->callbackRouter($dc);
		}
	}


	/**
	 * Detect changes
	 *
	 * @param $value
	 * @param $dc
	 */
	public function callbackSave($value, $dc)
	{
		if(!$this->changed)
		{
			// TODO: check which dc is used
			$entity = $this->getEntity($dc);
			$this->changed = ($value != $entity->getProperty($dc->field));
		}

		return $value;
	}


	/**
	 * Initialize the entity
	 *
	 * @param $dc
	 * @return EntityInterface
	 */
	protected function getEntity($dc)
	{
		if(!$this->entity)
		{
			$this->entity = ModelFactory::byDc($dc);
		}

		return $this->entity;
	}

}
