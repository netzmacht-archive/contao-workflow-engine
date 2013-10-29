<?php
/**
 * Created by PhpStorm.
 * User: david
 * Date: 29.10.13
 * Time: 10:39
 */

namespace WorkflowEngine\Model;

use DcGeneral\Data\DCGE;
use DcGeneral\Data\ModelInterface;
use WorkflowEngine\Registry;


/**
 * Class ModelManager
 * @package WorkflowEngine\Model
 */
class ModelManager
{
	const MODEL_IS_DELETED = 'isDeleted';

	const MODEL_DELETE     = 'delete';

	/**
	 * @var ModelInterface[]
	 */
	protected $models = array();


	/**
	 * @var Registry
	 */
	protected $registry;


	public function __construct(Registry $registry)
	{
		$this->registry = $registry;
	}


	/**
	 * Add a model to the model manager
	 *
	 * @param ModelInterface $model
	 */
	public function persist(ModelInterface $model)
	{
		$this->models[] = $model;
	}


	/**
	 * flush changed
	 */
	public function flush(ModelInterface $model=null)
	{
		if($model === null)
		{
			foreach($this->models as $model)
			{
				$this->doFlush($model);
			}
		}
		else {
			$this->doFlush($model);
		}

	}


	/**
	 * @param ModelInterface $model
	 */
	protected function doFlush(ModelInterface $model)
	{
		$driver = $this->registry->getDataProvider($model->getProviderName());

		if($model->getMeta(static::MODEL_DELETE))
		{
			$driver->delete($model);

			$model->setMeta(DCGE::MODEL_IS_CHANGED, false);
			$model->setMeta(static::MODEL_IS_DELETED, true);
			$model->setMeta(static::MODEL_DELETE, false);
		}
		elseif($model->getMeta(DCGE::MODEL_IS_CHANGED))
		{
			$model->setProperty('tstamp', time());
			$driver->save($model);

			$model->setMeta(DCGE::MODEL_IS_CHANGED, false);
		}
	}
} 