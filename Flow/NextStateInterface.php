<?php

namespace Lexik\Bundle\WorkflowBundle\Flow;

/**
 * Next state inerface.
 *
 * @author Cédric Girard <c.girard@lexik.fr>
 */
interface NextStateInterface
{
    const TARGET_TYPE_STEP    = 'step';
    const TARGET_TYPE_PROCESS = 'process';

    /**
     * Returns the state name.
     *
     * @return string
     */
    public function getName();

    /**
     * Returns the state target type.
     *
     * @return string
     */
    public function getTargetType();

    /**
     * Returns the state target.
     *
     * @return NodeInterface
     */
    public function getTarget();
}
