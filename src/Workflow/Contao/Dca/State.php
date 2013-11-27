<?php

namespace Workflow\Contao\Dca;

use DcaTools\Helper\Formatter;
use DcGeneral\Contao\BackendBindings;
use Workflow\Contao\Data\SerializedDataDriver;


/**
 * Class State
 * @package Workflow\Contao\Dca
 * @author David Molineus <molineus@netzmacht.de>
 */
class State extends Generic
{

	/**
	 * Select driver and prepare parent view
	 *
	 * Has to be done before DC_General sets an hardcopy of the dca array
	 * @see https://github.com/MetaModels/DC_General/pull/112
	 *
	 * @param $tableName
	 */
	public function selectDriver($tableName)
	{
		$providers =& $GLOBALS['TL_DCA']['tl_workflow_state']['dca_config']['data_provider'];

		// switch the driver if we are in show view
		if(\Input::get('act') == 'show')
		{
			$providers['default'] = $providers['show'];
		}
		elseif(!\Input::get('wfid'))
		{
			$providers['default'] = $providers['list'];
			unset($GLOBALS['TL_DCA']['tl_workflow_state']['list']['operations']['show']);
		}
		else
		{
			$this->initializeParentView();
		}

		return $tableName;
	}


	/**
	 * Initialize
	 *
	 * @param \DcGeneral\DC_General $dc
	 */
	public function initialize($dc)
	{
		parent::initialize($dc);

		// dynamically switch fields to parent table
		if(\Input::get('act') == 'show')
		{
			/** @var \DcGeneral\Data\ModelInterface $origin */
			$origin = $this->entity->getMeta(SerializedDataDriver::MODEL_ORIGIN);

			if($origin->getProperty('ptable'))
			{
				BackendBindings::loadDataContainer($origin->getProperty('ptable'));
				$GLOBALS['TL_DCA']['tl_workflow_state']['fields'] = $GLOBALS['TL_DCA'][$origin->getProperty('ptable')]['fields'];

				if($this->entity->getProperty('_children'))
				{
					$GLOBALS['TL_DCA']['tl_workflow_state']['fields']['_children'] = array
					(
						'label' => &$GLOBALS['TL_LANG']['tl_workflow_state']['_children'],
					);
				}
			}
		}
	}


	/**
	 * Child callback
	 *
	 * @param $row
	 * @return string
	 */
	public function callbackChildRecord($row)
	{
		$formatter = Formatter::create('tl_workflow_state');

		return sprintf(
			'%s %s %s %s',
			\Date::parse($GLOBALS['TL_CONFIG']['datimFormat'], $row['createdAt']),
			$row['processName'],
			$formatter->getPropertyValue('stepName', $row['stepName']),
			$row['successful'] ? $GLOBALS['TL_LANG']['MSC']['yes'] : $GLOBALS['TL_LANG']['MSC']['no']
		);
	}


	/**
	 * Add ptable to the header
	 * @param $header
	 * @return array
	 */
	public function callbackHeader(array $header)
	{
		$formatter = Formatter::create($this->definition->getName());
		$header[$formatter->getPropertyLabel('ptable')] = $this->definition->get('config/ptable');

		return $header;
	}


	/**
	 * Generate the list button
	 *
	 * @param $row
	 * @param $href
	 * @param $label
	 * @param $title
	 * @param $icon
	 * @param $attributes
	 * @param $table
	 * @return string
	 */
	public function getListButton($row, $href, $label, $title, $icon, $attributes, $table)
	{
		return sprintf(
			'<a href="%s" title="%s" %s>%s</a>',
			\Controller::addToUrl('wfid=' . $row['workflowIdentifier']),
			$title,
			$attributes,
			\Image::getHtml($icon, $label)
		);
	}


	/**
	 * Initialize the dynamically used parent view
	 */
	protected function initializeParentView()
	{
		$parent = \Database::getInstance()
			->prepare('SELECT ptable, pid FROM tl_workflow_state WHERE workflowIdentifier=?')
			->limit(1)
			->execute(\Input::get('wfid'));

		if(!$parent->count())
		{
			return;
		}

		// dynamically set id
		\Input::setGet('id', $parent->pid);

		// DC_General requires that get table isset
		if(\Input::get('do') == 'workflow_history')
		{
			\Input::setGet('table', 'tl_workflow_state');
		}

		// prepare the dca
		unset($GLOBALS['TL_DCA']['tl_workflow_state']['list']['operations']['list']);

		BackendBindings::loadDataContainer($parent->ptable);
		$GLOBALS['TL_DCA'][$parent->ptable]['config']['notEditable'] = true;

		$GLOBALS['TL_DCA']['tl_workflow_state']['list']['sorting']['mode']      = 4;
		$GLOBALS['TL_DCA']['tl_workflow_state']['config']['ptable']             = $parent->ptable;
		$GLOBALS['TL_DCA']['tl_workflow_state']['dca_config']['childCondition'] = array
		(
			array
			(
				'from' => $parent->ptable,
				'to'   => 'tl_workflow_state',

				'setOn' => array(
					array(
						'to_field'   => 'pid',
						'from_field' => 'id',
					)
				),

				'filter' => array(
					array(
						'local'     => 'pid',
						'operation' => '=',
						'remote'    => 'id',
					),

					array(
						'local'        => 'ptable',
						'operation'    => '=',
						'remote_value' => $parent->ptable,
					)
				),
			),
		);
	}
}
