<?php
/**
 * @version     1.6.1
 * @package     sellacious
 *
 * @copyright   Copyright (C) 2012-2018 Bhartiy Web Technologies. All rights reserved.
 * @license     SPL Sellacious Private License; see http://www.sellacious.com/spl.html
 * @author      Izhar Aazmi <info@bhartiy.com> - http://www.bhartiy.com
 */
// no direct access
defined('_JEXEC') or die;

use Joomla\Utilities\ArrayHelper;

/**
 * Sellacious helper.
 *
 * @since  3.0.0
 */
class SellaciousHelperProductQuery extends SellaciousHelperBase
{
	/**
	 * Prepare the query form data to include labels of fields and not just the field id
	 *
	 * @param   array  $query
	 *
	 * @return  stdClass[]
	 *
	 * @since   3.0.0
	 */
	public function prepare($query)
	{
		$fields = array();

		if (!empty($query))
		{
			$pks    = array_keys($query);
			$filter = array(
				'list.select' => 'a.id, a.title, c.title AS field_group',
				'id'          => $pks,
			);

			$fields = $this->helper->field->loadObjectList($filter);

			foreach ($fields as $field)
			{
				$field->value = ArrayHelper::getValue($query, $field->id);

				unset($field->level);
			}
		}

		return $fields;
	}
}
