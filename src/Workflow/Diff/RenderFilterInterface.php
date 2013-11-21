<?php
/**
 * Created by PhpStorm.
 * User: david
 * Date: 21.11.13
 * Time: 09:37
 */

namespace Workflow\Diff;

use DcGeneral\Data\ModelInterface as EntityInterface;

/**
 * Interface RenderFilterInterface
 * A RenderFilter is a helper for rendering the diff of two version
 *
 * @package Workflow\Diff
 */
interface RenderFilterInterface
{

	/**
	 * Set a
	 * @param EntityInterface $entity
	 */
	public function setA(EntityInterface $entity);


	/**
	 * Set b
	 * @param EntityInterface $entity
	 */
	public function setB(EntityInterface $entity);


	/**
	 * @param $propertyName
	 * @return bool
	 */
	public function match($propertyName);


	/**
	 * @param $propertyName
	 * @return array(valueA, valueB)
	 */
	public function filter($propertyName);


	/**
	 * @param $propertyName
	 * @return mixed
	 */
	public function createRenderer($propertyName);

}
