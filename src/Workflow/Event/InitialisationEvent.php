<?php
/**
 * Created by PhpStorm.
 * User: david
 * Date: 04.11.13
 * Time: 10:58
 */

namespace Workflow\Event;


use Symfony\Component\EventDispatcher\Event;
use Workflow\Entity\ModelState;
use Workflow\Model\ModelInterface;

/**
 * Class InitialisationEvent
 * @package Workflow\Event
 */
class InitialisationEvent extends Event
{

	/**
	 * @var \Workflow\Model\ModelInterface
	 */
	protected $model;

	/**
	 * @var \Workflow\Entity\ModelState
	 */
	protected $state;


	/**
	 * @param ModelInterface $model
	 * @param ModelState $state
	 */
	public function __construct(ModelInterface $model, ModelState $state)
	{
		$this->model = $model;
		$this->state = $state;
	}


	/**
	 * @return ModelInterface
	 */
	public function getModel()
	{
		return $this->model;
	}


	/**
	 * @return ModelState
	 */
	public function getState()
	{
		return $this->state;
	}

}
