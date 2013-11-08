<?php

/**
 * hooks
 */
$GLOBALS['TL_HOOKS']['loadDataContainer'][] = array('Workflow\Connector', 'hookLoadDataContainer');


/**
 * predefined workflow config
 */
$GLOBALS['TL_CONFIG']['workflow_disabledTables']    = array
(
	// workflow tables
	'tl_workflow', 'tl_workflow_state', 'tl_workflow_process', 'tl_workflow_service', 'tl_workflow_draft', 'tl_workflow_step',

	// system tables
	'tl_user', 'tl_user_group', 'tl_member', 'tl_member_group',	'tl_log', 'tl_version', 'tl_undo', 'tl_cron',
	'tl_repository_installs', 'tl_repository_instfiles', 'tl_search', 'tl_search_index', 'tl_session', 'tl_extension',
);


$GLOBALS['TL_CONFIG']['workflow_disabledModules']   = array('settings', 'member', 'user', 'mgroup', 'group');

$GLOBALS['TL_CONFIG']['workflow_steps'] = array
(
	'created', 'changed', 'proposed', 'validated', 'published', 'unpublished', 'archived', 'deleted',
);

$GLOBALS['TL_CONFIG']['workflow_actions'] = array
(
	'create', 'change', 'propose', 'reject', 'validate', 'restore', 'publish', 'unpublish',  'archive', 'delete'
);


$GLOBALS['TL_WORKFLOW_SERVICES']['notify']  = 'Workflow\Service\NotifyService';
$GLOBALS['TL_WORKFLOW_SERVICES']['parent'] = 'Workflow\Service\ParentService';


/**
 * Backend module
 */
$GLOBALS['BE_MOD']['content']['article']['tables'][] = 'tl_workflow_draft';


array_insert($GLOBALS['BE_MOD'], 1, array
(
	'workflow' => array
	(
		'wf_config' => array
		(
			'stylesheet' => 'system/modules/workflow/assets/css/style.css',
			'tables' => array('tl_workflow', 'tl_workflow_process', 'tl_workflow_step', 'tl_workflow_service'),
			'icon' => 'system/themes/default/images/settings.gif',
		),
	),

));


