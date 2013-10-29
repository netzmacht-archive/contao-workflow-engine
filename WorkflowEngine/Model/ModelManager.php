<?php
/**
 * Created by PhpStorm.
 * User: david
 * Date: 29.10.13
 * Time: 10:39
 */

namespace WorkflowEngine\Model;

use DcGeneral\Data\DCGE;
use DcGeneral\Data\DefaultDriver;
use DcGeneral\Data\ModelInterface;


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
	 * @var \DcGeneral\Data\DriverInterface[]
	 */
	protected $drivers = array();


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
	public function flush()
	{
		foreach($this->models as $model)
		{
			if($model->getMeta(static::MODEL_DELETE))
			{
				$driver = $this->getDataProvider($model->getProviderName());
				$driver->delete($model);

				$model->setMeta(DCGE::MODEL_IS_CHANGED, false);
				$model->setMeta(static::MODEL_IS_DELETED, true);
				$model->setMeta(static::MODEL_DELETE, false);
			}
			elseif($model->getMeta(DCGE::MODEL_IS_CHANGED))
			{
				$driver = $this->getDataProvider($model->getProviderName());
				$driver->save($model);

				$model->setMeta(DCGE::MODEL_IS_CHANGED, false);
			}
		}
	}


	/**
	 * @param $table
	 *
	 * @return \DcGeneral\Data\DriverInterface
	 */
	public function getDataProvider($table)
	{
		// FIXME: We need to get them from the DcGeneral, we should at least recognize the config

		if(!isset($this->drivers[$table]))
		{
			$driver = new DefaultDriver();
			$driver->setBaseConfig(array('source' => $table));

			$this->drivers[$table] = $driver;
		}

		return $this->drivers[$table];
	}
} 