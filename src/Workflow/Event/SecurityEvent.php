<?php

namespace Workflow\Event;

use Symfony\Component\EventDispatcher\Event;
use Workflow\Flow\Step;
use Workflow\Model\ModelInterface;
use Workflow\Validation\Violation;
use Workflow\Validation\ViolationList;

class SecurityEvent extends Event
{

	/**
	 * @var bool
	 */
	protected $granted;


	/**
	 * @var \Workflow\Flow\Step
	 */
	protected $step;


	/**
	 * @var ModelInterface
	 */
	protected $model;


	/**
	 * @var ViolationList
	 */
	protected $errors;

	/**
	 * @var string Process name
	 */
	protected $processName;


	/**
	 * @param string $processName
	 * @param Step $step
	 * @param ModelInterface $model
	 * @param bool $granted
	 */
	public function __construct($processName, Step $step, ModelInterface $model, $granted=false)
	{
		$this->processName = $processName;
		$this->model = $model;
		$this->step = $step;
		$this->granted = $granted;
		$this->errors = new ViolationList();
	}


	/**
	 * @return Step
	 */
	public function getStep()
	{
		return $this->step;
	}


	/**
	 * @return ModelInterface
	 */
	public function getModel()
	{
		return $this->model;
	}


	/**
	 * @return mixed
	 */
	public function isGranted()
	{
		return $this->granted;
	}


	/**
	 * @return string
	 */
	public function getProcessName()
	{
		return $this->processName;
	}


	/**
	 * Deny access
	 */
	public function grantAccess($granted, $stopPropagation=true)
	{
		$this->granted = (bool) $granted;

		if(!$granted && $stopPropagation)
		{
			$this->stopPropagation();
		}
	}


	/**
	 * @return ViolationList
	 */
	public function getErrors()
	{
		return $this->errors;
	}


	/**
	 * @param $strMessage
	 */
	public function addError($strMessage)
	{
		$this->errors->add(new Violation($strMessage));
	}

}
