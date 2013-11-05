<?php
/**
 * Created by PhpStorm.
 * User: david
 * Date: 29.10.13
 * Time: 22:45
 */

/** @var \Workflow\Config\Config $config */
/*
$config = $GLOBALS['container']['workflow.registry']->getConfig();
$providers = $config->getDataProviders();

if(count($providers))
{
	$legend = \DcaTools\Definition::getDataContainer('tl_user_group')
		->getPalette('default')
		->createLegend('workflow')
		->appendAfter('faq');

	foreach($providers as $provider)
	{
		$field = sprintf('workflow_%s_%s', $provider->getModuleName(), $provider->getName());
		$label = array($provider->getLabel(), sprintf(
			$GLOBALS['TL_LANG']['tl_user_group']['workflow'][1],
			$GLOBALS['TL_LANG']['MOD'][$provider->getModuleName()][0] ?: $provider->getModuleName(),
			$provider->getName()
		));

		$roles = $GLOBALS['container']['workflow.registry']->getProcess($provider->getProcessName())->getRoles();

		if(count($roles) && !isset($GLOBALS['TL_DCA']['tl_user_group']['fields'][$field]))
		{
			$GLOBALS['TL_DCA']['tl_user_group']['fields'][$field] = array
			(
				'inputType'     => 'checkbox',
				'exclude'       => true,
				'label'         => $label,
				'options'       => $roles,
				'reference'     => &$GLOBALS['TL_LANG']['tl_user_group']['roles'],
				'eval'          => array('multiple' => true, 'helpwizard' => true),
				'sql'           => 'mediumblob NULL',
			);

			$legend->addProperty($field);
		}
	}
}

*/