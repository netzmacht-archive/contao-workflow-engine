<?php
/**
 * Created by PhpStorm.
 * User: david
 * Date: 28.10.13
 * Time: 19:06
 */

namespace Workflow\Event;

use DcaTools\Definition;
use Symfony\Component\EventDispatcher\Event;
use Workflow\Model\Model;


/**
 * Class WorkflowDataEvent
 *
 * Event class for prepareWorkflowData event which is triggered before model stores their attributes
 *
 * @package Workflow\Event
 */
class WorkflowDataEvent extends Event
{

	/**
	 * @var Model
	 */
	protected $model;


	/**
	 * @var
	 */
	protected $data = array();


	/**
	 * @param Model $model
	 */
	public function __construct(Model $model)
	{
		$this->model = $model;
	}


	/**
	 * @return \Workflow\Model\Model
	 */
	public function getModel()
	{
		return $this->model;
	}


	/**
	 * @param string $property name of property
	 */
	public function addProperty($property)
	{
		$this->addData($property, $this->model->getEntity()->getProperty($property));
	}


	/**
	 * @param array $properties properties name, if null all properties will be added
	 */
	public function addProperties(array $properties=null)
	{
		if($properties === null)
		{
			foreach($this->model->getEntity()->getPropertiesAsArray() as $name => $value)
			{
				$this->addData($name, $value);
			}
		}
		else {
			foreach($properties as $name)
			{
				$this->addProperty($name);
			}
		}
	}


	/**
	 * @return \DcaTools\Definition\DataContainer
	 */
	public function getDefinition()
	{
		return Definition::getDataContainer($this->getModel()->getEntity()->getProviderName());
	}


	/**
	 * @param $name
	 * @param null $default
	 * @return array|null
	 */
	public function getData($name, $default=null)
	{
		if(isset($this->data[$name]))
		{
			return $this->data[$name];
		}

		return $default;
	}


	/**
	 * @param $name
	 * @param $data
	 */
	public function addData($name, $data)
	{
		$this->data[$name] = $data;
	}


	/**
	 * @param $name
	 * @return bool
	 */
	public function hasData($name)
	{
		return isset($this->data[$name]);
	}


	/**
	 * @return array
	 */
	public function getDataArray()
	{
		return $this->data;
	}

}
