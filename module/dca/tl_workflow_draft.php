<?php

$GLOBALS['TL_DCA']['tl_workflow_draft'] = array
(
	'config' => array
	(
		'dataContainer'     => 'Table',
		'enableVersioning'  => false,
		'dynamicPtable'     => true,
		'ptable'            => '',
		'ctable'            => array('tl_content'),

		'sql' => array
		(
			'keys' => array
			(
				'id' => 'primary',
				'pid' => 'index',
				'ptable' => 'index',
			),
		),
	),


	'workflow' => array
	(
		//'process'       => 'default',
		'events'        => array
		(

		),
		'subscribers'   => array
		(
		),
	),

	'metapalettes' => array
	(
		'default' => array
		(
			'title' => array('title', 'userid'),
			'comment' => array('comment'),
		),
	),

	'list' => array
	(
		'sorting' => array
		(
			'mode' => '4',
			'fields' => array('userid'),
			'headerFields' => array('title', 'headline', 'author', 'inColumn', 'tstamp', 'showTeaser', 'published', 'start', 'stop'),
			'child_record_callback' => array('Workflow\Contao\Dca\WorkflowDraft', 'generateChildRecord'),
		),

		'label' => array
		(
			'fields' => array('title', 'userid'),
		),

		'operations' => array
		(
			'edit' => array
			(
				'href' => '&amp;draft=1&amp;table=' . \Input::get('wtable'),
				'icon' => 'edit.gif',
			),
		)

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

		'userid' => array
		(
			'inputType'     => 'select',
			'options'       => array(\BackendUser::getInstance()->id),
			'default'       => \BackendUser::getInstance()->id,
			'foreignKey'    => 'tl_user.name',
			'sql'           => "int(10) unsigned NOT NULL default '0'"
		),

		'ptable' => array
		(
			'sql'           => "varchar(64) NOT NULL default ''"
		),

		'tstamp' => array
		(
			'sorting'       => true,
			'flag'          => 11,
			'sql'           => "int(10) unsigned NOT NULL default '0'"
		),

		'data' => array
		(
			'sql'           => "blob NULL"
		),

		'title' => array
		(
			'inputType' => 'text',
			'sql'           => "varchar(255) NOT NULL default ''"
		),

		'comment' => array
		(
			'inputType'     => 'textarea',
			'sql'           => "tinytext NULL",
		),

	),
);


switch(\Input::get('do'))
{
	case 'article':
		$GLOBALS['TL_DCA']['tl_workflow_draft']['config']['ptable'] = 'tl_article';
		break;
}