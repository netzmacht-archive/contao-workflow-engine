<?php
/**
 * Created by PhpStorm.
 * User: david
 * Date: 22.11.13
 * Time: 15:35
 */

namespace Workflow\Contao\Dca;

use DcaTools\Data\ModelFactory;
use DcaTools\Definition;

class UserGroup
{

	public static function initialize()
	{
		$processes  = \Database::getInstance()->execute('SELECT * FROM tl_workflow_process ORDER BY name');
		$definition = \DcaTools\Definition::getDataContainer('tl_user_group');

		if(!$definition->getPalette('default')->hasLegend('workflow'))
		{
			$legend = $definition->getPalette('default')->createLegend('workflow', 'alexf', \DcaTools\Definition::BEFORE);
		}
		else {
			$legend = $definition->getPalette('default')->getLegend('workflow');
		}

		$legend->addProperty('name');

		while($processes->next())
		{
			$process = ModelFactory::byResult('tl_workflow_process', $processes);

			$fieldName = 'workflow_' . $process->getProperty('name');

			$label = $GLOBALS['TL_LANG']['workflow']['process'][$process->getProperty('name')][0] ?: $process->getProperty('name');
			$label = array($label, sprintf($GLOBALS['TL_LANG']['tl_user_group']['workflow'][1], $label));

			$roles = trimsplit(',', $process->getProperty('roles'));
			$roles = array_filter($roles, function($value) { return ($value != 'owner'); });

			$GLOBALS['TL_DCA']['tl_user_group']['fields'][$fieldName] = array
			(
				'inputType'     => 'checkbox',
				'exclude'       => true,
				'label'         => $label,
				'options'       => array_values($roles),
				//'reference'     => &$GLOBALS['TL_LANG']['tl_user_group']['roles'],
				'eval'          => array('multiple' => true, 'helpwizard' => true),
				'sql'           => 'mediumblob NULL',
			);

			$legend->addProperty($fieldName);

			if(!\Database::getInstance()->fieldExists($fieldName, 'tl_user_group'))
			{
				try {
					\Database::getInstance()->execute(sprintf('ALTER TABLE tl_user_group ADD %s mediumblob NULL', $fieldName));
				}
				catch(\Exception $e) {
					\Controller::log($e->getMessage(), 'UserGroup initialize', TL_ERROR);
				}
			}

			if(!\Database::getInstance()->fieldExists($fieldName, 'tl_user'))
			{
				try {
					\Database::getInstance()->execute(sprintf('ALTER TABLE tl_user ADD %s mediumblob NULL', $fieldName));
				}
				catch(\Exception $e) {
					\Controller::log($e->getMessage(), 'UserGroup initialize', TL_ERROR);
				}
			}
		}
	}

} 