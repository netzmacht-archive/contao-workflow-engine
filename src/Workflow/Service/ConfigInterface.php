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
	 * Get Identifier
	 *
	 * Identifier is a unique name for a workflow service
	 *
	 * @return mixed
	 */
	public static function getIdentifier();


	/**
	 * Get the name, which can be translated to the current locale
	 *
	 * @return string
	 */
	public static function getName();


	/**
	 * Get description which explains what the service does. Can be translated
	 *
	 * @return string
	 */
	public static function getDescription();


	/**
	 * Get installed version
	 *
	 * @return string
	 */
	public static function getVersion();


	/**
	 * get events the service can listen to
	 *
	 * @return array
	 */
	public static function getEvents();


	/**
	 * Get config properties which can be set for the service
	 *
	 * Config properties are grouped by the legend named. Its recommend
	 * to use scope and config as legends
	 *
	 * example:
	 * return array
	 *  (
	 *      'scope'  => array('events'),
	 *      'config' => array('notify_email', 'notify_users'),
	 *  ),
	 *
	 * @return array
	 */
	public static function getConfigProperties();

}
