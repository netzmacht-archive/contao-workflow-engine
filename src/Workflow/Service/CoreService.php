<?php
/**
 * Created by PhpStorm.
 * User: david
 * Date: 05.11.13
 * Time: 08:37
 */

namespace Workflow\Service;

use Workflow\Event\SecurityEvent;

class CoreService extends AbstractService
{
	const VERSION = '1.0';

	const IDENTIFIER = 'core';

	/**
	 * @inheritdoc
	 */
	function initialize()
	{
		$dispatcher = $this->controller->getEventDispatcher();
		$dispatcher->addListener('workflow.check_credentials', array($this, 'checkCredentials'));
	}


	/**
	 *
	 * @throws \RuntimeException
	 */
	public static function getConfig()
	{
		throw new \RuntimeException('Core service is not configurable');
	}


	/**
	 * Basic security listener checking by user BackendUser::hasAccess
	 *
	 * @param SecurityEvent $event
	 */
	public function checkCredentials(SecurityEvent $event)
	{
		/** @var \BackendUser $user */
		$user = \BackendUser::getInstance();
		$roles = $event->getStep()->getRoles();
		$tableName = $event->getModel()->getEntity()->getProviderName();
		$field = sprintf('workflow_%s_%s', \Input::get('do'), $tableName);

		if(empty($roles) || $user->isAdmin || $user->hasAccess($roles, $field))
		{
			$event->grantAccess(true);
		}
		else
		{
			if(in_array('owner', $roles) && isset($GLOBALS['TL_WORKFLOW_OWNER_MAPPING'][$tableName]))
			{
				if($user->id == $event->getModel()->getEntity()->getProperty($GLOBALS['TL_WORKFLOW_OWNER_MAPPING'][$tableName]))
				{
					$event->grantAccess(true);
					return;
				}
			}

			$event->grantAccess(false);
		}
	}

}
