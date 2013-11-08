<?php

namespace Workflow\Entity;

use DcGeneral\Data\ModelInterface;


class ModelState extends Entity
{

	/**
	 * Construct.
	 */
	public function __construct(ModelInterface $model)
	{
		parent::__construct($model);

		$this->setProperty('createdAt', time());
		$this->setProperty('next', array());
	}


	/**
	 * Get workflowIdentifier
	 *
	 * @return string
	 */
	public function getWorkflowIdentifier()
	{
		return $this->getProperty('workflowIdentifier');
	}

	/**
	 * Set workflowIdentifier
	 *
	 * @param string $workflowIdentifier
	 */
	public function setWorkflowIdentifier($workflowIdentifier)
	{
		$this->setProperty('workflowIdentifier', $workflowIdentifier);
	}

	/**
	 * Get processName
	 *
	 * @return string
	 */
	public function getProcessName()
	{
		return $this->getProperty('processName');
	}


	/**
	 * Set processName
	 *
	 * @param string $processName
	 */
	public function setProcessName($processName)
	{
		$this->setProperty('processName', $processName);
	}


	/**
	 * Get stepName
	 *
	 * @return string
	 */
	public function getStepName()
	{
		return $this->getProperty('stepName');
	}


	/**
	 * Set stepName
	 *
	 * @param string $stepName
	 */
	public function setStepName($stepName)
	{
		$this->setProperty('stepName', $stepName);
	}


	/**
	 * Get createdAt
	 *
	 * @return int timestamp
	 */
	public function getCreatedAt()
	{
		return $this->getProperty('createdAt');
	}

	/**
	 * Set createdAt
	 *
	 * @param int $createdAt timestamp
	 */
	public function setCreatedAt($createdAt)
	{
		$this->setProperty('createdAt', $createdAt);
	}


	/**
	 * Get data
	 *
	 * @return array
	 */
	public function getData()
	{
		return deserialize($this->getProperty('data'), true);
	}


	/**
	 * Set data
	 *
	 * @param mixed $data An array or a JSON string
	 */
	public function setData($data)
	{
		if (!is_string($data)) {
			$data = serialize($data);
		}

		$this->setProperty('data', $data);
	}


	/**
	 * Get successful
	 *
	 * @return boolean
	 */
	public function getSuccessful()
	{
		return (boolean) $this->getProperty('successful');
	}


	/**
	 * Set successful
	 *
	 * @param boolean
	 */
	public function setSuccessful($successful)
	{
		$this->setProperty('successful', (boolean) $successful);
	}


	/**
	 * Get errors
	 *
	 * @return array
	 */
	public function getErrors()
	{
		return deserialize($this->getProperty('errors'), true);
	}


	/**
	 * Set errors
	 *
	 * @param string $errors
	 */
	public function setErrors($errors)
	{
		if (!is_string($errors)) {
			$errors = json_encode($errors);
		}

		$this->setProperty('errors', $errors);
	}


	/**
	 * Get previous
	 *
	 * @return \Workflow\Entity\ModelState
	 */
	public function getPrevious()
	{
		return $this->getProperty('previous');
	}


	/**
	 * Set previous
	 *
	 * @param ModelState $state
	 */
	public function setPrevious(ModelState $state)
	{
		$this->setProperty('previous', $state->getId());
	}


	/**
	 * Get next
	 *
	 * @return array
	 */
	public function getNext()
	{
		return $this->getProperty('next');
	}


	/**
	 * Add next
	 *
	 * @param ModelState $state
	 */
	public function addNext(ModelState $state)
	{
		$state->setPrevious($this);

		$next = $this->getProperty('next');
		$next[] = $state;

		$this->setProperty('next', $next);
	}

}
