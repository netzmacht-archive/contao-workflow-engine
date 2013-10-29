<?php

namespace WorkflowEngine\Model;


class Filter
{

	/**
	 * @var array
	 */
	protected $filters = array();


	/**
	 * @return static
	 */
	public static function create()
	{
		return new static();
	}

	/**
	 * Add an or filter
	 *
	 * @param array $children set of filter
	 *
	 * @return $this
	 */
	public function addOr(array $children)
	{
		return $this->addFilter('OR', array('children' => $children));
	}


	/**
	 * Add an and filter
	 *
	 * @param array $children set of filter
	 *
	 * @return $this
	 */
	public function addAnd(array $children)
	{
		return $this->addFilter('AND', array('children' => $children));
	}


	/**
	 * Add an in filter
	 *
	 * @param string $property property name
	 * @param array $values property value
	 * @return $this
	 */
	public function addIn($property, array $values)
	{
		return $this->addFilter('IN', array('values' => $values), $property);
	}


	/**
	 * Add an like filter
	 *
	 * @param string $property property name
	 * @param mixed $value property value
	 * @return $this
	 */
	public function addLike($property, $value)
	{
		return $this->addFilter('LIKE', array('value' => $value), $property);
	}


	/**
	 * Add an equals filter
	 *
	 * @param string $property property name
	 * @param mixed $value property value
	 * @return $this
	 */
	public function addEquals($property, $value)
	{
		return $this->addFilter('=', array('value' => $value), $property);
	}

	/**
	public function addNotEquals($property, $value)
	{
		// DcGeneral does not to support it so far
	}
	*/


	/**
	 * Add a greater than filter
	 *
	 * @param string $property property name
	 * @param mixed $value property value
	 * @return $this
	 */
	public function addGreaterThan($property, $value)
	{
		return $this->addFilter('>', array('value' => $value), $property);
	}


	/**
	 * Add a lesser than filter
	 *
	 * @param string $property property name
	 * @param mixed $value property value
	 * @return $this
	 */
	public function addLesserThan($property, $value)
	{
		return $this->addFilter('<', array('value' => $value), $property);
	}


	/**
	 * @return array
	 */
	public function getFilter()
	{
		return $this->filters;
	}


	/**
	 * @param $operation
	 * @param array $filter
	 * @param null $property
	 *
	 * @return $this
	 */
	public function addFilter($operation, array $filter=array(), $property=null)
	{
		$filter['operation'] = $operation;

		if($property !== null)
		{
			$filter['property']  = $property;
		}

		$this->filters[] = $filter;

		return $this;
	}

}
