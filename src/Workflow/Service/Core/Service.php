<?php
/**
 * Created by PhpStorm.
 * User: david
 * Date: 05.11.13
 * Time: 08:37
 */

namespace Workflow\Service\Core;


use Workflow\Event\SecurityEvent;
use Workflow\Service\AbstractService;

class Service extends AbstractService
{
	const VERSION = '1.0';

	const IDENTIFIER = 'core';

	/**
	 * @inheritdoc
	 */
	function initialize()
	{
		$dispatcher = $this->environment->getEventDispatcher();
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
		$field = sprintf('workflow_%s_%s', \Input::get('do'), $event->getModel()->getEntity()->getProviderName());

		if(empty($roles) || $user->isAdmin || $user->hasAccess($roles, $field))
		{
			$event->grantAccess(true);
		}
		else
		{
			$event->grantAccess(false);
		}
	}

}
