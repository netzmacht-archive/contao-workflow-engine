<?php

namespace Workflow\Contao\Dca;

use DcaTools\Data\ConfigBuilder;
use DcaTools\Definition;

class Workflow extends Generic
{

	public function initialize($dc)
	{
		parent::initialize($dc);

		$table = $this->model->getProperty('forTable');

		if($table)
		{
			$definition = Definition::getDataContainer($table);
			$palette    = $this->definition->getPalette('default');

			if($definition->get('config/ctable'))
			{
				$palette->addProperty('storeChildren', 'storage', Definition::FIRST);
			}

			if(isset($GLOBALS['TL_WORKFLOW_CONDITIONS'][$table]) && $this->model->getProperty('addCondition'))
			{
				/** @var \Workflow\Contao\WorkflowCondition\WorkflowConditionInterface $condition */
				$condition = $GLOBALS['TL_WORKFLOW_CONDITIONS'][$table];
				$condition::preparePalette($palette);
			}
		}
	}


	/**
	 * @param array $row
	 * @return string
	 */
	public function callbackLabel(array $row)
	{
		$module = isset($GLOBALS['TL_LANG']['MOD'][$row['forModule']]) ? $GLOBALS['TL_LANG']['MOD'][$row['forModule']][0] : $row['forModule'];

		return sprintf('%s <span class="tl_class">%s, %s</span>', $row['title'], $module, $row['forTable']);
	}


	/**
	 * @return array
	 */
	public function getStorageProperties()
	{
		$properties = array();
		$tables = array();
		$table = $this->model->getProperty('forTable');

		if($table)
		{
			$tables = array($table);
		}

		if($this->model->getProperty('storeChildren'))
		{
			$children = Definition::getDataContainer($table)->get('config/ctable') ?: array();
			$tables = array_merge($tables, $children);
		}

		foreach($tables as $table)
		{
			$definition = Definition::getDataContainer($table);

			foreach($definition->getProperties() as $name => $property)
			{
				$properties[$table][specialchars($table .'::'. $name)] = $property->getLabel()[0] ?: $name;
			}
		}

		return $properties;
	}


	/**
	 * @return array
	 */
	public function getColumns()
	{
		$definition = Definition::getDataContainer($this->model->getProperty('forTable'));
		$properties = array();

		foreach($definition->getProperties() as $name => $property)
		{
			$properties[$name] = $property->getLabel()[0] ?: $name;
		}

		return $properties;
	}


	/**
	 * @return array
	 */
	public function getSteps()
	{
		$steps = array();
		$stepsCollection = ConfigBuilder::create($this->manager->getDataProvider('tl_workflow_step'))
			->filterEquals('pid', $this->model->getProperty('process'))
			->field('name')
			->fetchAll();

		foreach($stepsCollection as $step)
		{
			/** @var \DcGeneral\Data\ModelInterface $step */
			$steps[$step->getId()] = $step->getProperty('name');
		}

		return $steps;
	}

}