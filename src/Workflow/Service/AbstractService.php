<?php
/**
 * Created by PhpStorm.
 * User: david
 * Date: 01.11.13
 * Time: 23:27
 */

namespace Workflow\Service;

use DcaTools\Translator;
use DcGeneral\Data\ModelInterface;
use Symfony\Component\EventDispatcher\EventDispatcher;
use Workflow\Handler\Environment;

abstract class AbstractService implements ServiceInterface
{

	/**
	 * @var \DcGeneral\Data\ModelInterface
	 */
	protected $model;


	/**
	 * @var Environment
	 */
	protected $environment;


	/**
	 * @param ModelInterface $model
	 * @param Environment $environment
	 */
	public function __construct(ModelInterface $model, Environment $environment)
	{
		$this->model = $model;
		$this->environment = $environment;

		$this->model->setProperty('steps', deserialize($this->model->getProperty('steps'), true));
		$this->model->setProperty('events', deserialize($this->model->getProperty('events'), true));
	}


	/**
	 * @inheritdoc
	 */
	abstract function initialize();


	/**
	 * @param \Workflow\Model\ModelInterface $model
	 * @return array
	 */
	protected function getModelProperties(\Workflow\Model\ModelInterface $model)
	{
		$entity = $model->getEntity();
		$translator = Translator::instantiate($entity->getProviderName());
		$properties = array();

		foreach($this->model->getProperty('model_properties') as $property)
		{
			if($entity->getProperty($property) !== null)
			{
				$properties[$property]['label'] = $translator->property($property);
				$properties[$property]['value'] = $translator->value($property, $entity->getProperty($property), '-');
			}
		}

		return $properties;
	}

}
