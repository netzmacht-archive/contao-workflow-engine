<?php
/**
 * Created by PhpStorm.
 * User: david
 * Date: 04.11.13
 * Time: 08:19
 */

namespace WorkflowEngine\Data;


interface DriverManagerInterface
{

	/**
	 * @param $name
	 * @return \DcGeneral\Data\DriverInterface
	 */
	public function getDataProvider($name);

} 