<?php
/**
 * Created by PhpStorm.
 * User: david
 * Date: 01.11.13
 * Time: 16:07
 */

namespace Workflow\Contao\Dca;

use DcaTools\Helper\Formatter;

class Step
{
	protected $process;


	public function saveStart($dc)
	{
		// only one field can be defined as start
		if($dc->activeRecord->start)
		{
			\Database::getInstance()
				->prepare('UPDATE tl_workflow_step %s WHERE pid=? AND id!=?')
				->set(array('start' => ''))
				->execute($dc->activeRecord->pid, $dc->activeRecord->id);
		}
	}


	public function generateChildRecord($row)
	{
		$formatter = Formatter::create('tl_workflow_step');
		$return = $formatter->getPropertyValue('name', $row['name']);

		if($row['start'] || $row['end'])
		{

			$return .= ' <span class="tl_gray">[';

			if($row['start'])
			{
				$return .=  $formatter->getPropertyLabel('start');

				if($row['end'])
				{
					$return .= ', ';
				}
			}

			if($row['end'])
			{
				$return .=  $formatter->getPropertyLabel('end');
			}

			$return .= ']</span>';
		}

		return $return;
	}


	public function getSteps()
	{
		return $GLOBALS['TL_CONFIG']['workflow_steps'];
	}

	public function getStates()
	{
		return $GLOBALS['TL_CONFIG']['workflow_actions'];
	}


	public function getRoles($dc)
	{
		$this->loadProcess($dc->activeRecord->pid);
		return $this->process->roles;
	}


	public function loadProcess($id)
	{
		if(!$this->process)
		{
			$this->process = \Database::getInstance()
				->prepare('SELECT * FROM tl_workflow_process WHERE id=?')
				->limit(1)
				->execute($id);

			$this->process->roles = trimsplit(',', $this->process->roles);
		}
	}

} 