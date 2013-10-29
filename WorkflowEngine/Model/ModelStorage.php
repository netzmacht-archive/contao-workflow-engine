<?php

namespace WorkflowEngine\Model;

use DcaTools\Model\Filter;
use DcGeneral\Data\DCGE;
use DcGeneral\Data\DriverInterface;
use WorkflowEngine\Entity\Entity;
use WorkflowEngine\Entity\ModelState;
use WorkflowEngine\Validation\ViolationList;


class ModelStorage
{

	/**
	 * @var ModelManager
	 */
	protected $modelManager;


	/**
	 * @var \DcGeneral\Data\DriverInterface
	 */
	protected $driver;


	/**
	 * @param ModelManager $manager
	 * @param $driver
	 */
	public function __construct(ModelManager $manager, DriverInterface $driver)
	{
		$this->modelManager = $manager;
		$this->driver = $driver;
	}


	/**
	 * Returns the current model state.
	 *
	 * @param  ModelInterface                                $model
	 * @param  string                                        $processName
	 * @return \WorkflowEngine\Entity\ModelState
	 */
	public function findCurrentModelState(ModelInterface $model, $processName)
	{
		$filter = Filter::create()
			->addEquals('workflowIdentifier', $model->getWorkflowIdentifier())
			->addEquals('processName', $processName)
			->addEquals('successful', true);

		$config = $this->driver->getEmptyConfig();
		$config->setFilter($filter->getFilter());
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
		$filter = Filter::create()
			->addEquals('workflowIdentifier', $model->getWorkflowIdentifier())
			->addEquals('processName', $processName);

		if($successOnly)
		{
			$filter->addEquals('successful', true);
		}

		$config = $this->driver->getEmptyConfig();
		$config->setFilter($filter->getFilter());
		$config->setSorting(array('createdAt' => DCGE::MODEL_SORTING_ASC));

		$collection = $this->driver->fetchAll($config);
		$newCollection = $this->driver->getEmptyCollection();

		foreach($collection as $model)
		{
			$modelState = new ModelState($model);
			$this->modelManager->persist($model);

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

		$this->modelManager->persist($modelState);
		$this->modelManager->flush($modelState);

		return new ModelState($modelState);
	}

	/**
	 * Delete all model states.
	 *
	 * @param ModelInterface $model
	 * @param string         $processName
	 */
	public function deleteAllModelStates(ModelInterface $model, $processName = null)
	{
		$filter = Filter::create()
			->addEquals('workflowIdentifier', $model->getWorkflowIdentifier());

		if($processName !== null)
		{
			$filter->addEquals('processName', $processName);
		}

		$config = $this->driver->getEmptyConfig();
		$config->setFilter($filter->getFilter());
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
	 * @return \WorkflowEngine\Entity\ModelState
	 */
	public function newModelStateSuccess(ModelInterface $model, $processName, $stepName, $previous = null)
	{
		$modelState = $this->createModelState($model, $processName, $stepName, $previous);
		$modelState->setSuccessful(true);

		$this->modelManager->persist($modelState);
		$this->modelManager->flush($modelState);

		return $modelState;
	}


	/**
	 * Create a new model state.
	 *
	 * @param  ModelInterface                                 $model
	 * @param  string                                         $processName
	 * @param  string                                         $stepName
	 * @param  ModelState                                     $previous
	 * @return \WorkflowEngine\Entity\ModelState
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
		$modelState->setMeta(DCGE::MODEL_IS_CHANGED, true);

		if ($previous instanceof ModelState)
		{
			$modelState->setPrevious($previous);
		}

		return $modelState;
	}
}
