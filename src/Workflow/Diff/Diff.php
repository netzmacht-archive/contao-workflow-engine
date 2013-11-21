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
		'ignore' => array('tstamp', 'id'),
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
		return static::sameEntities($this->a, $this->b, $this->options['ignore']);
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
		foreach($a as $property => $value)
		{
			if(in_array($property, $ignore))
			{
				continue;
			}

			if($value !== $b->getProperty($property))
			{
				return false;
			}
		}

		return true;
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
