<?php

if(TL_MODE == 'BE')
{

	/**
	 * workflow
	 */
	$GLOBALS['container']['workflow'] = $GLOBALS['container']->share(function() {
		return new \Workflow\Workflow();
	});


	/**
	 * driver manager
	 */
	$GLOBALS['container']['workflow.driver-manager'] = $GLOBALS['container']->share(function() {
		return new \Workflow\Data\DriverManager();
	});


	/**
	 * model state storage
	 */
	$GLOBALS['container']['workflow.model-state-storage'] = $GLOBALS['container']->share(function($c) {
		return new \Workflow\Model\ModelStorage(
			$c['workflow.driver-manager']->getDataProvider('tl_workflow_state')
		);
	});

}
