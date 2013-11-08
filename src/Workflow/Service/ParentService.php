<?php
/**
 * Created by PhpStorm.
 * User: david
 * Date: 08.11.13
 * Time: 11:36
 */

namespace Workflow\Service;


use DcaTools\Definition;
use DcaTools\Model\FilterBuilder;
use Workflow\Controller\WorkflowFactory;
use Workflow\Exception\WorkflowException;
use Workflow\Handler\ProcessHandler;
use Workflow\Model\Model;

class ParentService extends AbstractService
{
	protected static $config = array
	(
		'identifier' => 'parent',
		'version'    => '1.0.0',
		'properties' => array(),
	);

	/**
	 * @inheritdoc
	 */
	function initialize()
	{
		$table = $this->controller->getWorkflow()->getTable();

		$parentId = $this->controller->getModel()->getEntity()->getProperty('pid');
		$parentTable = Definition::getDataContainer($table)->get('config/ptable');
		$driver = $this->controller->getDriverManager()->getDataProvider($parentTable);

		$config = $driver->getEmptyConfig();
		$config->setId($parentId);

		$entity = $driver->fetch($config);

		if(!$entity)
		{
			throw new WorkflowException('Huh, could not load parent');
		}

		$controller = WorkflowFactory::createController($entity);
		$controller->initialize();
	}
}
