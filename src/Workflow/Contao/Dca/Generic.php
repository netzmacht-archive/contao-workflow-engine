<?php
/**
 * Created by PhpStorm.
 * User: david
 * Date: 05.11.13
 * Time: 10:40
 */

namespace Workflow\Contao\Dca;


class Generic
{
	protected static $instance;

	public static function getInstance()
	{
		if(!static::$instance)
		{
			static::$instance = new static;
		}

		return static::$instance;
	}


	public function getTables($dc)
	{
		// TODO: support non database data containers
		return array_values(array_diff(\Database::getInstance()->listTables(), $GLOBALS['TL_CONFIG']['workflow_disabledTables']));
	}

} 