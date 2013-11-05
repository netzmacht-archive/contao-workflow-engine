<?php

$GLOBALS['TL_DCA']['tl_workflow'] = array
(
	'config' => array
	(
		'dataContainer'     => 'Table',
		'table' => 'tl_workflow',
		'enableVersioning'  => false,

		'sql' => array
		(
			'keys' => array
			(
				'id' => 'primary',
				//'forTable,forModule' => 'unique'
			)
		),
	),

	'list' => array
	(
		'sorting' => array
		(
			'mode' => 1,
			'fields' => array('forModule', 'forTable'),
		),

		'label' => array
		(
			'fields'         => array('title', 'forModule', 'forTable'),
			'label_callback' => array('Workflow\Dca\Workflow', 'callbackLabel'),
		),

		'global_operations' => array
		(
			'process' => array
			(
				'label' => $GLOBALS['TL_LANG']['tl_workflow']['process'],
				'href'  => 'table=tl_workflow_process',
				'icon'  => 'system/modules/workflow/assets/img/process.png',
			),
			'service' => array
			(
				'label' => $GLOBALS['TL_LANG']['tl_workflow']['edit'],
				'href'  => 'table=tl_workflow_service',
				'icon'  => 'system/modules/workflow/assets/img/service.png'
			),
		),

		'operations' => array
		(
			'edit' => array
			(
				'label' => $GLOBALS['TL_LANG']['tl_workflow']['edit'],
				'href'  => 'act=edit',
				'icon'  => 'edit.gif'
			),

			'delete' => array
			(
				'label' => $GLOBALS['TL_LANG']['tl_workflow']['delete'],
				'href'  => 'act=delete',
				'icon'  => 'delete.gif'
			),


		),

	),

	'metapalettes' => array
	(
		'default' => array
		(
			'title'    => array('title', 'process'),
			'scope'    => array('forTable'),
			'services' => array('services'),
		),
	),

	'metasubselectpalettes' => array
	(
		'forTable' => array
		(
			'!' => array('forModule'),
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

		'title' => array
		(
			'label'         => &$GLOBALS['TL_LANG']['tl_workflow']['title'],
			'inputType'     => 'text',
			'eval'          => array
			(
				'mandatory' => true,
				'tl_class' => 'w50',
			),
			'sql'           => "varchar(255) NOT NULL default ''",
		),

		'forModule' => array
		(
			'label'         => &$GLOBALS['TL_LANG']['tl_workflow']['forModule'],
			'inputType' => 'select',
			'options_callback' => array('Workflow\Dca\Workflow', 'getModules'),
			'reference' => &$GLOBALS['TL_LANG']['MOD'],
			'eval' => array
			(
				'includeBlankOption' => true,
				'tl_class' => 'w50',
			),
			'sql'           => "varchar(64) NOT NULL default ''",
		),

		'forTable' => array
		(
			'label'         => &$GLOBALS['TL_LANG']['tl_workflow']['forTable'],
			'inputType'     => 'select',
			'options_callback' => array('Workflow\Dca\Workflow', 'getTables'),
			'eval' => array
			(
				'mandatory' => true,
				'submitOnChange' => true,
				'tl_class' => 'w50',
			),
			'sql'           => "varchar(64) NOT NULL default ''",
		),

		'process' => array
		(
			'label'         => &$GLOBALS['TL_LANG']['tl_workflow']['process'],
			'inputType'     => 'select',
			'foreignKey'    => 'tl_workflow_process.name',
			'eval' => array
			(
				'tl_class' => 'w50',
			),
			'sql'           => "int(10) unsigned NOT NULL default '0'"
		),

		'services' => array
		(
			'label'         => &$GLOBALS['TL_LANG']['tl_workflow']['services'],
			'inputType'     => 'multiColumnWizard',
			'eval'          => array
			(
				'columnFields' => array
				(
					'service' => array
					(
						'label'            => &$GLOBALS['TL_LANG']['tl_workflow']['service'],
						'inputType'        => 'select',
						'options_callback' => array('Workflow\Dca\Workflow', 'getServices'),
						'eval'          => array
						(
							'style' => 'width: 280px',
						),
					),

					'disabled' => array
					(
						'label'         => &$GLOBALS['TL_LANG']['tl_workflow']['service_disabled'],
						'inputType'     => 'checkbox',
						'eval'          => array
						(
							'style' => 'width: 80px',
						),
					),
				),
			),
			'sql'           => "blob NULL",
		),
	),
);