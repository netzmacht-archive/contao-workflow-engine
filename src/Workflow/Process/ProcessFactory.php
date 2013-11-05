<?php
/**
 * Created by PhpStorm.
 * User: david
 * Date: 04.11.13
 * Time: 08:10
 */

namespace Workflow\Process;


use DcaTools\Definition;
use DcaTools\Model\FilterBuilder;
use DcGeneral\Data\ModelInterface;
use Workflow\Exception\WorkflowException;
use Workflow\Flow\NextStateInterface;
use Workflow\Flow\Process;
use Workflow\Flow\Step;
use Workflow\Process\ProcessFactoryInterface;


class ProcessFactory implements ProcessFactoryInterface
{
	/**
	 * @var \Workflow\Data\DriverManagerInterface $providerManager
	 */
	protected $providerManager;


	/**
	 * @param \Workflow\Data\DriverManagerInterface|\DcGeneral\DC_General $providerManager
	 */
	public function __construct($providerManager)
	{
		$this->providerManager = $providerManager;
	}


	/**
	 * @param $name name or id
	 *
	 * @return Process
	 * @throws \Workflow\Exception\WorkflowException
	 */
	public function create($name)
	{
		if(is_numeric($name))
		{
			return $this->createById($name);
		}

		return $this->createByName($name);
	}


	/**
	 * @param $id
	 *
	 * @return Process
	 * @throws \Workflow\Exception\WorkflowException
	 */
	public function createById($id)
	{
		$driver = $this->providerManager->getDataProvider('tl_workflow_process');

		$config = $driver->getEmptyConfig();
		$config->setId($id);

		$model = $driver->fetch($config);

		if($model === null)
		{
			throw new WorkflowException(sprintf('Unknown process with ID "%s"', $id));
		}

		return $this->createFromModel($model);
	}


	/**
	 * @param $name
	 *
	 * @return Process
	 * @throws \Workflow\Exception\WorkflowException
	 */
	public function createByName($name)
	{
		$driver = $this->providerManager->getDataProvider('tl_workflow_process');

		$config = FilterBuilder::create()->addEquals('name', $name)->getConfig($driver);
		$model = $driver->fetch($config);

		if($model === null)
		{
			throw new WorkflowException(sprintf('Unknown process with name "%s"', $name));
		}

		return $this->createFromModel($model);
	}


	/**
	 * @param ModelInterface $model
	 *
	 * @return Process
	 *
	 * @throws \Workflow\Exception\WorkflowException
	 */
	public function createFromModel(ModelInterface $model)
	{
		$driver = $this->providerManager->getDataProvider('tl_workflow_step');

		$config = FilterBuilder::create()->addEquals('pid', $model->getId())->getConfig($driver);
		$stepsCollection = $driver->fetchAll($config);

		$steps = array();
		$stepsConfig = array();
		$start = null;
		$end = array();

		// pre generate config array, so we can find children steps
		foreach($stepsCollection as $step)
		{
			/** @var \DcGeneral\Data\ModelInterface $step */
			$stepsConfig[$step->getId()] = $step->getPropertiesAsArray();
		}

		foreach($stepsConfig as $step)
		{
			// step could has already been created by recursive calling
			if(!isset($steps[$step['name']]))
			{
				$steps[$step['name']] = $this->createStep($step, $steps, $stepsConfig);
			}

			if($step['start'])
			{
				$start = $step['name'];
			}

			if($step['end'])
			{
				$end[$step['name']] = $steps[$step['name']];
			}
		}

		return new Process($model->getProperty('name'), $steps, $start, $end);
	}


	/**
	 * @param $stepConfig
	 * @param Step[] $steps
	 * @param array $stepsConfig
	 *
	 * @return Step
	 *
	 * @throws \InvalidArgumentException
	 */
	protected function createStep($stepConfig, array &$steps, array $stepsConfig)
	{
		$nextStates = deserialize($stepConfig['next_states'], true);
		$roles      = deserialize($stepConfig['roles'], true);
		$onInvalid  = $stepConfig['invalid'] ?: null;

		$step = new Step($stepConfig['name'], $stepConfig['label'], array(), array('setStatus', $stepConfig['name']), $roles, $onInvalid);
		$steps[$stepConfig['name']] = $step;

		foreach($nextStates as $state)
		{
			if (NextStateInterface::TARGET_TYPE_STEP === $state['type'])
			{
				// step does not exist anymore
				if(!isset($stepsConfig[$state['target']]))
				{
					continue;
				}
				elseif(!isset($steps[$stepsConfig[$state['target']]['name']]))
				{
					$steps[$state['target']] = $this->createStep($stepsConfig[$state['target']], $steps, $stepsConfig);
					$target = $steps[$state['target']];
				}
				else {
					$target = $steps[$stepsConfig[$state['target']]['name']];
				}
			}
			elseif(NextStateInterface::TARGET_TYPE_PROCESS === $state['type'])
			{
				$target = $this->createById($state['target']);
			}
			else
			{
				throw new \InvalidArgumentException(sprintf('Unknown type "%s", please use "step" or "process"', $state['type']));
			}

			$step->addNextState($state['name'], $state['type'], $target);
		}

		return $step;
	}

}
