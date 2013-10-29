<?php

namespace WorkflowEngine\Flow;

/**
 * Process class.
 *
 * @author Cédric Girard <c.girard@lexik.fr>
 */
class Process extends Node
{
    /**
     * @var array
     */
    protected $steps;

    /**
     * @var string
     */
    protected $startStep;

    /**
     * @var array
     */
    protected $endSteps;

    /**
     * Construct.
     *
     * @param string $name
     * @param array  $steps
     * @param string $startStep
     * @param array  $endSteps
     */
    public function __construct($name, array $steps, $startStep, array $endSteps)
    {
        parent::__construct($name);

        $this->steps     = $steps;
        $this->startStep = $startStep;
        $this->endSteps  = $endSteps;
    }

    /**
     * @return string
     */
    public function __toString()
    {
        return $this->getName();
    }

    /**
     * Get process steps.
     *
     * @return array
     */
    public function getSteps()
    {
        return $this->steps;
    }

    /**
     * Returns a step by its name.
     *
     * @param string $name
     *
     * @return \WorkflowEngine\Flow\Step
     */
    public function getStep($name)
    {
        return $this->steps[$name];
    }

    /**
     * Returns the first step.
     *
     * @return string
     */
    public function getStartStep()
    {
        return $this->startStep;
    }

    /**
     * Returns an array of step name.
     *
     * @return array
     */
    public function getEndSteps()
    {
        return $this->endSteps;
    }
}
