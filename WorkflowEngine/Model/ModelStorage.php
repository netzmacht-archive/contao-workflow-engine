<?php

namespace WorkflowEngine\Model;

use DcGeneral\Data\DCGE;
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
	 * @var string
	 */
	protected $stateTable = 'tl_workflow_state';


	/**
	 * @param ModelManager $manager
	 * @param $providerName
	 */
	public function __construct(ModelManager $manager, $providerName)
    {
        $this->modelManager = $manager;
	    $this->driver = $manager->getDataProvider($providerName);
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
	    $driver = $this->modelManager->getDataProvider($this->stateTable);

	    $filter = Filter::create()
		    ->addEquals('workflowIdentifier', $model->getWorkflowIdentifier())
	        ->addEquals('processName', $processName)
	        ->addEquals('successful', true);

	    $config = $driver->getEmptyConfig();
	    $config->setFilter($filter);
	    $config->setSorting(array('id', DCGE::MODEL_SORTING_DESC));

        return $driver->fetch($config);
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
	    $driver = $this->modelManager->getDataProvider($this->stateTable);

	    $filter = Filter::create()
		    ->addEquals('workflowIdentifier', $model->getWorkflowIdentifier())
		    ->addEquals('processName', $processName);

	    if($successOnly)
	    {
		    $filter->addEquals('successful', true);
	    }

	    $config = $driver->getEmptyConfig();
	    $config->setFilter($filter);
	    $config->setSorting('createdAt', DCGE::MODEL_SORTING_ASC);

	    return $driver->fetchAll($config);
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
	    $filter = Filter::create()
		    ->addEquals('workflowIdentifier', $model->getWorkflowIdentifier());

	    if($processName !== null)
	    {
		    $filter->addEquals('processName', $processName);
	    }

	    $driver = $this->modelManager->getDataProvider($this->stateTable);
	    $config = $driver->getEmptyConfig();
	    $config->setFilter($filter);
	    $config->setIdOnly(true);

	    foreach($driver->fetchAll($config) as $id)
	    {
		    $driver->delete($id);
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
	    $driver = $this->modelManager->getDataProvider($this->stateTable);

        $modelState = new ModelState($driver->getEmptyModel());
        $modelState->setWorkflowIdentifier($model->getWorkflowIdentifier());
        $modelState->setProcessName($processName);
        $modelState->setStepName($stepName);
        $modelState->setData($model->getWorkflowData());

        if ($previous instanceof ModelState)
        {
            $modelState->setPrevious($previous);
        }

        return $modelState;
    }
}
