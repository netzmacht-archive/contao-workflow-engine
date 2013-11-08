<?php
/**
 * Created by PhpStorm.
 * User: david
 * Date: 08.11.13
 * Time: 18:28
 */

namespace Workflow\Service;


use DcaTools\Definition;
use DcaTools\Event\Helper;

class RestrictAccessService extends AbstractService
{
	protected static $config = array
	(
		'identifier' => 'restrict-access',
		'version'    => '1.0.0',
		'properties' => array
		(
			'scope'  => array('steps','roles'),
			'config' => array('restrict_tables', 'restrict_operations'),
		),
	);

	/**
	 * @inheritdoc
	 */
	function initialize()
	{
		$dispatcher = $this->controller->getEventDispatcher();
		$definition = Definition::getDataContainer($this->controller->getWorkflow()->getTable());
		$state  = $this->controller->getCurrentState();
		$user   = \BackendUser::getInstance();
		$roles  = deserialize($this->service->getProperty('roles'), true);

		if($state
			&& in_array($state->getStepName(), $this->service->getProperty('steps'))
			&& $user->isAdmin || $user->hasAccess($roles ,sprintf('workflow_%s', $definition->getName()))
		) {

			$operations = deserialize($this->service->getProperty('restrict_operations'), true);

			foreach($operations as $operation)
			{
				list($table, $scope, $name) = explode('::', $operation['operation']);

				if($operation['mode'] == 'hide')
				{
					$listener = Helper::getListener(array('DcaTools\Event\Listener\Operation', 'disable', array('value' => true)));
				}
				else {
					$listener = Helper::getListener(array('DcaTools\Event\Listener\Operation', 'disable', array('value' => true)));
				}

				$eventName = sprintf('dcatools.%s.%s', $scope == 'global' ? 'global_operations' : 'operations', $name);
				$dispatcher->addListener($eventName, $listener);
			}

			/*
			$definition->getOperation('editheader')->remove();
			$definition->getOperation('delete')->remove();
			$definition->getOperation('cut')->remove();
			$definition->getOperation('toggle')->remove();

			$definition->getOperation('all', 'global')->remove();

			if($definition->get('list/sorting/fields/0') == 'sorting')
			{
				$definition->set('list/sorting/fields/0', 'sorting ');
			}
			*/
		}
	}


}