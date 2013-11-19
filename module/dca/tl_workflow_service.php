<?php

$GLOBALS['TL_DCA']['tl_workflow_service'] = array
(
	'config' => array
	(
		'dataContainer'     => 'Table',
		'enableVersioning'  => false,
		'ptable'            => 'tl_workflow',
		'onload_callback' => array
		(
			array('Workflow\Contao\Dca\Service', 'initialize'),
		),

		'oncreate_callback' => array
		(
			array('Workflow\Contao\Dca\Service', 'callbackOnCreate'),
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
			'headerFields'  => array('title', 'workflow'),
			'panelLayout'   => 'sort,filter;search,limit',
			'fields' => array('tableName', 'service'),
			'child_record_callback' => array('Workflow\Contao\Dca\Service', 'generateChildRecord'),
		),

		'label' => array
		(
			'fields'         => array('name', 'service'),
			'format'         => '%s <span class="tl_gray">[%s]</span>',
			//'format'         => array('%s')
			//'label_callback' => array('Workflow\Contao\Dca\Workflow', 'callbackLabel'),
		),

		'global_operations' => array
		(
			'all' => array
			(
				'label'               => &$GLOBALS['TL_LANG']['MSC']['all'],
				'href'                => 'act=select',
				'class'               => 'header_edit_all',
				'attributes'          => 'onclick="Backend.getScrollOffset()" accesskey="e"'
			),
		),

		'operations' => array
		(
			'edit' => array
			(
				'label' => $GLOBALS['TL_LANG']['tl_workflow_service']['edit'],
				'href'  => 'act=edit',
				'icon'  => 'edit.gif'
			),

			'copy' => array
			(
				'label' => $GLOBALS['TL_LANG']['tl_workflow_service']['copy'],
				'href'  => 'act=copy',
				'icon'  => 'copy.gif'
			),

			'delete' => array
			(
				'label'         => $GLOBALS['TL_LANG']['tl_workflow_service']['edit'],
				'href'          => 'act=delete',
				'icon'          => 'delete.gif',
				'attributes'    => 'onclick="if(!confirm(\'' . $GLOBALS['TL_LANG']['MSC']['deleteConfirm'] . '\'))return false;Backend.getScrollOffset()"',
			),
		),
	),

	'metapalettes' => array
	(
		'default' => array
		(
			'name'   => array('name', 'description', 'service', 'tableName'),
			'scope'  => array(),
			'config' => array(),
		),
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
			'sql'                     => "int(10) unsigned NOT NULL default '0'"
		),

		'tstamp' => array
		(
			'sql'           => "int(10) unsigned NOT NULL default '0'"
		),

		'name' => array
		(
			'label'         => &$GLOBALS['TL_LANG']['tl_workflow_service']['name'],
			'inputType'     => 'text',
			'exclude'       => true,
			'sorting'       => true,
			'search'        => true,
			'filter'        => true,
			'eval'          => array
			(
				'mandatory' => true,
				'tl_class' => 'w50',
			),
			'sql'           => "varchar(255) NOT NULL default ''",
		),

		'description' => array
		(
			'label'         => &$GLOBALS['TL_LANG']['tl_workflow_service']['description'],
			'inputType'     => 'text',
			'exclude'       => true,
			'search'        => true,
			'eval'          => array
			(

				'tl_class' => 'clr long',
			),
			'sql'           => "varchar(255) NOT NULL default ''",
		),

		'service' => array
		(
			'label'             => &$GLOBALS['TL_LANG']['tl_workflow_service']['service'],
			'inputType'         => 'select',
			'exclude'       => true,
			'sorting'       => true,
			'search'        => true,
			'filter'        => true,
			'options_callback'  => array('Workflow\Contao\Dca\Service', 'getServices'),
			'reference'         => &$GLOBALS['TL_LANG']['workflow']['services'],
			'eval'              => array
			(
				'includeBlankOption' => true,
				'submitOnChange'     => true,
				'mandatory'          => true,
				'helpwizard'         => true,
				'tl_class'           => 'w50',
			),
			'sql'               => "varchar(64) NOT NULL default ''",
		),

		'tableName' => array
		(
			'label'             => &$GLOBALS['TL_LANG']['tl_workflow_service']['tableName'],
			'inputType'         => 'select',
			'exclude'       => true,
			'sorting'       => true,
			'search'        => true,
			'filter'        => true,
			'options_callback'  => array('Workflow\Contao\Dca\Service', 'getTables'),
			'reference'         => &$GLOBALS['TL_LANG']['workflow']['services'],
			'eval'              => array
			(
				'includeBlankOption' => true,
				'submitOnChange'     => true,
				'mandatory'          => true,
				'tl_class'           => 'w50',
			),
			'sql'               => "varchar(64) NOT NULL default ''",
		),

		'reference' => array
		(
			'label'             => &$GLOBALS['TL_LANG']['tl_workflow_service']['reference'],
			'inputType'         => 'select',
			'exclude'       => true,
			'sorting'       => true,
			'search'        => true,
			'filter'        => true,
			'options_callback'  => array('Workflow\Contao\Dca\Service', 'getReferenceTables'),
			'reference'         => &$GLOBALS['TL_LANG']['workflow']['services'],
			'eval'              => array
			(
				'includeBlankOption' => true,
				'submitOnChange'     => true,
				'mandatory'          => true,
				'tl_class'           => 'w50',
			),
			'sql'               => "varchar(64) NOT NULL default ''",
		),

		'events' => array
		(
			'label'             => &$GLOBALS['TL_LANG']['tl_workflow_service']['events'],
			'inputType'         => 'checkbox',
			'exclude'           => true,
			'default'           => 'reached',
			'options_callback'  => array('Workflow\Contao\Dca\Service', 'getEvents'),
			'eval'              => array
			(
				'mandatory' => true,
				'multiple'  => true,
				'tl_class'  => 'clr',
			),
			'sql'               => "mediumblob NULL",
		),

		'steps' => array
		(
			'label'             => &$GLOBALS['TL_LANG']['tl_workflow_service']['steps'],
			'inputType'         => 'checkbox',
			'exclude'           => true,
			'options_callback'  => array('Workflow\Contao\Dca\Service', 'getSteps'),
			'reference'         => &$GLOBALS['TL_LANG']['workflow']['steps'],
			'eval'              => array
			(
				'multiple'  => true,
				'mandatory' => true,
				'tl_class'  => 'clr',
			),
			'sql'               => "mediumblob NULL",
		),

		'state' => array
		(
			'label'             => &$GLOBALS['TL_LANG']['tl_workflow_service']['state'],
			'inputType'         => 'select',
			'exclude'           => true,
			'options_callback'  => array('Workflow\Contao\Dca\Service', 'getAllStates'),
			'reference'         => &$GLOBALS['TL_LANG']['workflow']['state'],
			'eval'              => array
			(
				'mandatory' => true,
				'tl_class'  => 'w50',
			),
			'sql'               => "varchar(32) NOT NULL default ''",
		),

		'roles' => array
		(
			'label'             => &$GLOBALS['TL_LANG']['tl_workflow_service']['roles'],
			'inputType'         => 'checkbox',
			'exclude'           => true,
			'default'           => 'reached',
			'options'           => &$GLOBALS['TL_CONFIG']['workflow_roles'],
			'reference'         => $GLOBALS['TL_LANG']['workflow']['roles'],
			'eval'              => array
			(
				'mandatory' => true,
				'multiple'  => true,
				'tl_class'  => 'clr',
			),
			'sql'               => "mediumblob NULL",
		),

		'notify_email' => array
		(
			'label'             => &$GLOBALS['TL_LANG']['tl_workflow_service']['notify_email'],
			'inputType'         => 'text',
			'exclude'           => true,
			'eval'              => array
			(
				'rgxp'      => 'email',
				'tl_class'  => 'w50',
			),
			'sql'               => "varchar(128) NOT NULL default ''",
		),

		'notify_users' => array
		(
			'label'             => &$GLOBALS['TL_LANG']['tl_workflow_service']['notify_email'],
			'inputType'         => 'checkbox',
			'exclude'           => true,
			'options_callback'  => array('Workflow\Contao\Dca\Service', 'getAllUsers'),
			'eval'              => array
			(
				'multiple'      => true,
				'tl_class'      => 'clr',
			),
			'sql'               => "mediumblob NULL",
		),

		'storage_type' => array
		(
			'label'             => &$GLOBALS['TL_LANG']['tl_workflow_service']['storage_type'],
			'inputType'         => 'checkbox',
			'options'           => array('data', 'children'),
			'reference'         =>  &$GLOBALS['TL_LANG']['tl_workflow_service']['storage_type'],
			'exclude'           => true,
			'eval'              => array
			(
				'multiple'      => true,
				'helpwizard'    => true,
			),
			'sql'               => "varchar(255) NOT NULL default ''",
		),

		'restrict_tables' => array
		(
			'label'             => &$GLOBALS['TL_LANG']['tl_workflow_service']['restrict_tables'],
			'inputType'         => 'checkbox',
			'exclude'           => true,
			'options_callback'  => array('Workflow\Contao\Dca\Service', 'getRestrictTables'),
			'eval'              => array
			(
				'multiple'      => true,
				'tl_class'      => 'clr',
				'submitOnChange' => true,
			),
			'sql'               => "mediumblob NULL",
		),

		'restrictions' => array
		(
			'label'             => &$GLOBALS['TL_LANG']['tl_workflow_service']['restrictions'],
			'inputType'         => 'multiColumnWizard',
			'exclude'           => true,
			'eval'              => array
			(
				'tl_class'      => 'clr',
				'buttons'       => array('copy' => false, 'up' => false, 'down' => false, 'delete' => false),
				'columnFields'  => array
				(
					'table' => array
					(
						'label'             => &$GLOBALS['TL_LANG']['tl_workflow_service']['restrictions_table'],
						'inputType'         => 'text',
						'eval'              => array('style' => 'width: 130px', 'readonly' => true),
					),

					'closed' => array
					(
						'label'             => &$GLOBALS['TL_LANG']['tl_workflow_service']['restrictions_closed'],
						'inputType'         => 'checkbox',
						'eval'              => array('style' => 'width: 120px'),
					),

					'notEditable' => array
					(
						'label'             => &$GLOBALS['TL_LANG']['tl_workflow_service']['restrictions_notEditable'],
						'inputType'         => 'checkbox',
						'eval'              => array('style' => 'width: 120px'),
					),

					'notDeletable' => array
					(
						'label'             => &$GLOBALS['TL_LANG']['tl_workflow_service']['restrictions_notDeletable'],
						'inputType'         => 'checkbox',
						'eval'              => array('style' => 'width: 120px'),
					),

					'notSortable' => array
					(
						'label'             => &$GLOBALS['TL_LANG']['tl_workflow_service']['restrictions_notSortable'],
						'inputType'         => 'checkbox',
						'eval'              => array('style' => 'width: 120px'),
					),
				),
			),
			'sql'               => "mediumblob NULL",
		),

		'restrict_operations' => array
		(
			'label'             => &$GLOBALS['TL_LANG']['tl_workflow_service']['restrict_operations'],
			'inputType'         => 'multiColumnWizard',
			'exclude'           => true,
			'eval'              => array
			(
				'multiple'      => true,
				'tl_class'      => 'clr',
				'columnFields'  => array
				(
					'operation' => array
					(
						'label'             => &$GLOBALS['TL_LANG']['tl_workflow_service']['restrict_operation'],
						'inputType'         => 'select',
						'options_callback'  => array('Workflow\Contao\Dca\Service', 'getRestrictOperations'),
						'eval'              => array('style' => 'width: 300px'),
					),

					'mode'      => array
					(
						'label'             => &$GLOBALS['TL_LANG']['tl_workflow_service']['restrict_mode'],
						'inputType'         => 'select',
						'reference'         => &$GLOBALS['TL_LANG']['tl_workflow_service']['restrict_mode'],
						'options'           => array('disable', 'hide'),
						'eval'              => array('style' => 'width: 200px'),
					),
				)
			),
			'sql'               => "blob NULL",
		),
	),
);