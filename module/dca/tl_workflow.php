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

		),

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
			'fields' => array('forTable'),
		),

		'label' => array
		(
			'fields'         => array('title', 'forTable'),
			'label_callback' => array('Workflow\Contao\Dca\Workflow', 'callbackLabel'),
		),

		'global_operations' => array
		(
			'process' => array
			(
				'label' => $GLOBALS['TL_LANG']['tl_workflow']['process'],
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

	'metapalettes' => array
	(
		'default' => array
		(
			'title'    => array('title', 'process', 'forTable'),
			'storage'  => array('store_children', 'data_properties')
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

		'forTable' => array
		(
			'label'         => &$GLOBALS['TL_LANG']['tl_workflow']['forTable'],
			'inputType'     => 'select',
			'options_callback' => array('Workflow\Contao\Dca\Workflow', 'getTables'),
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

		'store_children' => array
		(
			'label'             => &$GLOBALS['TL_LANG']['tl_workflow']['store_children'],
			'inputType'         => 'checkbox',
			'exclude'           => true,
			'eval'              => array
			(
				'tl_class'  => 'clr',
				'submitOnChange' => true,
			),
			'sql'               => "char(1) NOT NULL default ''",
		),

		'data_properties' => array
		(
			'label'             => &$GLOBALS['TL_LANG']['tl_workflow']['data_properties'],
			'inputType'         => 'checkbox',
			'options_callback'  => array('Workflow\Contao\Dca\Workflow', 'getStorageProperties'),
			'eval'              => array('tl_class' => 'clr', 'multiple' => true,),
			'sql'               => "blob NULL",
		),
	),
);