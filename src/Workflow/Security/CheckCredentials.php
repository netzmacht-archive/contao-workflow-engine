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

namespace Workflow\Security;

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

		if(empty($roles) || $user->isAdmin || $user->hasAccess($roles, 'workflow'))
		{
			$event->grantAccess(true);
		}

		$event->grantAccess(false);
	}

}
