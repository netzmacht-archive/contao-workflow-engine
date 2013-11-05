<?php


$GLOBALS['TL_DCA']['tl_article']['workflow'] = array
(
	//'process' => 'default',
	'subscribers'  => array
	(
		'Workflow\Process\WorkflowDataChildren',
	),
);