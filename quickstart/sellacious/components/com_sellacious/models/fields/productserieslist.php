<?php
/**
 * @version     1.6.1
 * @package     sellacious
 *
 * @copyright   Copyright (C) 2012-2018 Bhartiy Web Technologies. All rights reserved.
 * @license     SPL Sellacious Private License; see http://www.sellacious.com/spl.html
 * @author      Izhar Aazmi <info@bhartiy.com> - http://www.bhartiy.com
 */
// no direct access.
defined('_JEXEC') or die;

JFormHelper::loadFieldClass('List');

/**
 * Form Field class for the Joomla Framework.
 *
 * @package        Joomla.Administrator
 * @subpackage     com_sellacious
 * @since          1.6
 */
class JFormFieldProductSeriesList extends JFormFieldList
{
	/**
	 * The field type.
	 *
	 * @var        string
	 */
	protected $type = 'ProductSeriesList';

	/**
	 * Method to get the field options.
	 *
	 * @return    array    The field option objects.
	 * @since    1.6
	 */
	protected function getOptions()
	{
		$mfr_f  = (string) $this->element['mfr_field'];
		$cats_f = (string) $this->element['cat_field'];
		$doAjax = (string) $this->element['ajax'] == 'true';

		$mfr    = $mfr_f ? $this->form->getValue($mfr_f) : null;
		$cats   = $cats_f ? $this->form->getValue($cats_f) : null;

		$series = $this->getSeriesFiltered($mfr, $cats, true);

		$options = array();

		foreach ($series as $item)
		{
			$options[] = JHtml::_('select.option', $item->id, sprintf('%s (%s)', $item->series_name, $item->series_code));
		}

		if ($doAjax)
		{
			$prefix = $this->formControl ? $this->formControl . '_' : '';
			$prefix .= $this->group ? $this->group . '_' : '';

			$mfr_ff  = $prefix . $mfr_f;
			$cats_ff = $prefix . $cats_f;

			$doc = JFactory::getDocument();
			$doc->addScriptDeclaration("
					(function ($) {
						$(document).ready(function () {
							var o = new JFormFieldProductSeriesList;
							o.setup({
								id : '{$this->id}',
								mfrfield : '{$mfr_ff}',
								catfield : '{$cats_ff}',
							});
						});
					})(jQuery);
				");

			JHtml::_('script', 'com_sellacious/field.productserieslist.js', array('version' => S_VERSION_CORE, 'relative' => true));
		}

		return array_merge(parent::getOptions(), $options);
	}

	/**
	 * Get a list of product series filtered by manufacturer and category and state
	 *
	 * @param  int        $manufacturer  manufacturer for which the series are queried
	 * @param  int|int[]  $categories    Product Categories
	 * @param  bool       $only_enabled  Whether queried only published series items
	 *
	 * @return stdClass[]
	 * @throws Exception
	 */
	protected function getSeriesFiltered($manufacturer, $categories, $only_enabled = false)
	{
		$db    = JFactory::getDbo();
		$query = $db->getQuery(true);
		$table = SellaciousTable::getInstance('ProductSeries');

		$query->select('a.*')->from($db->qn($table->getTableName(), 'a'));

		if (!empty($manufacturer))
		{
			$query->where('a.manufacturer_id = ' . $db->q($manufacturer));
		}

		if (!empty($categories))
		{
			$cats = ArrayHelper::toInteger((array) $categories);

			if (count($cats))
			{
				$query->where('a.category_id IN (' . implode(', ', $db->q($cats)) . ')');
			}
			else
			{
				$query->where('a.category_id = 0');
			}
		}

		if ($only_enabled)
		{
			$query->where('a.state = 1');
		}

		$series = $db->setQuery($query)->loadObjectList();

		return is_array($series) ? $series : array();
	}
}
