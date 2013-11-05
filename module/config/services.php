<?php

if(TL_MODE == 'BE')
{

	/**
	 * workflow registry
	 */
	$GLOBALS['container']['workflow.registry'] = $GLOBALS['container']->share(function() {
		return new \Workflow\Registry();
	});


	/**
	 * workflow
	 */
	$GLOBALS['container']['workflow'] = $GLOBALS['container']->share(function() {
		return new \Workflow\Workflow();
	});


	/**
	 * model manager
	 */
	$GLOBALS['container']['workflow.model-manager'] = $GLOBALS['container']->share(function($c) {
		return new \Workflow\Model\ModelManager($c['workflow.driver-manager']);
	});


	/**
	 * driver manager
	 */
	$GLOBALS['container']['workflow.driver-manager'] = $GLOBALS['container']->share(function() {
		return new \Workflow\Data\DriverManager();
	});


	/**
	 * process factory
	 */
	$GLOBALS['container']['workflow.process-factory'] = $GLOBALS['container']->share(function($c) {
		return new \Workflow\Process\ProcessFactory($c['workflow.driver-manager']);
	});

	/**
	 * process manager
	 */
	$GLOBALS['container']['workflow.process-manager'] = $GLOBALS['container']->share(function($c) {
		return new \Workflow\Process\ProcessManager($c['workflow.process-factory']);
	});

	/**
	 * model state storage
	 */
	$GLOBALS['container']['workflow.model-state-storage'] = $GLOBALS['container']->share(function($c) {
		return new \Workflow\Model\ModelStorage(
			$c['workflow.model-manager'],
			$c['workflow.driver-manager']->getDataProvider('tl_workflow_state')
		);
	});

}
