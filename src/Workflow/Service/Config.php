<?php

namespace Workflow\Service;

/**
 * Class Config
 * @package Workflow\Service
 */
class Config
{

	/**
	 * @var array
	 */
	protected $config;


	/**
	 * @param array $config
	 */
	public function __construct(array $config)
	{
		$this->config = $config;
	}


	/**
	 * Get Identifier
	 *
	 * Identifier is a unique name for a workflow service
	 *
	 * @return mixed
	 */
	public function getIdentifier()
	{
		return $this->config['identifier'];
	}


	/**
	 * Get the name, which can be translated to the current locale
	 *
	 * @return string
	 */
	public function getName()
	{
		if(isset($GLOBALS['TL_LANG']['workflow']['services'][$this->getIdentifier()]))
		{
			return $GLOBALS['TL_LANG']['workflow']['services'][$this->getIdentifier()][0];
		}

		return $this->getIdentifier();
	}


	/**
	 * Get description which explains what the service does. Can be translated
	 *
	 * @return string
	 */
	public function getDescription()
	{
		if(isset($GLOBALS['TL_LANG']['workflow']['services'][$this->getIdentifier()]))
		{
			return $GLOBALS['TL_LANG']['workflow']['services'][$this->getIdentifier()][1];
		}

		return '';
	}


	/**
	 * Get installed version
	 *
	 * @return string
	 */
	public function getVersion()
	{
		return $this->config['version'];
	}


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
	public function getConfigProperties()
	{
		return $this->config['properties'];
	}


	/**
	 * Get events the service can listen to
	 *
	 * @return array
	 */
	public function getEvents()
	{
		return $this->config['events'];
	}

}
