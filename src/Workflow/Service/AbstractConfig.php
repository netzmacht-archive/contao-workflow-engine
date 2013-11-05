<?php

namespace Workflow\Service;


abstract class AbstractConfig implements ConfigInterface
{

	/**
	 * @inheritdoc
	 */
	public static function getIdentifier()
	{
		return static::IDENTIFIER;
	}

	/**
	 * @inheritdoc
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
	 * @inheritdoc
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
	 * @inheritdoc
	 */
	public static function getVersion()
	{
		return static::VERSION;
	}

}
