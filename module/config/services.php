<?php


/**
 * Workflow connector
 */
$GLOBALS['container']['workflow.connector'] = $GLOBALS['container']->share(function($c) {
	return new \Workflow\Contao\Connector\TableConnector();
});


/**
 * Controller Manager
 */
$GLOBALS['container']['workflow.controller'] = $GLOBALS['container']->share(function($c) {
	return new \Workflow\Controller\Controller(
		$c['workflow.workflow-manager'],
		$c['dcatools.driver-manager'],
		$c['workflow.entity-registry'],
		$c['event-dispatcher']);
});


/**
 * Workflow Manager
 */
$GLOBALS['container']['workflow.workflow-manager'] = $GLOBALS['container']->share(function($c) {
	return new \Workflow\Controller\WorkflowManager();
});


/**
 * model state storage
 */
$GLOBALS['container']['workflow.model-state-storage'] = $GLOBALS['container']->share(function($c) {
	return new \Workflow\Model\ModelStorage(
		$c['dcatools.driver-manager']->getDataProvider('tl_workflow_state')
	);
});


$GLOBALS['container']['workflow.entity-registry'] = $GLOBALS['container']->share(function($c) {
	return new \Workflow\Entity\Registry();
});
