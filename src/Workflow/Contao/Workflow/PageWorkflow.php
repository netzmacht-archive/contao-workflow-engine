<?php

namespace Workflow\Contao\Workflow;

use DcaTools\Data\ConfigBuilder;
use DcaTools\Definition;
use DcGeneral\Data\ModelInterface as EntityInterface;
use Workflow\Controller\Controller;
use Workflow\Event\WorkflowTypeEvent;
use Workflow\Controller\AbstractWorkflow;


class PageWorkflow extends AbstractWorkflow
{


	/**
	 * Bootstrap Page workflow
	 *
	 * @param Controller $controller
	 */
	public static function bootstrap(Controller $controller)
	{
		$controller->getEventDispatcher()->addListener('workflow.controller.get-workflow-types', array(__CLASS__, 'listenerGetWorkflowType'));

		$article    = \DcaTools\Controller::getInstance('tl_article');
		$article->enableLabelEvents();
	}


	/**
	 * Listener for get workflow type event
	 *
	 * @param WorkflowTypeEvent $event
	 */
	public static function listenerGetWorkflowType(WorkflowTypeEvent $event)
	{
		$providerName = $event->getEntity()->getProviderName();

		if(in_array($providerName, array('tl_page', 'tl_article')))
		{
			$event->addType(static::getIdentifier());
		}
		elseif($providerName == 'tl_content' && in_array($event->getEntity()->getProperty('ptable'), array('', 'tl_article')))
		{
			$event->addType(static::getIdentifier());
		}
	}


	/**
	 * Get Identifier
	 * @return string
	 */
	public static function getIdentifier()
	{
		return 'page';
	}


	/**
	 * Get supported tables
	 *
	 * @return array
	 */
	public static function getSupportedTables()
	{
		return array('tl_page', 'tl_article', 'tl_content');
	}


	/**
	 * Get config for a supported entity
	 *
	 * @param $tableName
	 * @return array|bool
	 */
	public static function getConfig($tableName)
	{
		switch($tableName)
		{
			case 'tl_content':
				return array
				(
					'parent'        => 'tl_article',
					'publishColumn' => 'invisible',
					'publishMode'   => static::PUBLISH_MODE_INVERTED,
					'ownerColumn'   => false,
				);
				break;

			case 'tl_article':
				return array
				(
					'parent'        => 'tl_page',
					'publishColumn' => 'published',
					'publishMode'   => static::PUBLISH_MODE_DEFAULT,
					'ownerColumn'   => 'author',
				);
				break;

			case 'tl_page':
				return array
				(
					'parent'        => false,
					'publishColumn' => 'published',
					'publishMode'   => static::PUBLISH_MODE_DEFAULT,
					'ownerColumn'   => 'cuser',
				);
				break;

			default:
				return false;
		}
	}


	/**
	 * Initialize the workflow
	 */
	public function initialize()
	{
		$this->initializeServices();

		var_dump('HURRA');
	}


	/**
	 * Get workflow entity
	 *
	 * @return EntityInterface
	 */
	public function getEntity()
	{
		return $this->workflow;
	}


	/**
	 * Consider whether model is assigned to workflow
	 *
	 * @param EntityInterface $entity
	 * @return bool
	 */
	public function isAssigned(EntityInterface $entity)
	{
		if(isset($this->processes[$entity->getProviderName()]))
		{
			switch($entity->getProviderName())
			{
				case 'tl_page':
					return $this->isPageAssigned($entity);
					break;

				case 'tl_article':
					return $this->isArticleAssigned($entity);
					break;

				case 'tl_content':
					return $this->isContentElementAssigned($entity);
					break;
			}
		}

		return false;
	}


	/**
	 * Consider whether page is assigned
	 *
	 * @param EntityInterface $entity
	 * @return bool
	 */
	protected function isPageAssigned(EntityInterface $entity)
	{
		$driver = $this->controller->getDataProvider('tl_page');

		while($entity->getProperty('addWorkflow') == '' && $entity->getProperty('pid') > 0)
		{
			$entity = ConfigBuilder::create($driver)
				->fields('workflow', 'addWorkflow', 'pid')
				->setId($entity->getProperty('pid'))
				->fetch();
		}

		return ($entity->getProperty('addWorkflow') && $entity->getProperty('workflow') == $this->workflow->getId());
	}


	/**
	 * Consider whether article is assigned
	 *
	 * @param EntityInterface $entity
	 * @return bool
	 */
	protected function isArticleAssigned(EntityInterface $entity)
	{
		$driver = $this->controller->getDataProvider('tl_page');

		$page = ConfigBuilder::create($driver)
			->fields('workflow', 'addWorkflow', 'pid')
			->setId($entity->getProperty('pid'))
			->fetch();

		if($page)
		{
			return $this->isPageAssigned($page);
		}

		return false;
	}


	/**
	 * Consider whether content element is assigned
	 *
	 * @param EntityInterface $entity
	 * @return bool
	 */
	protected function isContentElementAssigned(EntityInterface $entity)
	{
		$driver = $this->controller->getDataProvider('tl_article');

		$article = ConfigBuilder::create($driver)
			->field('pid')
			->setId($entity->getProperty('pid'))
			->fetch();

		if($article)
		{
			return $this->isArticleAssigned($article);
		}

		return false;
	}


	/**
	 * Get priority of given Model
	 *
	 * @param EntityInterface $entity
	 * @return int|mixed
	 */
	public function getPriority(EntityInterface $entity)
	{
		$tables   = array('tl_page', 'tl_article', 'tl_content');
		$priority = array_search($entity->getProviderName(), $tables);

		if($priority === false)
		{
			return -1;
		}

		return $priority;
	}


	/**
	 * Get workflow Data
	 *
	 * @param EntityInterface $entity
	 * @return array|mixed
	 */
	public function getWorkflowData(EntityInterface $entity)
	{
		return array();
	}

}
