<?php

$GLOBALS['TL_DCA']['tl_workflow_state'] = array
(
	'config' => array
	(
		'dataContainer'     => 'Table',
		'ptable'            => 'tl_workflow_state',
		'ctable'            => array('tl_workflow_state'),
		'enableVersioning'  => false,
		'closed'            => true,

		'sql' => array
		(
			'keys' => array
			(
				'id' => 'primary',
				'pid' => 'index',
			)
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

		'ptable' => array
		(
			'sql'           => "varchar(64) NOT NULL default ''"
		),

		'tstamp' => array
		(
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
			'label'         => '',
			'inputType'     => '',
			'sql'           => "varchar(64) NOT NULL default ''",
		),

		'stepName' => array
		(
			'label'         => '',
			'inputType'     => '',
			'sql'           => "varchar(64) NOT NULL default ''",
		),

		'successful' => array
		(
			'label'         => '',
			'inputType'     => '',
			'sql'           => "char(1) NOT NULL default ''",
		),

		'createdAt' => array
		(
			'label'         => '',
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

	),
);