<?php

namespace Workflow\Diff;

use DcaTools\Definition;
use DcGeneral\Data\ModelInterface as EntityInterface;


/**
 * Class ContaoRenderFilter applies render logic which is used for version diff to the entities
 *
 * @package Workflow\Diff
 */
class ContaoRenderFilter implements RenderFilterInterface
{

	/**
	 * @var EntityInterface
	 */
	protected $a;

	/**
	 * @var EntityInterface
	 */
	protected $b;

	/**
	 * @var \DcaTools\Definition\DataContainer
	 */
	protected $definition;


	/**
	 * @param EntityInterface $entity
	 */
	public function setA(EntityInterface $entity)
	{
		$this->a          = $entity;
		$this->definition = Definition::getDataContainer($entity->getProviderName());
	}


	/**
	 * @param EntityInterface $entity
	 */
	public function setB(EntityInterface $entity)
	{
		$this->b = $entity;
	}


	/**
	 * @param $propertyName
	 * @return bool
	 */
	public function match($propertyName)
	{
		if(!$this->definition->hasProperty($propertyName))
		{
			return false;
		}

		$property = $this->definition->getProperty($propertyName);

		if (!$property->getWidgetType() ||
			$property->getWidgetType() == 'password' ||
			$property->get('eval/doNotShow') ||
			$property->get('eval/hideInput'))
		{
			return false;
		}

		return true;
	}


	/**
	 * @param $propertyName
	 * @return array(valueA, valueB)
	 */
	public function filter($propertyName)
	{
		$a = $this->a->getProperty($propertyName);
		$b = $this->b->getProperty($propertyName);

		$property = $this->definition->getProperty($propertyName);

		// Convert serialized arrays into strings
		if (is_array(($tmp = deserialize($b))) && !is_array($b))
		{
			$b = $this->implodeRecursive($tmp);
		}
		if (is_array(($tmp = deserialize($a))) && !is_array($a))
		{
			$a = $this->implodeRecursive($tmp);
		}
		unset($tmp);

		// Convert date fields
		if(in_array($property->get('eval/rgxp'), array('date', 'time', 'datim')))
		{
			$format = $property->get('eval/rgxp') . 'Format';

			$a = \Date::parse($GLOBALS['TL_CONFIG'][$format], $a ?: '');
			$b     = \Date::parse($GLOBALS['TL_CONFIG'][$format], $b ?: '');
		}

		// Convert strings into arrays
		if (!is_array($b))
		{
			$b = explode("\n", $b);
		}
		if (!is_array($a))
		{
			$a = explode("\n", $a);
		}

		return array($a, $b);
	}


	/**
	 * Implode a multi-dimensional array recursively
	 * @param mixed
	 * @return string
	 */
	public static function implodeRecursive($var)
	{
		if (!is_array($var))
		{
			return $var;
		}
		elseif (!is_array(current($var)))
		{
			return implode(', ', $var);
		}
		else
		{
			$buffer = '';

			foreach ($var as $k=>$v)
			{
				$buffer .= $k . ": " . static::implodeRecursive($v) . "\n";
			}

			return trim($buffer);
		}
	}


	/**
	 * @param $propertyName
	 * @return mixed
	 */
	public function createRenderer($propertyName)
	{
		$field = $this->definition->getProperty($propertyName)->get('label/0') ?: $propertyName;
		return new \Diff_Renderer_Html_Contao(array('field'=>$field));
	}

}
