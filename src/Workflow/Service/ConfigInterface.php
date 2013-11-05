<?php
/**
 * Created by PhpStorm.
 * User: david
 * Date: 01.11.13
 * Time: 23:20
 */

namespace Workflow\Service;


interface ConfigInterface
{
	/**
	 * @return string
	 */
	public static function getName();


	/**
	 * @return string
	 */
	public static function getDescription();


	/**
	 * @return string
	 */
	public static function getVersion();


	/**
	 * @return array
	 */
	public static function getEvents();


	/**
	 * Get config property
	 * @return array
	 */
	public static function getProperties();


	/**
	 * @param array $states
	 * @return array
	 */
	public static function getStates(array $states);


	/**
	 * @param array $steps
	 * @return steps
	 */
	public static function getSteps(array $steps);

}