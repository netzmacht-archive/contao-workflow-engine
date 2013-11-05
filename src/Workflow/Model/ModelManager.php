<?php
/**
 * Created by PhpStorm.
 * User: david
 * Date: 29.10.13
 * Time: 10:39
 */

namespace Workflow\Model;

use DcGeneral\Data\DCGE;
use Workflow\Data\DriverManagerInterface;


/**
 * Class ModelManager
 * @package Workflow\Model
 */
class ModelManager
{

	/**
	 * is deleted state
	 */
	const MODEL_IS_DELETED = 'isDeleted';

	/**
	 * force deleting
	 */
	const MODEL_DELETE     = 'delete';


	/**
	 * @var \DcGeneral\Data\ModelInterface[]
	 */
	protected $models = array();


	/**
	 * @var DriverManagerInterface
	 */
	protected $driverManager;


	/**
	 * @param DriverManagerInterface $driverManager
	 */
	public function __construct($driverManager)
	{
		$this->driverManager = $driverManager;
	}


	/**
	 * Add a model to the model manager
	 *
	 * @param \DcGeneral\Data\ModelInterface $model
	 */
	public function persist(\DcGeneral\Data\ModelInterface $model)
	{
		$this->models[] = $model;
	}


	/**
	 * flush changed
	 */
	public function flush(\DcGeneral\Data\ModelInterface $model=null)
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
	 * @param \DcGeneral\Data\ModelInterface $model
	 */
	protected function doFlush(\DcGeneral\Data\ModelInterface $model)
	{
		$driver = $this->driverManager->getDataProvider($model->getProviderName());

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
