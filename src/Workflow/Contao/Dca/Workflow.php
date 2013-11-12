<?php
/**
 * Created by PhpStorm.
 * User: david
 * Date: 29.10.13
 * Time: 23:32
 */

namespace Workflow\Contao\Dca;

use DcaTools\Definition;

class Workflow extends Generic
{

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
	 * @param $dc
	 * @return array
	 */
	public function getStorageProperties($dc)
	{
		$properties = array();
		$tables = array();
		$table = $dc->activeRecord->forTable;

		if($table)
		{
			$tables = array($table);
		}

		if($dc->activeRecord->store_children)
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


	public function getColumns($dc)
	{
		$definition = Definition::getDataContainer($dc->table);
		$properties = array();

		foreach($definition->getProperties() as $name => $property)
		{
			$properties[$name] = $property->getLabel()[0] ?: $name;
		}

		return $properties;
	}

}