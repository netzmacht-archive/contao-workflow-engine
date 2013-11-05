<?php

namespace Workflow\Process;

use DcaTools\Definition;
use DcaTools\Model\FilterBuilder;
use DcGeneral\Data\DCGE;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Workflow\Event\RegisterEventsEvent;
use Workflow\Model\Model;
use Workflow\Event\StepEvent;


class WorkflowDataChildren implements EventSubscriberInterface
{

	/**
	 * Returns an array of event names this subscriber wants to listen to.
	 *
	 * The array keys are event names and the value can be:
	 *
	 *  * The method name to call (priority defaults to 0)
	 *  * An array composed of the method name to call and the priority
	 *  * An array of arrays composed of the method names to call and respective
	 *    priorities, or 0 if unset
	 *
	 * For instance:
	 *
	 *  * array('eventName' => 'methodName')
	 *  * array('eventName' => array('methodName', $priority))
	 *  * array('eventName' => array(array('methodName1', $priority), array('methodName2'))
	 *
	 * @return array The event names to listen to
	 *
	 * @api
	 */
	public static function getSubscribedEvents()
	{
		return array
		(
			'registerEvents' => 'registerEvents',
		);
	}


	/**
	 * Register storeContent to every reached event
	 *
	 * @param RegisterEventsEvent $event
	 */
	public function registerEvents(RegisterEventsEvent $event)
	{
		$processName = $event->getDefinition()->get('workflow/process');
		$dispatcher = $event->getDispatcher();

		foreach(array_keys($GLOBALS['TL_WORKFLOW'][$processName]['steps']) as $step)
		{
			$event = sprintf('%s.%s.reached', $processName, $step);
			$dispatcher->addListener($event, array($this, 'storeChildren'));
		}
	}


	/**
	 * When storing the element references to current
	 * @param StepEvent $event
	 */
	public static function storeChildren(StepEvent $event)
	{
		/** @var \Workflow\Model\Model $model */
		$model = $event->getModel();
		$definition = Definition::getDataContainer($model->getEntity()->getProviderName());

		$childTables = $definition->getFromDefinition('config/ctable');

		if($childTables === null)
		{
			return;
		}

		/** @var \Workflow\Registry $registry */
		$registry = $GLOBALS['container']['workflow.registry'];
		$children = array();
		$entity   = $model->getEntity();

		$filter = FilterBuilder::create()
			->addEquals(DCGE::MODEL_PID, $entity->getId())
			->addEquals(DCGE::MODEL_PTABLE, $entity->getProviderName())
			->getFilter();

		foreach($childTables as $table)
		{
			$config = $registry->getDataProvider($table)->getEmptyConfig();
			$config->setFilter($filter);
			$config->setSorting(array('sorting' => DCGE::MODEL_SORTING_ASC));

			$definition = Definition::getDataContainer($table);
			$childProcessName = $definition->getFromDefinition('workflow/process');

			if($childProcessName == '')
			{
				continue;
			}

			// register all events of child table before triggering their status
			$registry->getController($childProcessName)->registerEvents($table);
			$handler = $registry->getProcessHandler($childProcessName);

			foreach($registry->getDataProvider($table)->fetchAll($config) as $child)
			{
				$child = new Model($child);
				$state = $handler->getCurrentState($child);

				if($state === null)
				{
					$state = $handler->start($child);

					if($child->getEntity()->getId())
					{
						$children[$table][$child->getEntity()->getId()] = $state->getId();
					}
				}
				else {
					$state = $handler->reachNextState($child, 'export');
					$children[$table][$child->getEntity()->getId()] = $state->getId();
				}
			}
		}

		/** @var \Workflow\Model\Model $model */
		$modelState = $event->getModelState();
		$data = $modelState->getData();
		$data['children'] = $children;
		$modelState->setData($data);
		$modelState->setMeta(DCGE::MODEL_IS_CHANGED, true);

		$registry->getModelManager()->flush();
	}

}
