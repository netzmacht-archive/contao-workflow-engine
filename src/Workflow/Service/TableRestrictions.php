<?php
/**
 * Created by PhpStorm.
 * User: david
 * Date: 19.11.13
 * Time: 13:21
 */

namespace Workflow\Service;

use DcaTools\Definition;
use DcaTools\Event\Listener\DataContainer;
use DcaTools\Event\PermissionEvent;
use DcGeneral\Data\ModelInterface as EntityInterface;

class TableRestrictions extends AbstractService
{
	protected static $config = array
	(
		'name'      => 'table-restrictions',
		'drivers'   => array('Table'),
		'config'    => array
		(
			'scope'  => array('steps', 'roles'),
			'config' => array('table_restrictions'),
		),
	);

	protected $restrictions;

	/**
	 * Initialize the workflow service
	 *
	 * @inheritdoc
	 */
	function initialize()
	{
		$table  = $this->service->getProperty('tableName');
		$roles  = deserialize($this->service->getProperty('roles'), true);
		$steps  = deserialize($this->service->getProperty('steps'), true);
		$config = 'workflow_' . $this->controller->getProcessHandler()->getProcess()->getName();

		/** @var \BackendUser $user */
		$user   = \BackendUser::getInstance();

		if(!$user->hasAccess($roles, $config))
		{
			return false;
		}

		$state = $this->controller->getProcessHandler()->getCurrentState($this->controller->getCurrentModel());

		if($state && !in_array($state->getStepName(), $steps))
		{
			return false;
		}

		$definition   = Definition::getDataContainer($this->service->getProperty('tableName'));
		$this->restrictions = deserialize($this->service->getProperty('table_restrictions'), true);

		$definition->set('config/closed', $this->check('closed'));

		if($this->check('notDeletable'))
		{
			$definition->set('config/notDeletable', true);

			if($definition->hasOperation('delete'))
			{
				$definition->getOperation('delete')->remove();
			}
		}

		if($this->check('notEditable'))
		{
			$definition->set('config/notEditable', true);

			foreach($definition->getOperations() as $operation)
			{
				if($operation->getName() != 'show')
				{
					$operation->remove();
				}
			}

			foreach($definition->getOperations('global') as $operation)
			{
				$operation->remove();
			}
		}

		// notSortable is only available in Contao 3.2. Workaround for disabling sorting but keeping order
		// @see https://github.com/contao/core/issues/5254
		if($this->check('notSortable') && $definition->get('list/sorting/fields/0') == 'sorting')
		{
			$sorting    = $definition->get('list/sorting/fields');
			$sorting[0] = 'sorting ';

			$definition->set('list/sorting/fields', $sorting);

			// pass an permission event manually because check permission of DcaTools has already passed
			if($this->service->getProperty('tableName') == \Input::get('table'))
			{
				$event = new PermissionEvent($this->controller->getCurrentModel()->getEntity(), array('error' => ''));
				DataContainer::forbidden($event, array('act' => 'paste'));
			}
		}
	}


	/**
	 * @param $restriction
	 * @return bool
	 */
	protected function check($restriction)
	{
		return in_array($restriction, $this->restrictions);
	}

}
