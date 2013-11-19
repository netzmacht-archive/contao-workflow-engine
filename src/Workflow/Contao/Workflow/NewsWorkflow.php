<?php

namespace Workflow\Contao\Workflow;

use DcaTools\Data\ConfigBuilder;
use DcaTools\Definition;
use DcGeneral\Data\ModelInterface as EntityInterface;
use Workflow\Controller\AbstractWorkflow;
use Workflow\Event\WorkflowTypeEvent;
use Workflow\Model\Model;


class NewsWorkflow extends AbstractWorkflow
{

	/**
	 * Listener for get workflow type event
	 *
	 * @param WorkflowTypeEvent $event
	 */
	public static function listenerGetWorkflowType(WorkflowTypeEvent $event)
	{
		$providerName = $event->getEntity()->getProviderName();

		if(in_array($providerName, array('tl_news_archive', 'tl_news')))
		{
			$event->addType(static::getIdentifier());
		}
		elseif($providerName == 'tl_content' && $event->getEntity()->getProperty('ptable') == 'tl_news')
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
		return 'news';
	}


	/**
	 * Get supported tables
	 *
	 * @return array
	 */
	public static function getSupportedTables()
	{
		return array('tl_news_archive', 'tl_news', 'tl_content');
	}


	public static function getConfig($tableName)
	{
		switch($tableName)
		{
			case 'tl_content':
				return array
				(
					'parent'        => 'tl_news',
					'publishColumn' => 'invisible',
					'publishMode'   => static::PUBLISH_MODE_INVERTED,
					'ownerColumn'   => false,
				);
				break;

			case 'tl_news':
				return array
				(
					'parent'        => 'tl_news_archive',
					'publishColumn' => 'published',
					'publishMode'   => static::PUBLISH_MODE_DEFAULT,
					'ownerColumn'   => 'author',
				);
				break;

			case 'tl_news_archive':
				return array
				(
					'parent'        => false,
					'publishColumn' => 'published',
					'publishMode'   => static::PUBLISH_MODE_UNSUPPORTED,
					'ownerColumn'   => false,
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
				case 'tl_news_archive':
					return $this->isNewsArchiveAssigned($entity);
					break;

				case 'tl_news':
					return $this->isNewsAssigned($entity);
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
	protected function isNewsArchiveAssigned(EntityInterface $entity)
	{
		return ($entity->getProperty('addWorkflow') && $entity->getProperty('workflow') == $this->workflow->getId());
	}


	/**
	 * Consider whether article is assigned
	 *
	 * @param EntityInterface $entity
	 * @return bool
	 */
	protected function isNewsAssigned(EntityInterface $entity)
	{
		$page = $this->loadParent($entity);

		if($page)
		{
			return $this->isNewsArchiveAssigned($page);
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
		$news = $this->loadParent($entity);

		if($news)
		{
			return $this->isNewsAssigned($news);
		}

		return false;
	}


	/**
	 * Get priority of given Model
	 *
	 * @param EntityInterface $model
	 * @return int|mixed
	 */
	public function getPriority(EntityInterface $model)
	{
		$tables   = array('tl_news_archive', 'tl_news', 'tl_content');
		$priority = array_search($model->getProviderName(), $tables);

		if($priority === false)
		{
			return -1;
		}

		return $priority;
	}


	/**
	 * @param EntityInterface $entity
	 *
	 * @return array
	 */
	public function getWorkflowData(EntityInterface $entity)
	{
		switch($entity->getProviderName())
		{
			case 'tl_news_archive':
				return $this->getNewsArchiveWorkflowData($entity);
				break;
			case 'tl_news':
				return $this->getNewsWorkflowData($entity);
				break;

			case 'tl_content':
				return $this->getContentElementWorkflowData($entity);
				break;
		}

		return null;
	}


	/**
	 * Get workflow data for news
	 *
	 * @param EntityInterface $entity
	 * @return array
	 */
	protected function getNewsWorkflowData(EntityInterface $entity)
	{
		$data = $entity->getPropertiesAsArray();

		$driver  = $this->controller->getDataProvider('tl_content');
		$builder = ConfigBuilder::create($driver)
			->filterEquals('pid', $entity->getId())
			->filterEquals('ptable', $entity->getProviderName())
			->sorting('sorting');

		/** @var EntityInterface $child */
		foreach($builder->fetchAll() as $child)
		{
			$model   = new Model($child, $this->controller);
			$handler = $this->controller->getCurrentWorkflow()->getProcessHandler($child->getProviderName());
			$state   = $handler->getCurrentState($model);

			if(!$state)
			{
				$state = $handler->start($model);
			}

			$data['_children']['tl_content'][] = $state->getId();
		}

		return $data;
	}


	/**
	 * Get workflow data for content element
	 *
	 * @param EntityInterface $entity
	 * @return array
	 */
	protected function getContentElementWorkflowData(EntityInterface $entity)
	{
		return $entity->getPropertiesAsArray();
	}


	/**
	 * Get all workflow data for whole archive
	 *
	 * @param EntityInterface $entity
	 * @return array
	 */
	protected function getNewsArchiveWorkflowData(EntityInterface $entity)
	{
		$data    = $entity->getPropertiesAsArray();
		$driver  = $this->controller->getDataProvider('tl_news');
		$builder = ConfigBuilder::create($driver)
			->filterEquals('pid', $entity->getId());

		/** @var EntityInterface $news */
		foreach($builder->fetchAll() as $news)
		{
			$model   = new Model($news, $this->controller);
			$handler = $this->controller->getCurrentWorkflow()->getProcessHandler($news->getProviderName());
			$state   = $handler->getCurrentState($model);

			if(!$state)
			{
				$state = $handler->start($model);
			}

			$data['_children']['tl_news'][] = $state->getId();
		}

		return $data;
	}

}
