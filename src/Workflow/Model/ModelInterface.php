<?php

namespace Workflow\Model;


/**
 * Interface ModelInterface
 * @package Workflow\Model
 */
interface ModelInterface
{
    /**
     * Returns a unique identifier.
     *
     * @return mixed
     */
    public function getWorkflowIdentifier();


    /**
     * Returns data to store in the ModelState.
     *
     * @return array
     */
    public function getWorkflowData();


	/**
	 * @return \DcGeneral\Data\ModelInterface
	 */
	public function getEntity();

}
