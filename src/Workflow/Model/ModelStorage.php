<?php

namespace Workflow\Model;

use DcaTools\Model\FilterBuilder;
use DcGeneral\Data\DCGE;
use DcGeneral\Data\DriverInterface;
use Workflow\Entity\Entity;
use Workflow\Entity\ModelState;
use Workflow\Validation\ViolationList;


class ModelStorage
{

	/**
	 * @var \DcGeneral\Data\DriverInterface
	 */
	protected $driver;


	/**
	 * @param $driver
	 */
	public function __construct(DriverInterface $driver)
	{
		$this->driver = $driver;
	}


	/**
	 * Returns the current model state.
	 *
	 * @param  ModelInterface                                $model
	 * @param  string                                        $processName
	 * @return \Workflow\Entity\ModelState
	 */
	public function findCurrentModelState(ModelInterface $model, $processName)
	{
		$builder = FilterBuilder::create()
			->addEquals('workflowIdentifier', $model->getWorkflowIdentifier())
			->addEquals('processName', $processName)
			->addEquals('successful', true);

		$config = $builder->getConfig($this->driver);
		$config->setSorting(array('id' => DCGE::MODEL_SORTING_DESC));

		$entity =  $this->driver->fetch($config);

		if($entity === null)
		{
			return null;
		}

		return new ModelState(new Entity($entity));
	}


	/**
	 * Returns all model states.
	 *
	 * @param  ModelInterface $model
	 * @param  string         $processName
	 * @param  bool         $successOnly
	 *
	 * @return \DcGeneral\Data\CollectionInterface
	 */
	public function findAllModelStates(ModelInterface $model, $processName, $successOnly = true)
	{
		$builder = FilterBuilder::create()
			->addEquals('workflowIdentifier', $model->getWorkflowIdentifier())
			->addEquals('processName', $processName);

		if($successOnly)
		{
			$builder->addEquals('successful', true);
		}

		$config = $builder->getConfig($this->driver);
		$config->setSorting(array('createdAt' => DCGE::MODEL_SORTING_ASC));

		$collection = $this->driver->fetchAll($config);
		$newCollection = $this->driver->getEmptyCollection();

		foreach($collection as $model)
		{
			$modelState = new ModelState($model);
			$newCollection->add($modelState);
		}

		return $newCollection;
	}


	/**
	 * Create a new invalid model state.
	 *
	 * @param ModelInterface  $model
	 * @param string          $processName
	 * @param string          $stepName
	 * @param ViolationList   $violationList
	 * @param null|ModelState $previous
	 *
	 * @return ModelState
	 */
	public function newModelStateError(ModelInterface $model, $processName, $stepName, ViolationList $violationList, $previous = null)
	{
		$modelState = $this->createModelState($model, $processName, $stepName, $previous);
		$modelState->setSuccessful(false);
		$modelState->setErrors($violationList->toArray());

		$this->driver->save($modelState);

		return $modelState;
	}

	/**
	 * Delete all model states.
	 *
	 * @param ModelInterface $model
	 * @param string         $processName
	 */
	public function deleteAllModelStates(ModelInterface $model, $processName = null)
	{
		$builder = FilterBuilder::create()
			->addEquals('workflowIdentifier', $model->getWorkflowIdentifier());

		if($processName !== null)
		{
			$builder->addEquals('processName', $processName);
		}

		$config = $builder->getConfig($this->driver);
		$config->setIdOnly(true);

		foreach($this->driver->fetchAll($config) as $id)
		{
			$this->driver->delete($id);
		}
	}


	/**
	 * Create a new successful model state.
	 *
	 * @param  ModelInterface                                 $model
	 * @param  string                                         $processName
	 * @param  string                                         $stepName
	 * @param  ModelState                                     $previous
	 * @return \Workflow\Entity\ModelState
	 */
	public function newModelStateSuccess(ModelInterface $model, $processName, $stepName, $previous = null)
	{
		$modelState = $this->createModelState($model, $processName, $stepName, $previous);
		$modelState->setSuccessful(true);

		$this->driver->save($modelState);
		return $modelState;
	}


	/**
	 * Create a new model state.
	 *
	 * @param  ModelInterface                                 $model
	 * @param  string                                         $processName
	 * @param  string                                         $stepName
	 * @param  ModelState                                     $previous
	 * @return \Workflow\Entity\ModelState
	 */
	protected function createModelState(ModelInterface $model, $processName, $stepName, $previous = null)
	{
		$modelState = new ModelState($this->driver->getEmptyModel());
		$modelState->setWorkflowIdentifier($model->getWorkflowIdentifier());
		$modelState->setProcessName($processName);
		$modelState->setStepName($stepName);
		$modelState->setData($model->getWorkflowData());
		$modelState->setProperty(DCGE::MODEL_PTABLE, $model->getEntity()->getProviderName());
		$modelState->setProperty(DCGE::MODEL_PID, $model->getEntity()->getId());
		$modelState->setProperty('tstamp', time());

		if ($previous instanceof ModelState)
		{
			$modelState->setPrevious($previous);
		}

		return $modelState;
	}
}
