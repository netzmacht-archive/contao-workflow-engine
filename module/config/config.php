<?php

/**
 * Hooks
 */
$GLOBALS['TL_HOOKS']['loadDataContainer'][] = array('Workflow\Contao\Connector', 'hookLoadDataContainer');


/**
 * Tables which are disabled for workflows
 */
$GLOBALS['TL_CONFIG']['workflow_disabledTables'] = array
(
	// workflow tables
	'tl_workflow',
	'tl_workflow_state',
	'tl_workflow_process',
	'tl_workflow_service',
	'tl_workflow_draft',
	'tl_workflow_step',

	// system tables
	'tl_user',
	'tl_user_group',
	'tl_member',
	'tl_member_group',
	'tl_log',
	'tl_version',
	'tl_undo',
	'tl_cron',
	'tl_repository_installs',
	'tl_repository_instfiles',
	'tl_search',
	'tl_search_index',
	'tl_session',
	'tl_extension',
	'tl_settings',
);


/**
 * no workflow in this modules
 */
$GLOBALS['TL_CONFIG']['workflow_disabledModules'] = array
(
	'settings',
	'member',
	'user',
	'mgroup',
	'group'
);


/**
 * Possible workflow steps
 *
 * Steps can be defined without a limit. List is limited for making defining of a process easier.
 */
$GLOBALS['TL_CONFIG']['workflow_steps'] = array
(
	'created',
	'changed',
	'proposed',
	'validated',
	'published',
	'unpublished',
	'archived',
	'deleted',
	'aborted'
);


/**
 * Possible workflow actions
 *
 * Actions can be defined freely. Only some are bound to Contao DC actions. These are:
 *   change -> act=edit
 *   delete -> act=delete
 */
$GLOBALS['TL_CONFIG']['workflow_actions'] = array
(
	'create',
	'change',
	'propose',
	'reject',
	'validate',
	'restore',
	'publish',
	'unpublish',
	'archive',
	'delete'
);


/**
 * Roles are assigned to a user group. Every role can be defined. The only required one is owner which is used to map
 * the author to the logged in backend user
 */
$GLOBALS['TL_CONFIG']['workflow_roles'] = array(
	'owner',
	'editor',
	'reviewer',
	'publisher',
	'superuser',
);


/**
 * Add draft table to defined backend module
 */
$GLOBALS['BE_MOD']['content']['article']['tables'][] = 'tl_workflow_draft';

/**
 * Workflow backend module
 */
array_insert($GLOBALS['BE_MOD'], 1, array
(
	'workflow' => array
	(
		'workflow_draft' => array
		(
			'tables' => array('tl_workflow_draft'),

		),

		'workflow_settings' => array
		(
			'stylesheet' => 'system/modules/workflow/assets/css/style.css',
			'tables' => array('tl_workflow', 'tl_workflow_process', 'tl_workflow_step', 'tl_workflow_service'),
			'icon' => 'system/themes/default/images/settings.gif',
		),
	),
));


/**
 * Provided workflow services
 */
$GLOBALS['TL_WORKFLOW_SERVICES']['notify']          = 'Workflow\Service\NotifyService';
$GLOBALS['TL_WORKFLOW_SERVICES']['parent']          = 'Workflow\Service\ParentService';
$GLOBALS['TL_WORKFLOW_SERVICES']['restrict-access'] = 'Workflow\Service\RestrictAccessService';
