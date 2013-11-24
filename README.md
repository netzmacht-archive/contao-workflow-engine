Contao Workflow Engine
===================

This library has started as a fork of the Symfony2 [LexikWorkflowBundle](https://github.com/lexik/LexikWorkflowBundle).
After preparing this bundle for being used in Contao, a generic workflow extension has been built. The goal of this extension
is to provide a powerful interface where workflow can be easily implemented based for every dca based tables.

Features
----------
 * Defining workflow processes, multiple steps, multiple actions for each step and multiple roles [(See LexikWorkflowBundle documentation)](https://github.com/lexik/LexikWorkflowBundle)
 * Defining workflows which contain a process assignment to the related tables
 * Defining services which listens to workflow actions
 * GUI based workflow and process configurations
 * Prepared for working with different DC_Drivers

Components
----------

### Workflows

 * Knows about relations between tables and which features are supported (auto publishing)
 * Use shipped workflow (currently a news workflow is implemented) or create the one you need to

### Services

 * A service provides the logic which can be applied to an entity when it reached an state
 * A service is always limited to a table which it is assigned to
 * A service usually are not applied to all steps, it can defined the "scope" where it is active
 * Each service can support it's custom scopes
 * Scopes used by different provided services
 	* limit to current step
 	* limit to user roles
 	* limit to a field value of current entity/its parents
 	* limit to the request action (edit, delete and so on)
 * Implemented services
 	* core service: Is always assigned to a workflow
 	* items-restrictions: Is used for disabling operation buttons and deny access to serval actions
 	* table-restrictions: Is used for set table to notEditable/notSortable/notDeletable
 	* parent service: Triggers an chosen state of the parent workflow process

### Processes
Processes defines the possible steps which an entity can reach. It also defines the routes to the possible next steps.
Processes are not assigned to any specific table or workflow. Each process can be used for different workflow settings.

### Roles
 * Roles can be defined for each workflow
 * Roles are defined using Contaos backend user right (for each process a TL_RESTRICTIONS is created)
 * Admin state is also a role, so there can be limitations being set which also disabling functions to the admin (e.g. set a table closed)

For developers
----------
 * Is based on the data providers and models of DC_General
 * So it is database/table independent and could be used for almost everything
 * Uses [contao-community-alliance/event-dispatcher](https://github.com/contao-community-alliance/event-dispatcher)
 * Uses [contao-communtiy-alliance/dependency-container](contao-communtiy-alliance/dependency-container)

### Events ###

Events uses the prefix "workflow.*" and are assigned to the global event dispatcher. So you can listen to workflow events
in your own extensions easily.

*Global events*
 * `workflow.controller.get-workflow-types` is used for prefilter the possible workflows for an entity
 * `workflow.check_credentials` is called when checking user credentials

*Process spcific events*
Events are triggered for each process name. So each listener has to decide for itself if the given table is the one it
listens to.

* `workflow.PROCESS_NAME.STEP_NAME.reached` used then reaching a next step. Currently the default on where services listens to
* `workflow.PROCESS_NAME.STEP_NAME.validate` used to validate if the next step can be reached
* `workflow.PROCESS_NAME.STEP_NAME.validation_fail` used when validation has failed
* `workflow.PROCESS_NAME.STEP_NAME.STATE_NAME.pre_validation` called before the process handler tries to validate
* `workflow.PROCESS_NAME.STEP_NAME.STATE_NAME.pre_validation_fail` called if pre_validation fails

Usually the services register their listeners for themselve. But their is not limit that you have to use any services.

Usage
--------------

Usually the Connectors auto connects the workflow engine to a given table. But you could also use your custom module for
triggering the workflow engine

```php
<?php
public function callbackSubmit(\DcGeneral\DC_General $dc)
{
	/** @var \Workflow\Controller\Controller $controller */
	$controller = $GLOBALS['container']['workflow.controller'];
	$entity		= $dc->getEnvironment()->getCurrentModel(); // DC_General models are called entity to avoid naming confusions with workflow models
	$state      = $controller->initialize($entity);

	// state is null when workflow could not be started or no workflow was found
	if($state)
	{
		if($state->getSuccessFul())
		{
			// .. do something here
		}
		else {
			// log error and redirec to contao/main.php?act=error
			\DcaTools\Controller::error(implode(',', $state->getErrors()));
		}
	}
}
```


Requirements
--------------
TODO

Installation
------------
TODO

License
-------

Please note https://github.com/lexik/LexikWorkflowBundle/blob/master/LICENSE