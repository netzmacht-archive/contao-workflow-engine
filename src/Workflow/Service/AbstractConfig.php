<?php

namespace Workflow\Service;


abstract class AbstractConfig implements ConfigInterface
{
	const IDENTIFIER = 'abstract';

	const VERSION = 'undefined';

	/**
	 * @return string
	 */
	public static function getName()
	{
		if(isset($GLOBALS['TL_LANG']['workflow']['services'][static::IDENTIFIER]))
		{
			return $GLOBALS['TL_LANG']['workflow']['services'][static::IDENTIFIER][0];
		}

		return static::IDENTIFIER;
	}


	/**
	 * @return string
	 */
	public static function getDescription()
	{
		if(isset($GLOBALS['TL_LANG']['workflow']['services'][static::IDENTIFIER]))
		{
			return $GLOBALS['TL_LANG']['workflow']['services'][static::IDENTIFIER][1];
		}

		return '';
	}


	/**
	 * @return string
	 */
	public static function getVersion()
	{
		return static::VERSION;
	}

} 