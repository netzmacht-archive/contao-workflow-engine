<?php

namespace Workflow\Contao\Connector;

use DcaTools\Data\ConfigBuilder;
use DcaTools\Data\ModelFactory;
use DcaTools\Definition;
use DcaTools\Definition\DataContainer;
use Symfony\Component\EventDispatcher\EventDispatcher;


/**
 * TableConnector is the default connector for DC_Table
 *
 * @package Workflow\Contao\Connector
 * @author David Molineus <molineus@netztmacht.de>
 */
class TableConnector extends AbstractConnector
{

	/**
	 * Initialisation state
	 *
	 * @var bool
	 */
	protected $initialized = false;


	/**
	 * Track changes with save callback
	 *
	 * @var bool
	 */
	protected $reachedChanged = false;


	/**
	 * Cached process state names
	 *
	 * @var array
	 */
	protected $states;


	/**
	 * Bootstrap the connector has to define method for registering the initialize method
	 *
	 * @param DataContainer $definition
	 * @param EventDispatcher $eventDispatcher
	 * @return mixed
	 */
	public static function bootstrap(DataContainer $definition, EventDispatcher $eventDispatcher)
	{
		$class = get_called_class();
		$definition->registerCallback('onload', array($class, 'initialize'));
	}


	/**
	 * Initialize the Workflow connector
	 *
	 * @param mixed $dc
	 * @return bool
	 */
	public function initialize($dc)
	{
		if(!$this->initialized)
		{
			$this->initialized = true;

			if(($success = parent::initialize($dc)))
			{
				$this->registerCallbacks();
			}

			return $success;
		}

		return true;
	}


	/**
	 * Initialize Definition of object
	 *
	 * @param \DC_Table $dc
	 */
	protected function initializeDefinition($dc)
	{
		$this->definition = Definition::getDataContainer($dc->table);
	}


	/**
	 * @return \DcGeneral\Data\ModelInterface|null
	 */
	protected function initializeEntity()
	{
		$driver = $this->controller->getDataProvider($this->definition->getName());
		$entity = null;

		if($this->parentView)
		{
			if(!$this->id)
			{
				if(CURRENT_ID)
				{
					$this->id = CURRENT_ID;
				}
			}

			if($this->id && $this->definition->get('config/ptable'))
			{
				$table  = $this->definition->get('config/ptable');
				$driver = $this->controller->getDataProvider($table);
				$entity = ConfigBuilder::create($driver)->setId($this->id)->fetch();
			}
		}
		else {
			$entity  = ConfigBuilder::create($driver)->setId($this->id)->fetch();
		}

		return $entity;
	}


	/**
	 * Register callbacks
	 */
	protected function registerCallbacks()
	{
		$class = get_class($this);

		$this->definition->registerCallback('onsubmit', array($class, 'callbackOnSubmit'));
		$this->definition->registerCallback('ondelete', array($class, 'callbackOnDelete'));

		foreach($this->definition->getProperties() as $property)
		{
			if($property->isEditable())
			{
				$property->registerCallback('save', array($class, 'callbackSave'));
			}
		}
	}


	/**
	 * Save callback is used to track changes and try to reach the next step
	 *
	 * @param $value
	 * @param $dc
	 *
	 * @return mixed
	 */
	public function callbackSave($value, $dc)
	{
		if(!$this->reachedChanged)
		{
			$entity = ModelFactory::byDc($dc);

			if($this->controller->initialize($entity) && $value != $entity->getProperty($dc->field))
			{
				if($this->hasState('change'))
				{
					$this->reachNextState('change');
					$this->reachedChanged = true;
				}
			}
		}

		return $value;
	}


	/**
	 * If next step is reached we have to update the workflow model data because DC_Table does not provide a
	 * callback for getting validated record before storing it
	 */
	public function callbackOnSubmit($dc)
	{
		$entity = ModelFactory::byDc($dc);

		if($this->reachedChanged && $this->controller->initialize($entity))
		{
			$state = $this->controller->getCurrentState();
			$state->setData($this->controller->getCurrentModel()->getWorkflowData());

			$driver = $this->controller->getDataProvider('tl_workflow_state');
			$driver->save($state);
		}
	}


	/**
	 * Trigger delete action if action is defined in process steps
	 */
	public function callbackOnDelete($dc)
	{
		$entity = ModelFactory::byDc($dc);

		if($this->controller->initialize($entity))
		{
			$this->reachNextState('delete');
		}
	}


	public function callbackLabel()
	{

	}


	/**
	 * Check if a state action exists in process steps
	 *
	 * @param $name
	 * @return bool
	 */
	protected function hasState($name)
	{
		if(!$this->states)
		{
			$this->states = array();

			$steps = $this->controller->getProcessHandler()->getProcess()->getSteps();

			foreach($steps as $step)
			{
				foreach($step->getNextStates() as $state)
				{
					$this->states[] = $state->getName();
				}
			}
		}

		return in_array($name, $this->states);
	}

}
