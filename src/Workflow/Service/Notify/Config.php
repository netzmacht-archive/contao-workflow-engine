<?php
/**
 * Created by PhpStorm.
 * User: david
 * Date: 01.11.13
 * Time: 23:13
 */

namespace Workflow\Service\Notify;

use Workflow\Service\AbstractConfig;


class Config extends AbstractConfig
{
	const IDENTIFIER = 'notify';

	const VERSION = '1.0';


	/**
	 * Get config properties
	 *
	 * @return array
	 */
	public static function getProperties()
	{
		return array
		(
			'notify_email',
			'notify_users',
		);
	}


	public static function getEvents()
	{
		return array
		(
			'reached',
			//'validate',
			'validation_fail',
		);
	}


	/**
	 * @param array $states
	 * @return array|null
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
