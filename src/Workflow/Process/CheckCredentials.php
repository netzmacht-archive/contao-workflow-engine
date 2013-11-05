<?php

/**
 * Contao Open Source CMS
 *
 * Copyright (C) 2005-2012 Leo Feyer
 *
 * @package   workflow-engine
 * @author    David Molineus <http://www.netzmacht.de>
 * @license   https://github.com/netzmacht/contao-workflow-engine/blob/master/LICENSE
 * @copyright Copyright 2013 David Molineus netzmacht creative
 *
 **/

namespace Workflow\Process;

use Workflow\Event\SecurityEvent;

class CheckCredentials
{

	/**
	 * Basic security listener checking by user BackendUser::hasAccess
	 *
	 * @param SecurityEvent $event
	 */
	public static function execute(SecurityEvent $event)
	{
		/** @var \BackendUser $user */
		$user = \BackendUser::getInstance();
		$roles = $event->getStep()->getRoles();
		$field = sprintf('workflow_%s_%s',
			\Input::get('do'),
			$event->getModel()->getEntity()->getProviderName()
		);

		if(empty($roles) || $user->isAdmin || $user->hasAccess($roles, $field))
		{
			$event->grantAccess(true);
		}
		else {
			$event->grantAccess(false);
		}
	}

}
