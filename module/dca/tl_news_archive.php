<?php

$GLOBALS['TL_DCA']['tl_news_archive']['fields']['addWorkflow'] = array
(
	'label'             => &$GLOBALS['TL_LANG']['tl_news_archive']['addWorkflow'],
	'inputType'         => 'checkbox',
	'eval'              => array('submitOnChange' => true, 'tl_class' => 'clr m12 w50'),
	'sql'               => "char(1) NOT NULL default ''"
);

$GLOBALS['TL_DCA']['tl_news_archive']['fields']['workflow'] = array
(
	'label'             => &$GLOBALS['TL_LANG']['tl_news_archive']['workflow'],
	'inputType'         => 'select',
	'options_callback'  => array('Workflow\Contao\Dca\NewsArchive', 'getWorkflows'),
	'eval'              => array('tl_class' => 'w50', 'includeBlankOptions' => true),
	'sql'               => "int(10) unsigned NOT NULL default '0'"
);


$GLOBALS['TL_DCA']['tl_news_archive']['config']['onload_callback'][] = array('Workflow\Contao\Dca\NewsArchive', 'initialize');

$GLOBALS['TL_DCA']['tl_news_archive']['metasubpalettes']['addWorkflow'] = array('workflow');

$GLOBALS['TL_DCA']['tl_news_archive']['palettes']['default'] .= ';{workflow_legend},addWorkflow';

//$definition->getPalette('default')->createLegend('workflow')->addProperty('addWorkflow');