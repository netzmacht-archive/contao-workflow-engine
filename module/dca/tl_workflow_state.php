<?php

$GLOBALS['TL_DCA']['tl_workflow_state'] = array
(
	'config' => array
	(
		'dataContainer'     => 'General',
		'enableVersioning'  => false,
		'closed'            => true,
		'notEditable'       => true,

		'tablename_callback' => array
		(
			array('Workflow\Contao\Dca\State', 'selectDriver'),
		),

		'onload_callback' => array
		(
			array('Workflow\Contao\Dca\State', 'initialize'),
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

	'dca_config' => array
	(
		'data_provider'  => array
		(
			'list' => array
			(
				'class'     => 'Workflow\Contao\Data\GroupListingDriver',
				'source'    => 'tl_workflow_state',
				'group_by'  => array('ptable', 'pid', ),
				'count'     => 'count(id) count',
				'condition' => array
				(
					'select'  => 'MAX(tstamp) maxtime',
					'filter'  => 'tstamp=maxtime',
				),
			),

			'show' => array
			(
				'class'       => 'Workflow\Contao\Data\SerializedDataDriver',
				'source'      => 'tl_workflow_state',
				'data_field'  => 'data',
				'data_format' => \Workflow\Contao\Data\SerializedDataDriver::DATA_FORMAT_JSON,
			),
		),
	),

	'list' => array
	(
		'sorting' => array
		(
			'mode'                  => 1,
			'flag'                  => 11,
			'panelLayout'           => 'search,limit',
			'fields'                => array('createdAt DESC'),
			'headerFields'          => array('title', 'name', 'type', 'headline', 'id'),
			'header_callback'       => array('Workflow\Contao\Dca\State', 'callbackHeader'),
			'child_record_callback' => array('Workflow\Contao\Dca\State', 'callbackChildRecord'),
		),

		'label' => array
		(
			'fields'         => array('ptable', 'pid', 'processName', 'stepName', 'successful', 'count'),
			'showColumns'    => true,
		),

		'global_operations' => array
		(

		),

		'operations' => array
		(
			'list' => array
			(
				'label' => &$GLOBALS['TL_LANG']['tl_workflow_state']['list'],
				'icon'  => 'system/modules/workflow/assets/img/history.png',
				'href'  => '',
				'button_callback' => array('Workflow\Contao\Dca\State', 'getListButton'),
			),

			'show' => array
			(
				'icon'  => 'show.gif',
				'href'  => 'act=show&amp;wfid=',
				'label' => &$GLOBALS['TL_LANG']['tl_workflow_state']['show']
			),
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
			'label'         => &$GLOBALS['TL_LANG']['tl_workflow_state']['pid'],
			'sql'           => "int(10) unsigned NOT NULL default '0'"
		),

		'ptable' => array
		(
			'label'         => &$GLOBALS['TL_LANG']['tl_workflow_state']['ptable'],
			'sorting'       => true,
			'sql'           => "varchar(64) NOT NULL default ''"
		),

		'tstamp' => array
		(
			'eval'          => array('rgxp' => 'dateim'),
			'sql'           => "int(10) unsigned NOT NULL default '0'"
		),

		'workflowIdentifier' => array
		(
			'label' => '',
			'inputType' => '',
			'sql' => "varchar(255) NOT NULL default ''",
		),

		'processName' => array
		(
			'label'         => &$GLOBALS['TL_LANG']['tl_workflow_state']['processName'],
			'inputType'     => '',
			'sorting'       => true,
			'reference'     => &$GLOBALS['TL_LANG']['workflow']['process'],
			'sql'           => "varchar(64) NOT NULL default ''",
		),

		'stepName' => array
		(
			'label'         => &$GLOBALS['TL_LANG']['tl_workflow_state']['stepName'],
			'inputType'     => '',
			'reference'     => &$GLOBALS['TL_LANG']['workflow']['steps'],
			'sql'           => "varchar(64) NOT NULL default ''",
		),

		'successful' => array
		(
			'label'         => &$GLOBALS['TL_LANG']['tl_workflow_state']['successful'],
			'inputType'     => '',
			'sql'           => "char(1) NOT NULL default ''",
		),

		'createdAt' => array
		(
			'label'         => &$GLOBALS['TL_LANG']['tl_workflow_state']['createdAt'],
			'sorting'       => true,
			'inputType'     => '',
			'sql'           => "int(10) unsigned NOT NULL default '0'"
		),

		'data' => array
		(
			'label'         => '',
			'inputType'     => '',
			'sql'           => "blob NULL",
		),

		'errors' => array
		(
			'label' => '',
			'inputType' => '',
			'sql' => "text NULL",
		),

		'previous' => array
		(
			'label' => '',
			'inputType' => '',
			'sql'           => "int(10) unsigned NOT NULL default '0'"
		),

		'next' => array
		(
			'label' => '',
			'inputType' => '',
			'sql'           => "int(10) unsigned NOT NULL default '0'"
		),

		'count' => array
		(
			'label'         => &$GLOBALS['TL_LANG']['tl_workflow_state']['count'],
		),

	),
);