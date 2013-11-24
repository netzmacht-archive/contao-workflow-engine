<?php

namespace Workflow\Controller;

use DcGeneral\Data\ModelInterface as EntityInterface;

/**
 * Interface UserInterface
 * @package Workflow\User\UserInterface
 */
interface UserInterface
{
	/**
	 * Contao Admins does not have any restrictions. The workflow uses the admin role so that the workflow can also
	 * be limited for admins
	 */
	const ROLE_ADMIN = 'admin';

	/**
	 * Some tables use author/owner column. The role owner is used for auto adding the role to the user
	 */
	const ROLE_OWNER = 'owner';


	/**
	 * Get roles of current user
	 *
	 * @param $processName
	 * @return bool
	 */
	public function getRoles($processName);


	/**
	 * Check role of current user
	 *
	 * @param string $processName
	 * @param array|string $roles
	 * @return bool
	 */
	public function hasRole($processName, $roles);


	/**
	 * Check whether current user is owner of passed entity
	 *
	 * @param WorkflowInterface $workflow
	 * @param EntityInterface $entity
	 * @return bool
	 */
	public function isOwner(WorkflowInterface $workflow, EntityInterface $entity);

} 