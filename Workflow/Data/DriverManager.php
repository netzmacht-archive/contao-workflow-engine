<?php

namespace Workflow\Data;

use DcGeneral\Contao\BackendBindings;

class DriverManager implements DriverManagerInterface
{

	/**
	 * @var array
	 */
	protected $drivers = array();


	/**
	 * ProviderManager is used for getting a DataProvider when not using the DC_General
	 *
	 * @param $dataContainer
	 * @return \DcGeneral\Data\DriverInterface
	 */
	public function getDataProvider($dataContainer)
	{
		if(!isset($this->drivers[$dataContainer]))
		{
			if(!isset($GLOBALS['TL_DCA'][$dataContainer]))
			{
				BackendBindings::loadLanguageFile($dataContainer);
				BackendBindings::loadDataContainer($dataContainer);
			}

			$config = $GLOBALS['TL_DCA'][$dataContainer]['dca_config'];
			$class = '\DcGeneral\Data\DefaultDriver';

			if(isset($config['data_provider']['default']))
			{
				$config = $config['data_provider']['default'];

				if(array_key_exists('class', $config))
				{
					$class = $config['class'];
				}
			}

			/** @var \DcGeneral\Data\DriverInterface $driver */
			$driver = new $class();
			$driver->setBaseConfig(array('source' => $dataContainer));

			$this->drivers[$dataContainer] = $driver;
		}

		return $this->drivers[$dataContainer];
	}

}
