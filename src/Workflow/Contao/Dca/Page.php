<?php
/**
 * Created by PhpStorm.
 * User: david
 * Date: 15.11.13
 * Time: 16:33
 */

namespace Workflow\Contao\Dca;


use DcaTools\Data\ConfigBuilder;

class Page extends Generic
{

	public function getWorkflows()
	{
		$driver    = $this->manager->getDataProvider('tl_workflow');
		$workflows = array();
		$builder   = ConfigBuilder::create($driver)
			->filterEquals('workflow', 'page')
			->field('title')
			->sorting('title');

		foreach($builder->fetchAll() as $workflow)
		{
			$workflows[$workflow->getId()] = $workflow->getProperty('title');
		}

		return $workflows;
	}

} 