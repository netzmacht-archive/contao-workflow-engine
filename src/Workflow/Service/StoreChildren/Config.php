<?php
/**
 * Created by PhpStorm.
 * User: david
 * Date: 01.11.13
 * Time: 23:19
 */

namespace Workflow\Service\StoreChildren;

use Workflow\Service\AbstractConfig;

class Config extends AbstractConfig
{

	/**
	 * @return array
	 */
	public static function getEvents()
	{
		return array
		(
			'get_data',
		);
	}


	/**
	 * Get config property
	 * @return array
	 */
	public static function getProperties()
	{
		return array
		(
			'store_children',
		);
	}


	/**
	 * @param array $states
	 * @return array
	 */
	public static function getStates(array $states)
	{
		return $states;
	}


	/**
	 * @param array $steps
	 * @return array
	 */
	public static function getSteps(array $steps)
	{
		return $steps;
	}

}
