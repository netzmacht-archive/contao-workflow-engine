<?php

namespace Workflow\Service;

use DcaTools\Controller;
use DcaTools\Definition;
use DcaTools\Event\GenerateEvent;
use DcaTools\Listener\ListenerHelper;
use DcGeneral\Data\ModelInterface as EntityInterface;


/**
 * Class ItemRestrictions
 * @package Workflow\Service
 */
class ItemRestrictions extends AbstractService
{
	protected static $config = array
	(
		'name'      => 'item-restrictions',
		'drivers'   => array('Table'),
		'config'    => array
		(
			'scope'  => array('steps', 'roles'),
			'filter' => array('addFilter'),
			'config' => array('table_operations', 'actions'),
		),
	);

	protected $restrictions;

	/**
	 * Initialize the workflow service
	 *
	 * @inheritdoc
	 */
	function initialize()
	{
		$this->checkCredentials();
		//$this->controller->getEventDispatcher()->addListener('workflow.check_credentials', array($this, 'checkCredentials'));

		/** @var \DcaTools\Definition\DataContainer $definition */
		$controller  = Controller::getInstance($this->service->getProperty('tableName'));
		$definition  = $controller->getDefinition();
		$operations  = deserialize($this->service->getProperty('table_operations'), true);

		foreach($operations as $operation)
		{
			if($definition->hasOperation($operation))
			{
				$service  = $this;
				$listener = array('\DcaTools\Listener\OperationListener', 'disableIcon');
				$config   = array();
				$config['callback'] = function(GenerateEvent $event) use($service)
				{
					return $this->isAssigned($event->getSubject()->getModel());
				};

				$listener = ListenerHelper::createConfigurableListener($listener, $config);
				$controller->addOperationListener($operation, $listener);
			}
		}
	}


	/**
	 *
	 */
	protected function checkCredentials()
	{
		if($this->isAssigned($this->controller->getCurrentModel()->getEntity()) && $this->applyRequestAction())
		{
			/** @var \BackendUser $user */
			$user    = \BackendUser::getInstance();
			$message = 'User "%s (%s)" has not enough permission to run action "%s"';

			\DcaTools\Controller::error(sprintf($message, $user->username, $user->id, $this->controller->getRequestAction()));
		}
	}


	/**
	 * Check whether service assigned to current entity
	 *
	 * @param EntityInterface $entity
	 * @return bool|mixed
	 */
	public function isAssigned(EntityInterface $entity)
	{
		return parent::isAssigned($entity) && $this->applySteps($entity) && $this->applyRoles() && $this->applyFilter($entity);
	}

}
