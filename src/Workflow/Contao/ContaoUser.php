<?php

namespace Workflow\Contao;

use DcGeneral\Data\ModelInterface as EntityInterface;
use Workflow\Controller\UserInterface;
use Workflow\Controller\WorkflowInterface;

/**
 * Class ContaoUser
 * @package Workflow\User\UserInterface
 */
class ContaoUser implements UserInterface
{

	/**
	 * @var \BackendUser
	 */
	protected $user;


	/**
	 * Construct
	 */
	public function __construct()
	{
		$this->user = \BackendUser::getInstance();
	}


	/**
	 * Get roles of current user
	 *
	 * @param $processName
	 * @return array
	 */
	public function getRoles($processName)
	{
		$key   = 'workflow_' . $processName;
		$roles = $this->user->$key;

		if(!is_array($roles))
		{
			$roles = array();
		}

		if($this->user->isAdmin)
		{
			$roles[] = static::ROLE_ADMIN;
		}

		return $roles;
	}


	/**
	 * Check role of current user
	 *
	 * @param $processName
	 * @param array|string $roles
	 * @return bool
	 */
	public function hasRole($processName, $roles)
	{
		if(is_string($roles))
		{
			$roles = array($roles);
		}

		return count(array_intersect($roles, $this->getRoles($processName))) > 0;
	}


	/**
	 * Check whether current user is author of passed entity
	 *
	 * @param WorkflowInterface $workflow
	 * @param EntityInterface $entity
	 * @return false
	 */
	public function isOwner(WorkflowInterface $workflow, EntityInterface $entity)
	{
		$config = $workflow->getConfig($entity->getProviderName());

		if(!$config || !$config['ownerColumn'] || !$entity->getProperty($config['ownerColumn']))
		{
			return false;
		}

		return ($entity->getProperty($config['ownerColumn']) == $this->user->id);
	}

}
