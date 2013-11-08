<?php
/**
 * Created by PhpStorm.
 * User: david
 * Date: 01.11.13
 * Time: 14:17
 */

namespace Workflow\Contao\Dca;


use DcaTools\Translator;

class Process
{

	public function getSteps($dc)
	{
		$results = \Database::getInstance()->prepare('SELECT name,label,id FROM tl_workflow_step WHERE pid=?')->execute($dc->id);
		$steps = array();

		while($results->next())
		{
			if($results->label)
			{
				$steps[$results->id] = sprintf('%s [$s]', $results->label, $results->name);
			}
			else {
				$steps[$results->id] = $results->name;
			}
		}

		return $steps;
	}


	/**
	 * Generate step list, called by dcawizard
	 *
	 * @param $records
	 * @param $id
	 * @return string
	 */
	public function generateStepList($records, $id)
	{

		$template = new \BackendTemplate('be_workflow_steplist');
		$template->records = $records;

		$translator = Translator::instantiate('tl_workflow_step');

		$template->stepLabel = $translator->property('label');
		$template->rolesLabel = $translator->property('roles');
		$template->endLabel = $translator->property('end');
		$template->startLabel = $translator->property('start');

		$template->steps = $GLOBALS['TL_LANG']['workflow']['steps'];

		$template->yes = $GLOBALS['TL_LANG']['MSC']['yes'];
		$template->no = '-';

		return $template->parse();
	}


	public function getRoutines()
	{
		$routines = array();

		foreach($GLOBALS['TL_WORKFLOW']['routines'] as $routine => $config)
		{
			if($config['tables'] == '*')
			{
				$tables = $GLOBALS['TL_LANG']['tl_workflow_process']['forAllTables'];
			}
			else {
				$tables = implode(', ', $config['tables']);
			}

			$routines[$routine] = sprintf(
				'%s <br><span class="description">%s</span><br><span class="tl_gray">%s: %s</span>',
				$config['description'][0] ?: $routine,
				$config['description'][1],
				$GLOBALS['TL_LANG']['tl_workflow_process']['forTables'],
				$tables
			);
		}

		return $routines;
	}

}