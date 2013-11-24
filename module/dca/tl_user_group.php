<?php
/**
 * Created by PhpStorm.
 * User: david
 * Date: 29.10.13
 * Time: 22:45
 */


// onload_callback does not accept dynamically created field definitions AND dynamically added fields
// no glue why. no bug in DcaTools. Also happens with single string adding

//$GLOBALS['TL_DCA']['tl_user_group']['config']['onload_callback'][] = array('Workflow\Contao\Dca\UserGroup', 'initialize');
if(class_exists('\Workflow\Contao\Dca\UserGroup'))
{
	\Workflow\Contao\Dca\UserGroup::initialize();
}