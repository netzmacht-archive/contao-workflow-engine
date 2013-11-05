<?php
/**
 * Created by PhpStorm.
 * User: david
 * Date: 01.11.13
 * Time: 10:22
 */

namespace Workflow\Handler;

use Workflow\Exception\WorkflowException;
use Workflow\Flow\NextStateInterface;
use Workflow\Flow\Process;
use Workflow\Flow\Step;


class ProcessManager
{

	/**
	 * @var Process[]
	 */
	protected $processes = array();


	/**
	 *
	 */
	protected $steps = array();


	/**
	 * @param $name
	 * @return mixed
	 * @throws \Workflow\Exception\WorkflowException
	 */
	public function getProcess($name)
	{
		if($this->hasProcess($name))
		{
			return $this->processes[$name];
		}

		throw new WorkflowException(sprintf('Unknown process "%s".', $name));
	}


	/**
	 * @param $name
	 * @return bool
	 */
	public function hasProcess($name)
	{
		return $this->loadProcess($name);
	}


	/**
	 * @param $processName
	 * @param $stepName
	 */
	protected function getProcessStep($processName, $stepName)
	{

	}


	/**
	 * @param $processName
	 * @param $stepName
	 */
	protected function hasProcessStep($processName, $stepName)
	{

	}


	/**
	 * @param $name
	 * @return bool
	 */
	protected function loadProcess($name)
	{
		if(!isset($this->processes[$name]))
		{
			if(isset($GLOBALS['TL_WORKFLOW']['processes'][$name]))
			{
				$this->processes[$name] = $this->buildProcess($name, $GLOBALS['TL_WORKFLOW']['processes'][$name]);
			}
			else {
				$this->processes[$name] = false;
			}
		}

		return (bool) $this->processes[$name];
	}


	/**
	 * @param $name
	 * @param array $config
	 *
	 * @return Process
	 */
	protected function buildProcess($name, array $config)
	{
		$this->steps[$name] = array();

		foreach(array_keys($config['step']) as $stepName)
		{
			$this->buildStep($name, $stepName);
		}

		return new Process($name, $this->steps[$name], $config['start'], $config['end']);
	}

	protected function getStep($processName, $stepName)
	{
		if(!isset($this->steps[$processName][$stepName]))
		{
			if(!isset($GLOBALS['TL_WORKFLOW'][$processName]['steps'][$stepName]))
			{
				throw new WorkflowException(sprintf('Unknown step "%s" of process "%s"', $stepName, $processName));
			}

			$stepConfig = $GLOBALS['TL_WORKFLOW'][$processName]['steps'][$stepName];

			$nextStates  = isset($stepConfig['next_states']) ? $stepConfig['next_states'] : array();
			$modelStatus = isset($stepConfig['model_status']) ? $stepConfig['model_status'] : array();
			$roles       = isset($stepConfig['roles']) ? $stepConfig['roles'] : array();
			$onInvalid   = isset($stepConfig['on_invalid']) ? $stepConfig['on_invalid'] : array();

			$step = new Step($stepName, $stepConfig['label'], array(), $modelStatus, $roles, $onInvalid);
			$this->steps[$processName][$stepName] = $step;

			foreach($nextStates as $stateName => $stateConfig)
			{
				$target = null;

				if (NextStateInterface::TARGET_TYPE_STEP === $stateConfig['type'])
				{
					$target = $this->getStep($processName, $stateConfig['target']);
				}
				elseif(NextStateInterface::TARGET_TYPE_PROCESS === $stateConfig['type'])
				{
					$target = $this->getProcess($stateConfig['target']);
				}
				else {
					throw new \InvalidArgumentException(sprintf('Unknown type "%s", please use "step" or "process"', $stateConfig['type']));
				}

				$step->addNextState($stateName, $stateConfig['type'], $target);
			}
		}

		return $this->steps[$processName][$stepName];
	}

} 