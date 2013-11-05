<?php

/*
$GLOBALS['TL_DCA']['tl_settings']['fields']['workflowModule'] = array
(
	'label' => &$GLOBALS['TL_LANG']['tl_settings']['workflowModule'],
	'inputType' => 'multiColumnWizard',
	'eval' => array
	(
		'columnsCallback' => array('\Workflow\Contao\Dca\Settings', 'getWorkflowModuleMcw'),
		'columnFields' => array
		(
			'label' => array
			(
				'label' => &$GLOBALS['TL_LANG']['tl_settings']['workflowModule_label'],
				'inputType' => 'text',
				'eval' => array('style' => 'width: 220px', 'includeBlankOption' => true),
			),

			'name' => array
			(
				'label' => &$GLOBALS['TL_LANG']['tl_settings']['workflowModule_name'],
				'inputType' => 'select',
				'options_callback'   => array('\Workflow\Contao\Dca\Settings', 'getModules'),
				'reference' => $GLOBALS['TL_LANG']['MOD'],
				'eval' => array('style' => 'width: 120px', 'submitOnChange' => true, 'includeBlankOption' => true),
			),

			'table' => array
			(
				'label' => &$GLOBALS['TL_LANG']['tl_settings']['workflowModule_table'],
				'inputType' => 'select',
				'options_callback'   => array('\Workflow\Contao\Dca\Settings', 'getTables'),
				'eval' => array('style' => 'width: 120px', 'includeBlankOption' => true),
			),

			'process' => array
			(
				'label' => &$GLOBALS['TL_LANG']['tl_settings']['workflowModule_process'],
				'inputType' => 'select',
				'options'   => array_keys($GLOBALS['TL_WORKFLOW']),
				'reference' => $GLOBALS['TL_LANG']['workflow']['processes'],
				'eval' => array('style' => 'width: 120px'),
			)
		),
	),
);


\DcaTools\Definition::getPalette('tl_settings', 'default')
	->createLegend('workflow')
	->appendBefore('timeout')
		->addProperty('workflowModule');

*/