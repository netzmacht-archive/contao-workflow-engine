<?php
/**
 * Created by PhpStorm.
 * User: david
 * Date: 19.11.13
 * Time: 22:21
 */

namespace Workflow\Contao\Data;

use DcGeneral\Data\DefaultDriver;

/**
 * DataProvider which allows to group entries of a table
 *
 * Actually this should be done by applying filters but at the moment the Default View/Controller of the DC_General
 * ignores ALL filters in the list view
 *
 * Required configuration:
 *  - source    -> table name
 *  - group_by  -> array of fields used for grouping
 *                 example: array('pid', 'ptable')
 *  - condition -> array with keys select and filter,
 *                 example: array('select'  => 'MAX(tstamp) maxtime', 'filter'  => 'tstamp=maxtime'),
 *
 * Optional configuration:
 *  - count     -> optional count the grouped rows
 *                 example: count(id) count
 *
 * @package Workflow\Contao\Data
 */
class GroupListingDriver extends DefaultDriver
{

	/**
	 * @var array
	 */
	protected $groupBy;

	/**
	 * @var array
	 */
	protected $condition;

	/**
	 * @var bool|string
	 */
	protected $count;


	/**
	 * @param array $arrConfig
	 */
	public function setBaseConfig(array $arrConfig)
	{
		parent::setBaseConfig($arrConfig);

		$this->groupBy    = $arrConfig['group_by'];
		$this->condition  = $arrConfig['condition'];
		$this->count      = isset($arrConfig['count']) ? $arrConfig['count'] : false;
	}


	/**
	 * @param \DcGeneral\Data\ConfigInterface $objConfig
	 * @param array $arrParams
	 * @return string
	 */
	protected function buildWhereQuery($objConfig, array &$arrParams = null)
	{
		$query  = sprintf(
			' a JOIN (SELECT %s, %s%s FROM %s GROUP BY %s) b ON %s AND a.%s ',
			implode(', ', $this->groupBy),
			$this->condition['select'],
			$this->count ? sprintf(', %s ', $this->count) : '',
			$this->strSource,
			implode(', ', $this->groupBy),
			$this->buildOnQuery($this->groupBy),
			$this->condition['filter']
		);

		$query .= parent::buildWhereQuery($objConfig, $arrParams);

		return $query;
	}


	/**
	 * @param array $fields
	 * @return string
	 */
	protected function buildOnQuery(array $fields)
	{
		$query = '';

		foreach($fields as $field)
		{
			if($query)
			{
				$query .= ' AND ';
			}

			$query .= sprintf('a.%s=b.%s ', $field, $field);
		}

		return $query;
	}

} 