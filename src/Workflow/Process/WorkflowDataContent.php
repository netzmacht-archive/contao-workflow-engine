<?php
/**
 * Created by PhpStorm.
 * User: david
 * Date: 30.10.13
 * Time: 13:57
 */

namespace Workflow\Process;

use DcGeneral\Data\DCGE;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Workflow\Event\RegisterEventsEvent;
use Workflow\Event\StepEvent;


class WorkflowDataContent implements EventSubscriberInterface
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
			$dispatcher->addListener($event, array($this, 'storeContent'));
		}
	}


	/**
	 * Store content of element
	 *
	 * @param StepEvent $event
	 */
	public static function storeContent(StepEvent $event)
	{
		$model = $event->getModelState();
		$model->setData($event->getModel()->getEntity()->getPropertiesAsArray());
		$model->setMeta(DCGE::MODEL_IS_CHANGED, true);

		/** @var \Workflow\Registry $registry */
		$registry = $GLOBALS['container']['workflow.registry'];
		$registry->getModelManager()->flush($model);
	}

}
