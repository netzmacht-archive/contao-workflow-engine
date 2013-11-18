<?php

namespace Workflow\Controller;

use DcGeneral\Data\ModelInterface as EntityInterface;

/**
 * Interface WorkflowInterface
 * @package Workflow\Workflow
 */
interface WorkflowInterface
{
	const PUBLISH_MODE_UNSUPPORTED = 0;

	const PUBLISH_MODE_DEFAULT = 1;

	const PUBLISH_MODE_INVERTED = 2;


	/**
	 * @param EntityInterface $entity
	 */
	public function __construct(EntityInterface $entity);


	/**
	 * @return mixed
	 */
	public static function getIdentifier();


	/**
	 * Bootstrap the workflow will be called no matter if workflow is assigned to an entity or not
	 *
	 * Useful for registering some events
	 *
	 * @param Controller $controller
	 */
	public static function bootstrap(Controller $controller);


	/**
	 * Get tables which are supported by the workflow
	 *
	 * @return array
	 */
	public static function getSupportedTables();


	/**
	 * @param $tableName
	 * @return mixed
	 */
	public static function getConfig($tableName);


	/**
	 * @return mixed
	 */
	public function initialize();


	/**
	 * @return EntityInterface
	 */
	public function getEntity();


	/**
	 *
	 */
	public function getProcessConfiguration();


	/**
	 * @param EntityInterface $entity
	 * @return mixed
	 */
	public function isAssigned(EntityInterface $entity);


	/**
	 * @param EntityInterface $entity
	 * @return mixed
	 */
	public function getPriority(EntityInterface $entity);


	/**
	 * @param Controller $controller
	 */
	public function setController(Controller $controller);


	/**
	 * @return Controller
	 */
	public function getController();


	/**
	 * @param $tableName
	 * @return \Workflow\Handler\ProcessHandlerInterface
	 */
	public function getProcessHandler($tableName);


	/**
	 * Get workflow data
	 *
	 * @param EntityInterface $entity
	 * @return mixed
	 */
	public function getWorkflowData(EntityInterface $entity);


	/**
	 * @param EntityInterface $entity
	 * @param $tableName=null
	 * @return EntityInterface|null
	 */
	public function getParent(EntityInterface $entity, $tableName=null);

}