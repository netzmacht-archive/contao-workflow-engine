<?php

$GLOBALS['TL_DCA']['tl_workflow_step'] = array
(
	'config' => array
	(
		'dataContainer'     => 'Table',
		'ptable' => 'tl_workflow_process',
		'enableVersioning'  => false,

		'onsubmit_callback' => array
		(
			array('Workflow\Dca\Step', 'saveStart'),
		),

		'sql' => array
		(
			'keys' => array
			(
				'id' => 'primary',
				'pid' => 'index',
			)
		),
	),

	'list' => array
	(
		'sorting' => array
		(
			'mode' => 4,
			'flag' => 1,
			'headerFields' => array('name', 'description'),
			'fields' => array('name'),
			'child_record_callback' => array('Workflow\Dca\Step', 'generateChildRecord'),
		),

		'label' => array
		(
			'fields'         => array('name'),
			'label_callback' => array('Workflow\Dca\Workflow', 'callbackLabel'),
		),

		'global_operations' => array
		(
			'all' => array
			(
				'label' => $GLOBALS['TL_LANG']['tl_workflow_step']['all'],
				'href'  => 'act=select',
				'class'  => 'header_edit_all'
			),
		),

		'operations' => array
		(
			'edit' => array
			(
				'label' => $GLOBALS['TL_LANG']['tl_workflow_step']['edit'],
				'href'  => 'act=edit',
				'icon'  => 'edit.gif'
			),

			'delete' => array
			(
				'label' => $GLOBALS['TL_LANG']['tl_workflow_step']['edit'],
				'href'  => 'act=delete',
				'icon'  => 'delete.gif'
			),
		),

	),

	'palettes' => array
	(
		'__selector__' => array('end'),
	),

	'metapalettes' => array
	(
		'default' => array
		(
			'name' => array('name', 'description', 'start', 'end'),
			'states' => array('next_states'),
			'protect' => array('roles'),
		),

		'end extends default' => array
		(
			'-states' => array('next_states'),
		)
	),

	'metasubselectpalettes' => array
	(
	),

	'fields' => array
	(
		'id' => array
		(
			'sql'           => "int(10) unsigned NOT NULL auto_increment"
		),

		'pid' => array
		(
			'sql'           => "int(10) unsigned NOT NULL default '0'"
		),

		'sorting' => array
		(
			'sql'           => "int(10) unsigned NOT NULL default '0'"
		),

		'tstamp' => array
		(
			'sql'           => "int(10) unsigned NOT NULL default '0'"
		),

		'name' => array
		(
			'label'         => &$GLOBALS['TL_LANG']['tl_workflow_step']['name'],
			'inputType'     => 'select',
			'options_callback' => array('Workflow\Dca\Step', 'getSteps'),
			'reference'     => &$GLOBALS['TL_LANG']['workflow']['steps'],
			'eval'          => array
			(
				'mandatory' => true,
				'tl_class' => 'w50',
			),
			'sql'           => "varchar(255) NOT NULL default ''",
		),

		'description' => array
		(
			'label'         => &$GLOBALS['TL_LANG']['tl_workflow_step']['description'],
			'inputType'     => 'text',
			'eval'          => array
			(

				'tl_class' => 'clr long',
			),
			'sql'           => "varchar(255) NOT NULL default ''",
		),

		'start' => array
		(
			'label'         => &$GLOBALS['TL_LANG']['tl_workflow_step']['start'],
			'inputType'     => 'checkbox',
			'eval'          => array
			(
				'tl_class' => 'w50',
			),
			'sql'           => "char(1) NOT NULL default ''",
		),

		'end' => array
		(
			'label'         => &$GLOBALS['TL_LANG']['tl_workflow_step']['end'],
			'inputType'     => 'checkbox',
			'eval'          => array
			(
				'tl_class' => 'w50',
				'submitOnChange' => true,
			),
			'sql'           => "char(1) NOT NULL default ''",
		),

		'next_states' => array
		(
			'label'         => &$GLOBALS['TL_LANG']['tl_workflow_step']['next_states'],
			'inputType'     => 'multiColumnWizard',
			'load_callback' => array
			(
				array('Workflow\Dca\NextStateSelection', 'initialize'),
			),
			'eval'          => array
			(
				'columnFields' => array
				(
					'state' => array
					(
						'label'         => &$GLOBALS['TL_LANG']['tl_workflow_step']['state'],
						'inputType'     => 'select',
						'options_callback' => array('Workflow\Dca\Step', 'getStates'),
						'reference'     => &$GLOBALS['TL_LANG']['workflow']['states'],
						'eval'          => array
						(
							'style' => 'width: 150px',
						),
					),

					'type' => array
					(
						'label'         => &$GLOBALS['TL_LANG']['tl_workflow_step']['targetType'],
						'inputType'          => 'select',
						'default'       => 'step',
						'options'       => array('step', 'process'),
						'reference'     => &$GLOBALS['TL_LANG']['tl_workflow_step']['types'],
						'eval'          => array
						(
							'submitOnChange' => true,
							'style' => 'width: 150px',
						),
					),

					'target' => array
					(
						'label'         => &$GLOBALS['TL_LANG']['tl_workflow_step']['target'],
						'inputType'          => 'select',
						'options_callback'  => array('Workflow\Dca\NextStateSelection', 'getTargets'),
						'reference'     => &$GLOBALS['TL_LANG']['workflow']['steps'],
						'eval'          => array
						(
							'style' => 'width: 150px',
						),
					),
				),
			),
			'sql'           => "blob NULL",
		),

		'roles' => array
		(
			'label'         => &$GLOBALS['TL_LANG']['tl_workflow_step']['roles'],
			'inputType'     => 'checkbox',
			'options_callback'  => array('Workflow\Dca\Step', 'getRoles'),
			'eval'          => array
			(
				'multiple' => true,
			),
			'sql'           => "blob NULL",
		),
	),
);