<?php

namespace Workflow\Contao\Dca;


/**
 * Class Workflow
 *
 * @package Workflow\Contao\Dca
 * @author David Molineus <molineus@netzmacht.de>
 */
class Workflow extends Generic
{

	/**
	 * Get all installed workflow types
	 *
	 * @return array
	 */
	public function getWorkflowTypes()
	{
		return array_keys($GLOBALS['TL_WORKFLOWS']);
	}


	/**
	 * Get all supported tables of the workflow
	 *
	 * @return array
	 */
	public function getWorkflowTables()
	{
		$tables   = array();
		$workflow = $this->entity->getProperty('workflow');

		if(isset($GLOBALS['TL_WORKFLOWS'][$workflow]))
		{
			/** @var \Workflow\Controller\WorkflowInterface $class */
			$class = $GLOBALS['TL_WORKFLOWS'][$workflow];
			return $class::getSupportedTables();
		}

		return $tables;
	}

}
