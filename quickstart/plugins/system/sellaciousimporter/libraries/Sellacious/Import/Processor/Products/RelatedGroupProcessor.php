<?php
/**
 * @version     1.6.1
 * @package     sellacious
 *
 * @copyright   Copyright (C) 2012-2018 Bhartiy Web Technologies. All rights reserved.
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 * @author      Izhar Aazmi <info@bhartiy.com> - http://www.bhartiy.com
 */
namespace Sellacious\Import\Processor\Products;

defined('_JEXEC') or die;

use Sellacious\Import\Processor\AbstractProcessor;

class RelatedGroupProcessor extends AbstractProcessor
{
	/**
	 * The columns that will be the part of import CSV
	 *
	 * @return  string[]
	 *
	 * @see     getcolumns()
	 *
	 * @since   1.6.1
	 */
	protected function getCsvColumns()
	{
		return array('related_product_groups');
	}

	/**
	 * The columns that will NOT be the part of import CSV,
	 * but they are needed to be evaluated first by any other processors.
	 * Without these keys evaluated this processor cannot process.
	 *
	 * @return  string[]
	 *
	 * @see     getDependencies()
	 *
	 * @since   1.6.1
	 */
	protected function getRequiredColumns()
	{
		return array('x__product_id');
	}

	/**
	 * The columns that will NOT be the part of import CSV,
	 * but they will be evaluated by this processors and are available to be used by any other processor.
	 *
	 * @return  string[]
	 *
	 * @see     getDependables()
	 *
	 * @since   1.6.1
	 */
	protected function getGeneratedColumns()
	{
		return array();
	}

	/**
	 * Method to perform the actual import tasks for individual record.
	 * Any write actions can be performed at this stage relevant to the passed record.
	 * If this is called then all dependency must've been already fulfilled by some other processors.
	 *
	 * @param   \stdClass  $obj  The record obtained from CSV, was pre-processed in <var>preProcessRecord()</var>
	 *
	 * @return  void
	 *
	 * @since   1.6.1
	 */
	public function processRecord($obj)
	{
		if (!$obj->x__product_id || !$obj->related_product_groups)
		{
			return;
		}

		$groupNames = preg_split('#(?<!\\\);#', $obj->related_product_groups, -1, PREG_SPLIT_NO_EMPTY);
		$groupNames = array_unique(array_filter($groupNames, 'trim'));

		foreach ($groupNames as $groupName)
		{
			$table = \SellaciousTable::getInstance('RelatedProduct');
			$tbl2  = \SellaciousTable::getInstance('RelatedProduct');
			$xref  = new \stdClass;

			$xref->product_id  = $obj->x__product_id;
			$xref->group_title = $groupName;
			$xref->group_alias = null;

			$table->bind($xref);
			$table->check();

			$keys = array(
				'product_id'  => $table->get('product_id'),
				'group_alias' => $table->get('group_alias')
			);

			if (!$tbl2->load($keys))
			{
				$table->store();
			}
		}
	}
}
