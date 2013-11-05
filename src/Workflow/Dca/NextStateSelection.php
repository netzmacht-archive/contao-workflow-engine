<?php
/**
 * Created by PhpStorm.
 * User: david
 * Date: 01.11.13
 * Time: 15:12
 */

namespace Workflow\Dca;


class NextStateSelection
{
	protected $value;

	protected $dc;

	protected static $instance;

	protected $index = 0;


	public static function getInstance()
	{
		if(!static::$instance)
		{
			static::$instance = new static();
		}

		return static::$instance;
	}


	public function initialize($value, $dc)
	{
		$this->value = deserialize($value, true);
		$this->dc = $dc;

		return $value;
	}

	public function getTargets()
	{
		$targets = array();

		if($this->value[$this->index]['type'] == 'step' || !isset($this->value[$this->index]))
		{
			$result = \Database::getInstance()
				->prepare('SELECT id,name FROM tl_workflow_step WHERE pid=?')
				->execute($this->dc->activeRecord->pid);

			while($result->next())
			{
				$targets[$result->id] = $result->name;
			}
		}
		elseif($this->value[$this->index]['type'] == 'step')
		{
			$result = \Database::getInstance()
				->prepare('SELECT id,name FROM tl_workflow_process WHERE pid!=?')
				->execute($this->dc->activeRecord->pid);

			while($result->next())
			{
				$targets[$result->id] = $result->name;
			}
		}

		$this->index++;

		if(!isset($this->value[$this->index]))
		{
			$this->index = 0;
		}

		return $targets;
	}

}
