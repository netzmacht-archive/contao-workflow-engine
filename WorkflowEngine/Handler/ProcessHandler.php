<?php

namespace WorkflowEngine\Handler;

use WorkflowEngine\Event\SecurityEvent;
use WorkflowEngine\Registry;
use WorkflowEngine\Entity\ModelState;
use WorkflowEngine\Event\StepEvent;
use WorkflowEngine\Event\ValidateStepEvent;
use WorkflowEngine\Exception\WorkflowException;
use WorkflowEngine\Exception\AccessDeniedException;
use WorkflowEngine\Flow\Step;
use WorkflowEngine\Flow\Process;
use WorkflowEngine\Model\ModelStorage;
use WorkflowEngine\Model\ModelInterface;
use WorkflowEngine\Validation\Violation;
use WorkflowEngine\Validation\ViolationList;

/**
 * Contains all logic to handle a process and its steps.
 */
class ProcessHandler implements ProcessHandlerInterface
{
    /**
     * @var Process
     */
    protected $process;

    /**
     * @var ModelStorage
     */
    protected $storage;

    /**
     * @var Registry
     */
    protected $registry;


	/**
	 * @param Process $process
	 * @param Registry $registry
	 */
	public function __construct(Process $process, Registry $registry)
    {
        $this->process = $process;
	    $this->registry = $registry;
        $this->storage = $registry->getStateStorage();
    }


    /**
     * {@inheritdoc}
     */
    public function start(ModelInterface $model)
    {
        $modelState = $this->storage->findCurrentModelState($model, $this->process->getName());

        if ($modelState instanceof ModelState) {
            throw new WorkflowException(sprintf('The given model has already started the "%s" process.', $this->process->getName()));
        }

        $step = $this->getProcessStep($this->process->getStartStep());

        return $this->reachStep($model, $step);
    }


    /**
     * {@inheritdoc}
     */
    public function reachNextState(ModelInterface $model, $stateName)
    {
        $currentModelState = $this->storage->findCurrentModelState($model, $this->process->getName());

        if ( ! ($currentModelState instanceof ModelState) ) {
            throw new WorkflowException(sprintf('The given model has not started the "%s" process.', $this->process->getName()));
        }

        $currentStep = $this->getProcessStep($currentModelState->getStepName());

        if ( !$currentStep->hasNextState($stateName) ) {
            throw new WorkflowException(sprintf('The step "%s" does not contain any next state named "%s".', $currentStep->getName(), $stateName));
        }

	    $dispatcher = $this->registry->getEventDispatcher($model->getEntity()->getProviderName());

	    /** @var Step $step */
        $state = $currentStep->getNextState($stateName);
        $step = $state->getTarget();

        // pre validations
        $event = new ValidateStepEvent($step, $model, new ViolationList());
        $eventName = sprintf('%s.%s.%s.pre_validation', $this->process->getName(), $currentStep->getName(), $stateName);
	    $dispatcher->dispatch($eventName, $event);

        $modelState = null;

        if (count($event->getViolationList()) > 0) {
            $modelState = $this->storage->newModelStateError($model, $this->process->getName(), $step->getName(), $event->getViolationList(), $currentModelState);

            $eventName = sprintf('%s.%s.%s.pre_validation_fail', $this->process->getName(), $currentStep->getName(), $stateName);
	        $dispatcher->dispatch($eventName, new StepEvent($step, $model, $modelState));
        } else {
            $modelState = $this->reachStep($model, $step, $currentModelState);
        }

        return $modelState;
    }


    /**
     * Reach the given step.
     *
     * @param  ModelInterface $model
     * @param  Step           $step
     * @param  ModelState     $currentModelState
     * @return ModelState
     */
    protected function reachStep(ModelInterface $model, Step $step, ModelState $currentModelState = null)
    {
        try {
            $this->checkCredentials($model, $step);
        }
        catch (AccessDeniedException $e)
        {
	        $violationList = new ViolationList();
	        $violationList->add(new Violation($e->getMessage()));

            return $this->storage->newModelStateError($model, $this->process->getName(), $step->getName(), $violationList, $currentModelState);
        }

	    $dispatcher = $this->registry->getEventDispatcher($model->getEntity()->getProviderName());

        $event = new ValidateStepEvent($step, $model, new ViolationList());
        $eventName = sprintf('%s.%s.validate', $this->process->getName(), $step->getName());
	    $dispatcher->dispatch($eventName, $event);

        if (0 === count($event->getViolationList())) {
            $modelState = $this->storage->newModelStateSuccess($model, $this->process->getName(), $step->getName(), $currentModelState);

            // update model status
            if ($step->hasModelStatus()) {
                list($method, $status) = $step->getModelStatus();
                $model->$method($status);
            }

            $eventName = sprintf('%s.%s.reached', $this->process->getName(), $step->getName());
	        $dispatcher->dispatch($eventName, new StepEvent($step, $model, $modelState));
        } else {
            $modelState = $this->storage->newModelStateError($model, $this->process->getName(), $step->getName(), $event->getViolationList(), $currentModelState);

            $eventName = sprintf('%s.%s.validation_fail', $this->process->getName(), $step->getName());
	        $dispatcher->dispatch($eventName, new StepEvent($step, $model, $modelState));

            if ($step->getOnInvalid()) {
                $step = $this->getProcessStep($step->getOnInvalid());
                $modelState = $this->reachStep($model, $step);
            }
        }

        return $modelState;
    }


    /**
     * {@inheritdoc}
     */
    public function getCurrentState(ModelInterface $model)
    {
        return $this->storage->findCurrentModelState($model, $this->process->getName());
    }


    /**
     * {@inheritdoc}
     */
    public function isProcessComplete(ModelInterface $model)
    {
        $state = $this->getCurrentState($model);

        return ( $state->getSuccessful() && in_array($state->getStepName(), $this->process->getEndSteps()) );
    }


    /**
     * {@inheritdoc}
     */
    public function getAllStates(ModelInterface $model, $successOnly = true)
    {
        return $this->storage->findAllModelStates($model, $this->process->getName(), $successOnly);
    }


    /**
     * Returns a step by its name.
     *
     * @param  string $stepName
     * @return Step
     *
     * @throws WorkflowException
     */
    protected function getProcessStep($stepName)
    {
        $step = $this->process->getStep($stepName);

        if (! ($step instanceof Step)) {
            throw new WorkflowException(sprintf('Can\'t find step named "%s" in process "%s".', $stepName, $this->process->getName()));
        }

        return $step;
    }


    /**
     * Check if the user is allowed to reach the step.
     *
     * @param  ModelInterface $model
     * @param  Step $step
     * @throws AccessDeniedException
     */
    protected function checkCredentials(ModelInterface $model, Step $step)
    {
	    // auto grant access if no roles are defined
	    $grant = count($step->getRoles());
	    $event = new SecurityEvent($step, $model, $grant);

	    $this->registry->getEventDispatcher($model->getEntity()->getProviderName())->dispatch('check_credentials', $event);

        if (!$event->isGranted())
        {
            throw new AccessDeniedException($step->getName());
        }
    }
}
