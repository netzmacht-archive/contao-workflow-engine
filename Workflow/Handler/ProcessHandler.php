<?php

namespace Workflow\Handler;

use Workflow\Handler\Environment;
use Workflow\Event\SecurityEvent;
use Workflow\Entity\ModelState;
use Workflow\Event\StepEvent;
use Workflow\Event\ValidateStepEvent;
use Workflow\Exception\WorkflowException;
use Workflow\Exception\AccessDeniedException;
use Workflow\Flow\Step;
use Workflow\Flow\Process;
use Workflow\Model\ModelStorage;
use Workflow\Model\ModelInterface;
use Workflow\Validation\Violation;
use Workflow\Validation\ViolationList;

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
	 * @var Environment
	 */
	protected $environment;


	/**
	 * @param Process $process
	 * @param Environment $environment
	 * @param ModelStorage $storage
	 */
	public function __construct(Process $process, Environment $environment, ModelStorage $storage)
    {
	    $this->storage = $storage;
        $this->process = $process;
	    $this->environment = $environment;
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

	    /** @var Step $step */
        $state = $currentStep->getNextState($stateName);
        $step = $state->getTarget();

        // pre validations
        $event = new ValidateStepEvent($step, $model, new ViolationList());
        $eventName = sprintf('workflow.%s.%s.%s.%s.pre_validation',
	        $model->getEntity()->getProviderName(),
	        $this->process->getName(),
	        $currentStep->getName(),
	        $stateName
        );

	    $this->environment->getEventDispatcher()->dispatch($eventName, $event);

        $modelState = null;

        if (count($event->getViolationList()) > 0) {
            $modelState = $this->storage->newModelStateError($model, $this->process->getName(), $step->getName(), $event->getViolationList(), $currentModelState);

            $eventName = sprintf('workflow.%s.%s.%s.%s.pre_validation_fail',
	            $model->getEntity()->getProviderName(),
	            $this->process->getName(),
	            $currentStep->getName(),
	            $stateName
            );

	        $this->environment->getEventDispatcher()->dispatch($eventName, new StepEvent($step, $model, $modelState));
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

        $event = new ValidateStepEvent($step, $model, new ViolationList());
        $eventName = sprintf('workflow.%s.%s.%s.validate', $model->getEntity()->getProviderName(), $this->process->getName(), $step->getName());
	    $this->environment->getEventDispatcher()->dispatch($eventName, $event);

        if (0 === count($event->getViolationList()))
        {
            $modelState = $this->storage->newModelStateSuccess($model, $this->process->getName(), $step->getName(), $currentModelState);

            // update model status
            if ($step->hasModelStatus()) {
                list($method, $status) = $step->getModelStatus();
                $model->$method($status);
            }

            $eventName = sprintf('workflow.%s.%s.%s.reached', $model->getEntity()->getProviderName(), $this->process->getName(), $step->getName());
	        $this->environment->getEventDispatcher()->dispatch($eventName, new StepEvent($step, $model, $modelState));
        }
        else
        {
            $modelState = $this->storage->newModelStateError($model, $this->process->getName(), $step->getName(), $event->getViolationList(), $currentModelState);

            $eventName = sprintf('workflow.%s.%s.%s.validation_fail', $model->getEntity()->getProviderName(), $this->process->getName(), $step->getName());
	        $this->environment->getEventDispatcher()->dispatch($eventName, new StepEvent($step, $model, $modelState));

            if ($step->getOnInvalid()) {
                $step = $this->getProcessStep($step->getOnInvalid());
                $modelState = $this->reachStep($model, $step);
            }
        }

	    $this->environment->setCurrentState($modelState);

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
	    $grant = (count($step->getRoles()) == 0);
	    $event = new SecurityEvent($step, $model, $grant);

	    $this->environment->getEventDispatcher()->dispatch('workflow.check_credentials', $event);

        if (!$event->isGranted())
        {
            throw new AccessDeniedException($step->getName());
        }
    }
}
