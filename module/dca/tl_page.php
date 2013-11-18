<?php

$GLOBALS['TL_DCA']['tl_page']['fields']['addWorkflow'] = array
(
	'label'             => &$GLOBALS['TL_LANG']['tl_page']['addWorkflow'],
	'inputType'         => 'checkbox',
	'eval'              => array('submitOnChange' => true, 'tl_class' => 'clr m12 w50'),
	'sql'               => "char(1) NOT NULL default ''"
);

$GLOBALS['TL_DCA']['tl_page']['fields']['workflow'] = array
(
	'label'             => &$GLOBALS['TL_LANG']['tl_page']['workflow'],
	'inputType'         => 'select',
	'options_callback'  => array('Workflow\Contao\Dca\Page', 'getWorkflows'),
	'eval'              => array('tl_class' => 'w50', 'includeBlankOptions' => true),
	'sql'               => "int(10) unsigned NOT NULL default '0'"
);

$definition = \DcaTools\Definition::getDataContainer('tl_page');

$definition->registerCallback('onload', array('Workflow\Contao\Dca\Page', 'initialize'));
$definition->createSubPalette('addWorkflow')->addProperty('workflow');

$definition->getPalette('root')->getLegend('chmod')->addProperty('addWorkflow');
$definition->getPalette('regular')->getLegend('chmod')->addProperty('addWorkflow');