<?php


/**
 * workflow
 */
$GLOBALS['container']['workflow.connector'] = $GLOBALS['container']->share(function() {
	return new \Workflow\Contao\Connector();
});


/**
 * model state storage
 */
$GLOBALS['container']['workflow.model-state-storage'] = $GLOBALS['container']->share(function($c) {
	return new \Workflow\Model\ModelStorage(
		$c['dcatools.driver-manager']->getDataProvider('tl_workflow_state')
	);
});
