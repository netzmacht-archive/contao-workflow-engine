<?php

$GLOBALS['TL_DCA']['tl_workflow_process'] = array
(
	'config' => array
	(
		'dataContainer'     => 'Table',
		'enableVersioning'  => false,
		'ctable'            => array('tl_workflow_step'),

		'sql' => array
		(
			'keys' => array
			(
				'id' => 'primary',
				'name' => 'unique'
			),
		),
	),

	'metapalettes' => array
	(
		'default' => array
		(
			'name'  => array('name', 'description', 'routine'),
			'roles' => array('roles'),
			'steps' => array('steps'),
		),
	),

	'list' => array
	(
		'sorting' => array
		(
			'mode' => 1,
			'flag' => 1,
			'fields' => array('name'),
			//'disableGrouping' => true,
		),

		'label' => array
		(
			'fields' => array('name'),
			'format' => '%s',
		),

		'operations' => array
		(
			'edit' => array
			(
				'label' => &$GLOBALS['TL_LANG']['tl_workflow_process']['edit'],
				'href'  => 'act=edit',
				'icon'  => 'edit.gif',
			),
			'copy' => array
			(
				'label' => &$GLOBALS['TL_LANG']['tl_workflow_process']['edit'],
				'href'  => 'act=copy',
				'icon'  => 'copy.gif',
			),
			'delete' => array
			(
				'label' => &$GLOBALS['TL_LANG']['tl_workflow_process']['delete'],
				'href'  => 'act=delete',
				'icon'  => 'delete.gif',
			),
		),
	),

	'fields' => array
	(
		'id' => array
		(
			'sql'           => "int(10) unsigned NOT NULL auto_increment"
		),

		'tstamp' => array
		(
			'sql'           => "int(10) unsigned NOT NULL default '0'"
		),

		'name' => array
		(
			'label'         => &$GLOBALS['TL_LANG']['tl_workflow_process']['name'],
			'inputType'     => 'text',
			'eval'          => array
			(
				'rgxp' => 'alias',
				'tl_class' => 'w50',
			),
			'sql'           => "varchar(255) NOT NULL default ''",
		),

		'description' => array
		(
			'label'         => &$GLOBALS['TL_LANG']['tl_workflow_process']['title'],
			'inputType'     => 'text',
			'eval'          => array
			(
				'tl_class' => 'clr long',
			),
			'sql'           => "varchar(255) NOT NULL default ''",
		),

		'routine' => array(
			'label'         => &$GLOBALS['TL_LANG']['tl_workflow_process']['routine'],
			'inputType'          => 'radio',
			'options_callback' => array('Workflow\Dca\Process', 'getRoutines'),
			'eval'          => array('multiple' => true),
			'sql'           => "varchar(64) NOT NULL default ''",
		),

		'roles' => array
		(
			'label'         => &$GLOBALS['TL_LANG']['tl_workflow_process']['roles'],
			'inputType'     => 'text',
			'default'       => 'owner',
			'eval'          => array(),
			'sql'           => "varchar(255) NOT NULL default ''",
		),

		'steps' => array
		(
			'label'         => &$GLOBALS['TL_LANG']['tl_workflow_process']['steps'],
			'inputType'     => 'dcaWizard',
			'foreignTable'  => 'tl_workflow_step',
			'eval'          => array
			(
				'listCallback' => array('Workflow\Dca\Process', 'generateStepList'),
				'editButtonLabel' => &$GLOBALS['TL_LANG']['tl_workflow_process']['editStep'],
				'applyButtonLabel' => &$GLOBALS['TL_LANG']['tl_workflow_process']['applyStep'],
				'tl_class' => 'clr',
				'currentRecord' => \Input::get('id'),
			),
		),
	),

);