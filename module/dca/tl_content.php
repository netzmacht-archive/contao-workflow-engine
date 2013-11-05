<?php
/**
 * Created by PhpStorm.
 * User: david
 * Date: 28.10.13
 * Time: 07:08
 */


/**
 * callbacks
 */
//$GLOBALS['TL_DCA']['tl_content']['config']['onload_callback'][] = array('Workflow\Workflow', 'initialize');


/**
 * workflow
 */
$GLOBALS['TL_DCA']['tl_content']['workflow'] = array
(
	// process will be set automatically
	//'process' => 'simple',
	'subscribers'  => array
	(
		'Workflow\Process\WorkflowDataContent',
	),
);


/**
 * operations
 */
\DcaTools\Definition::getDataContainer('tl_content')
	->createOperation('draft', 'global')
		->setHref('table=tl_workflow_draft&amp;id=' . CURRENT_ID . '&amp;wtable=' . \Input::get('table') .'&amp;popup=1')
		->setLabelByRef($GLOBALS['TL_LANG']['tl_content']['draft'])
		->setIcon('system/modules/workflow/assets/img/draft.png')
		->setAttributes('onclick="Backend.openModalIframe({\'width\':765,\'title\':\'Entwürfe\',\'url\':this.href});return false"');



if(\Input::get('draft') == '1')
{
	$GLOBALS['TL_DCA']['tl_content']['config']['ptable'] = 'tl_workflow_draft';
}