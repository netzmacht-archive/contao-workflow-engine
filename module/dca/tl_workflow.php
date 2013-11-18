<?php

$GLOBALS['TL_DCA']['tl_workflow'] = array
(
	'config' => array
	(
		'dataContainer'     => 'Table',
		'table'             => 'tl_workflow',
		'ctables'           => array('tl_workflow_service'),
		'enableVersioning'  => false,

		'onload_callback' => array
		(
			array('Workflow\Contao\Dca\Workflow', 'initialize'),
		),

		'sql' => array
		(
			'keys' => array
			(
				'id' => 'primary',
				'tableName' => 'index'
			)
		),
	),

	'list' => array
	(
		'sorting' => array
		(
			'mode' => 1,
			'fields' => array('workflow'),
		),

		'label' => array
		(
			'fields'         => array('title', 'workflow'),
			'format'         => '%s <span class="tl_gray">[%s]</span>',
			//'label_callback' => array('Workflow\Contao\Dca\Workflow', 'callbackLabel'),
		),

		'global_operations' => array
		(
			'process' => array
			(
				'label' => $GLOBALS['TL_LANG']['tl_workflow']['btn_process'],
				'href'  => 'table=tl_workflow_process',
				'icon'  => 'system/modules/workflow/assets/img/process.png',
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

			'services' => array
			(
				'label' => $GLOBALS['TL_LANG']['tl_workflow']['btn_services'],
				'href'  => 'table=tl_workflow_service',
				'icon'  => 'system/modules/workflow/assets/img/service.png'
			),

			'delete' => array
			(
				'label' => $GLOBALS['TL_LANG']['tl_workflow']['delete'],
				'href'  => 'act=delete',
				'icon'  => 'delete.gif'
			),


		),

	),

	'palettes' => array
	(
		'__selector__' => array('workflow')
	),

	'metapalettes' => array
	(
		'default' => array
		(
			'workflow'  => array('title', 'workflow', 'active'),
		),

		'_workflow_ extends default' => array
		(
			'processes'  => array('processes'),
		),

		'page extends _workflow_' => array(),
		'news extends _workflow_' => array(),
	),

	'metasubpalettes' => array
	(
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

		'tableName' => array
		(
			'label'         => &$GLOBALS['TL_LANG']['tl_workflow']['tableName'],
			'inputType'     => 'select',
			'options_callback' => array('Workflow\Contao\Dca\Workflow', 'getTables'),
			'eval' => array
			(
				'mandatory'      => true,
				'submitOnChange' => true,
				'chosen'         => true,
				'tl_class'       => 'w50',
			),
			'sql'           => "varchar(64) NOT NULL default ''",
		),

		'workflow' => array
		(
			'label'            => &$GLOBALS['TL_LANG']['tl_workflow']['workflow'],
			'inputType'        => 'select',
			'options_callback' => array('Workflow\Contao\Dca\Workflow', 'getWorkflowTypes'),
			'eval' => array
			(
				'includeBlankOption' => true,
				'submitOnChange'     => true,
				'mandatory'          => true,
				'tl_class'           => 'w50',
			),
			'sql'           => "varchar(32) NOT NULL default ''"
		),

		'processes' => array
		(
			'label'             => &$GLOBALS['TL_LANG']['tl_workflow']['processes'],
			'inputType'         => 'multiColumnWizard',
			'eval'              => array
			(
				'tl_class'      => 'clr',
				'columnCallback' => array('Workflow\Contao\Dca\Workflow', 'getProcessMcw'),
				'columnFields'  => array
				(
					'table' => array
					(
						'label'             => &$GLOBALS['TL_LANG']['tl_workflow']['table'],
						'inputType'         => 'select',
						'options_callback'  => array('Workflow\Contao\Dca\Workflow', 'getWorkflowTables'),
						'eval'              => array('style' => 'width: 240px', 'submitOnChange' => true,),
					),

					'process' => array
					(
						'label'         => &$GLOBALS['TL_LANG']['tl_workflow']['process'],
						'inputType'     => 'select',
						'foreignKey'    => 'tl_workflow_process.name',
						'eval'          => array
						(
							'includeBlankOption' => true,
							'style' => 'width: 240px',
						),
					),

				),
			),
			'sql'               => "blob NULL",
		),

		'dataProperties' => array
		(
			'label'             => &$GLOBALS['TL_LANG']['tl_workflow']['dataProperties'],
			'inputType'         => 'checkbox',
			'options_callback'  => array('Workflow\Contao\Dca\Workflow', 'getStorageProperties'),
			'eval'              => array('tl_class' => 'clr', 'multiple' => true,),
			'sql'               => "blob NULL",
		),

		'publishStep' => array
		(
			'label'         => &$GLOBALS['TL_LANG']['tl_workflow']['publishStep'],
			'inputType'     => 'select',
			'default'       => 'published',
			'options_callback'  => array('Workflow\Contao\Dca\Workflow', 'getSteps'),
			'eval' => array
			(
				'tl_class' => 'w50',
				'chosen' => true,
			),
			'sql'           => "int(10) unsigned NOT NULL default '0'"
		),

		'invertPublishValue' => array
		(
			'label'             => &$GLOBALS['TL_LANG']['tl_workflow']['invertPublishValue'],
			'inputType'         => 'checkbox',
			'eval'              => array('tl_class' => 'w50',),
			'sql'               => "char(1) NOT NULL default ''",
		),

		'addModuleLimit' => array
		(
			'label'             => &$GLOBALS['TL_LANG']['tl_workflow']['addModuleLimit'],
			'inputType'         => 'checkbox',
			'eval'              => array
			(
				'tl_class' => 'clr w50 m12',
				'submitOnChange' => true,
			),
			'sql'               => "char(1) NOT NULL default ''",
		),

		'limitModule' => array
		(
			'label'             => &$GLOBALS['TL_LANG']['tl_workflow']['limitModule'],
			'inputType'         => 'select',
			'options_callback'  => array('Workflow\Contao\Dca\Workflow', 'getModules'),
			'reference'         => &$GLOBALS['TL_LANG']['MOD'],
			'eval' => array
			(
				'includeBlankOption' => true,
				'mandatory'          => true,
				'submitOnChange'     => true,
				'tl_class'           => 'w50',
			),
			'sql'           => "varchar(64) NOT NULL default ''",
		),

		'addParentLimit' => array
		(
			'label'             => &$GLOBALS['TL_LANG']['tl_workflow']['addParentLimit'],
			'inputType'         => 'checkbox',
			'eval'              => array
			(
				'tl_class' => 'clr w50 m12',
				'submitOnChange' => true
			),
			'sql'               => "char(1) NOT NULL default ''",
		),

		'limitParentTable' => array
		(
			'label'             => &$GLOBALS['TL_LANG']['tl_workflow']['limitParentTable'],
			'inputType'         => 'select',
			'options_callback'  => array('Workflow\Contao\Dca\Workflow', 'getParentTables'),
			'reference'         => &$GLOBALS['TL_LANG']['MOD'],
			'eval' => array
			(
				'includeBlankOption' => true,
				'mandatory'          => true,
				'submitOnChange'     => true,
				'tl_class'           => 'w50',
			),
			'sql'               => "varchar(64) NOT NULL default ''",
		),

		'limitParentIds' => array
		(
			'label'             => &$GLOBALS['TL_LANG']['tl_workflow']['limitParentIds'],
			'inputType'         => 'multiColumnWizard',
			'eval'              => array
			(
				'tl_class'      => 'clr',
				'flatArray'     => true,
				'columnFields'  => array
				(
					'parent' => array
					(
						'label'             => &$GLOBALS['TL_LANG']['tl_workflow']['limitParentIds'],
						'inputType'         => 'select',
						'options_callback'  => array('Workflow\Contao\Dca\Workflow', 'getParentEntries'),
						'eval'              => array('style' => 'width: 400px', 'chosen' => true),
					),
				),
			),
			'sql'               => "blob NULL",
		),

		'hasAuthorColumn' => array
		(
			'label'             => &$GLOBALS['TL_LANG']['tl_workflow']['hasAuthorColumn'],
			'inputType'         => 'checkbox',
			'eval'              => array
			(
				'tl_class' => 'clr w50 m12',
				'submitOnChange' => true
			),
			'sql'               => "char(1) NOT NULL default ''",
		),

		'authorColumn' => array
		(
			'label'         => &$GLOBALS['TL_LANG']['tl_workflow']['authorColumn'],
			'inputType'     => 'select',
			'default'       => 'published',
			'options_callback'  => array('Workflow\Contao\Dca\Workflow', 'getColumns'),
			'eval' => array
			(
				'tl_class' => 'w50',
				'chosen' => true,
			),
			'sql'           => "varchar(64) NOT NULL default ''"
		),

	),
);