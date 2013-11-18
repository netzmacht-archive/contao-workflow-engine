<?php
/**
 * Created by PhpStorm.
 * User: david
 * Date: 15.11.13
 * Time: 16:33
 */

namespace Workflow\Contao\Dca;

use DcaTools\Data\ConfigBuilder;

class NewsArchive extends Generic
{
	/**
	 * @var static
	 */
	protected static $instance;

	public function getWorkflows()
	{
		$driver    = $this->manager->getDataProvider('tl_workflow');
		$workflows = array();
		$builder   = ConfigBuilder::create($driver)
			->filterEquals('workflow', 'news')
			->fields('title', 'workflow')
			->sorting('title');

		foreach($builder->fetchAll() as $workflow)
		{
			$workflows[$workflow->getId()] = sprintf('%s [%s]', $workflow->getProperty('title'), $workflow->getProperty('workflow'));
		}

		return $workflows;
	}
}