<?php

namespace Workflow\Diff;

use DcaTools\Definition;
use DcGeneral\Data\ModelInterface as EntityInterface;

/**
 * Class Diff
 * @package Workflow\Diff
 * @author David Molineus <molineus@netzmacht.de>
 */
class Diff
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
	 * @var array
	 */
	protected $options = array
	(
		'ignore' => array('tstamp'),
	);


	/**
	 * @param array $options
	 */
	public function __construct(array $options=array())
	{
		$this->options = array_merge($this->options, $options);
	}


	/**
	 * @param EntityInterface $entity
	 */
	public function setA(EntityInterface $entity)
	{
		$this->a = $entity;
	}


	/**
	 * @param EntityInterface $entity
	 */
	public function setB(EntityInterface $entity)
	{
		$this->b = $entity;
	}


	/**
	 * Check if there are any changes
	 *
	 * @return bool
	 */
	public function hasChanges()
	{
		return !static::sameEntities($this->a, $this->b, $this->options['ignore']);
	}


	/**
	 * Get Changed properties from B
	 *
	 * @return array
	 */
	public function getChangedProperties()
	{
		$properties = array();
		$all        = $this->a->getPropertiesAsArray();

		// ATTENTION: ID is not compared because the
		foreach($this->b as $property => $value) {
			unset($all[$property]);

			if(in_array($property, $this->options['ignore'])) {
				continue;
			}

			if(!$this->sameValue($value, $this->a->getProperty($property))) {
				$properties[$property] = $value;
			}
		}

		if(!in_array('id', $this->options['ignore'])) {
			unset($all['id']);

			if(!$this->sameValue($this->b->getId(), $this->a->getId())) {
				$properties['id'] = $this->b->getId();
			}
		}

		// also add properties which existed in a but not in b anymore
		if(count($all)) {
			foreach(array_keys($all) as $key) {
				if(!in_array($key, $this->options['ignore'])) {
					$properties[$key] = null;
				}
			}
		}

		return $properties;
	}


	/**
	 * Consider whether 2 entities are same
	 *
	 * @param EntityInterface $a
	 * @param EntityInterface $b
	 * @param array $ignore fields to ignore
	 *
	 * @return bool
	 */
	public static function sameEntities(EntityInterface $a, EntityInterface $b, array $ignore=array())
	{
		// precondition not the same if different number of properties
		if(count($a->getPropertiesAsArray()) != count($b->getPropertiesAsArray())) {
			return false;
		}

		foreach($a as $property => $value) {
			if(in_array($property, $ignore)) {
				continue;
			}

			if(!static::sameValue($value, $b->getProperty($property))) {
				return false;
			}
		}

		return true;
	}


	/**
	 * Compare 2 values
	 *
	 * @param $a
	 * @param $b
	 * @return bool
	 */
	public static function sameValue($a, $b)
	{
		if(is_array($a) || is_object($a)) {
			$a = serialize($a);
		}

		if(is_array($b) || is_object($b)) {
			$b = serialize($b);
		}

		return $a == $b;
	}


	/**
	 * Render the diff using the Diff library
	 *
	 * @param RenderFilterInterface $filter
	 *
	 * @return string
	 */
	public function render(RenderFilterInterface $filter)
	{
		$buffer = '';

		// Include the PhpDiff library
		require_once TL_ROOT . '/system/modules/core/vendor/phpdiff/Diff.php';
		require_once TL_ROOT . '/system/modules/core/vendor/phpdiff/Diff/Renderer/Html/Contao.php';

		foreach($this->a as $property => $value)
		{
			if($filter->match($property))
			{
				list($a, $b) = $filter->filter($property);

				$diff    = new \Diff($a, $b);
				$buffer .= $diff->render($filter->createRenderer($property));
			}
		}

		return $buffer;
	}

}
