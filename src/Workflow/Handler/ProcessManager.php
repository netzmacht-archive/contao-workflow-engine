<?php
/**
 * Created by PhpStorm.
 * User: david
 * Date: 04.11.13
 * Time: 10:01
 */

namespace Workflow\Handler;


class ProcessManager
{

	/**
	 * @var array
	 */
	protected $processes = array();


	/**
	 * @var ProcessFactory
	 */
	protected $factory;


	/**
	 * @param ProcessFactory $factory
	 */
	public function __construct(ProcessFactory $factory)
	{
		$this->factory = $factory;
	}


	/**
	 * @param $name
	 * @return mixed
	 */
	public function getProcess($name)
	{
		if(!isset($this->processes[$name]))
		{
			$this->processes[$name] = $this->factory->create($name);
		}

		return $this->processes[$name];
	}


	/**
	 * @return ProcessAggregator
	 */
	public function getAggregator()
	{
		return new ProcessAggregator($this->processes);
	}

}
