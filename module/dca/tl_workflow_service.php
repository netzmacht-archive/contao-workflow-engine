<?php

$GLOBALS['TL_DCA']['tl_workflow_service'] = array
(
	'config' => array
	(
		'dataContainer'     => 'Table',
		'enableVersioning'  => false,

		'onload_callback' => array
		(
			array('Workflow\Dca\Service', 'initialize'),
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
			'mode' => 2,
			'flag' => 1,
			'headerFields'  => array('name', 'description'),
			'panelLayout'   => 'sort,filter;search,limit',
			'fields' => array('name'),
			//'child_record_callback' => array('Workflow\Dca\Service', 'generateChildRecord'),
		),

		'label' => array
		(
			'fields'         => array('name', 'service'),
			'format'         => '%s <span class="tl_gray">[%s]</span>',
			//'format'         => array('%s')
			//'label_callback' => array('Workflow\Dca\Workflow', 'callbackLabel'),
		),

		'global_operations' => array
		(
			'back' => array
			(
				'label' => $GLOBALS['TL_LANG']['MSC']['backBT'],
				'href'  => 'table=tl_workflow',
				'class'  => 'header_back'
			),

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
			'name'   => array('name', 'service', 'description'),
			'config' => array(),
		),

		'scope'      => array
		(
			'scope' => array('events', 'steps'),
		),
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
			'options_callback'  => array('Workflow\Dca\Service', 'getServices'),
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

		'events' => array
		(
			'label'             => &$GLOBALS['TL_LANG']['tl_workflow_service']['events'],
			'inputType'         => 'checkbox',
			'exclude'           => true,
			'default'           => 'reached',
			'options_callback'  => array('Workflow\Dca\Service', 'getEvents'),
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
			'options_callback'  => array('Workflow\Dca\Service', 'getSteps'),
			'reference'         => &$GLOBALS['TL_LANG']['workflow']['steps'],
			'eval'              => array
			(
				'multiple'  => true,
				'mandatory' => true,
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
			'options_callback'  => array('Workflow\Dca\Service', 'getUsers'),
			'eval'              => array
			(
				'multiple'      => true,
				'tl_class'      => 'clr',
			),
			'sql'               => "mediumblob NULL",
		),


	),
);