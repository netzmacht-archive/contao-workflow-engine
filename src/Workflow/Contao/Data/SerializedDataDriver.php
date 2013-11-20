<?php

namespace Workflow\Contao\Data;

use DcGeneral\Data\ConfigInterface;
use DcGeneral\Data\DefaultDriver;
use DcGeneral\Data\ModelInterface;


class SerializedDataDriver extends DefaultDriver
{
	const DATA_FORMAT_SERIALIZED = 'serialize';

	const DATA_FORMAT_JSON = 'json';

	const MODEL_ORIGIN = 'originModel';

	protected $dataField;

	protected $dataFormat;


	/**
	 * @param array $config
	 * @throws
	 */
	public function setBaseConfig(array $config)
	{
		parent::setBaseConfig($config);

		if(!isset($config['data_field']) || !isset($config['data_format']))
		{
			throw new \RuntimeException('Invalid driver configuration');
		}

		$this->dataField  = $config['data_field'];
		$this->dataFormat = $config['data_format'];
	}


	/**
	 * @param ConfigInterface $config
	 * @return \DcGeneral\Data\ModelInterface
	 */
	public function fetch(ConfigInterface $config)
	{
		$model = parent::fetch($config);

		if($model)
		{
			$data = $this->restoreData($model->getProperty($this->dataField));

			$new  = $this->getEmptyModel();
			$new->setId($model->getId());
			$new->setPropertiesAsArray($data);
			$new->setMeta(static::MODEL_ORIGIN, $model);

			return $new;
		}

		return $model;
	}


	/**
	 * @param ConfigInterface $config
	 * @return \DcGeneral\Data\CollectionInterface
	 */
	public function fetchAll(ConfigInterface $config)
	{
		$collection = $this->getEmptyCollection();

		/** @var \DcGeneral\Data\ModelInterface $item */
		foreach(parent::fetchAll($config) as $item)
		{
			$data = $this->restoreData($item->getProperty($this->dataField));

			$new = $this->getEmptyModel();
			$new->setId($item->getId());
			$new->setPropertiesAsArray($data);
			$new->setMeta(static::MODEL_ORIGIN, $item);

			$collection->add($new);
		}

		return $collection;
	}


	/**
	 * @param ModelInterface $item
	 * @return ModelInterface|void
	 */
	public function save(ModelInterface $item)
	{
		$properties = $item->getPropertiesAsArray();

		$new = $this->getEmptyModel();
		$new->setPropertiesAsArray($properties);
		$new->setId($item->getId());

		return parent::save($item);
	}


	/**
	 * @param $data
	 * @return mixed|null
	 */
	protected function restoreData($data)
	{
		switch($this->dataFormat)
		{
			case static::DATA_FORMAT_JSON:
				$data = json_decode($data, true);
				break;

			case static::DATA_FORMAT_SERIALIZED:
				$data = deserialize($data, true);
				break;

			default:
				$data = array();
		}

		if(isset($data['id']))
		{
			$data['__id__'] = $data['id'];
			unset($data['id']);
		}

		return $data;
	}

} 