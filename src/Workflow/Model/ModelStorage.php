<?php

namespace Workflow\Model;

use DcaTools\Data\ConfigBuilder;
use DcGeneral\Data\DCGE;
use DcGeneral\Data\DriverInterface;
use Workflow\Entity\Entity;
use Workflow\Entity\ModelState;
use Workflow\Validation\ViolationList;


/**
 * Class ModelStorage
 * @package Workflow\Model
 */
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
		$entity = ConfigBuilder::create($this->driver)
			->filterEquals('workflowIdentifier', $model->getWorkflowIdentifier())
			->filterEquals('processName', $processName)
			->filterEquals('successful', true)
			->sorting('id', DCGE::MODEL_SORTING_DESC)
			->fetch();

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
		$builder = ConfigBuilder::create($this->driver)
			->filterEquals('workflowIdentifier', $model->getWorkflowIdentifier())
			->filterEquals('processName', $processName)
			->sorting('createdAt', DCGE::MODEL_SORTING_ASC);

		if($successOnly)
		{
			$builder->filterEquals('successful', true);
		}

		$collection = $this->driver->getEmptyCollection();

		foreach($builder->fetchAll() as $model)
		{
			$modelState = new ModelState($model);
			$collection->add($modelState);
		}

		return $collection;
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
		$builder = ConfigBuilder::create($this->driver)
			->filterEquals('workflowIdentifier', $model->getWorkflowIdentifier())
			->setIdOnly(true);

		if($processName !== null)
		{
			$builder->filterEquals('processName', $processName);
		}

		foreach($builder->fetchAll() as $id)
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
