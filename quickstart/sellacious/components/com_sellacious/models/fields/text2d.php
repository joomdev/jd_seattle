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

/**
 * Form Field class for Sellacious.
 *
 * @since   1.5.1
 */
class JFormFieldText2d extends JFormField
{
	/**
	 * The field type.
	 *
	 * @var   string
	 *
	 * @since  1.5.1
	 */
	protected $type = 'Text2d';

	/**
	 * Method to get the field input markup.
	 *
	 * @return   string  The field input markup.
	 *
	 * @since    1.6
	 */
	protected function getInput()
	{
		// May be we should also check for data structure of value. Skipping for now!
		$this->value = is_array($this->value) ? $this->value : array();

		JHtml::_('jquery.framework');
		JHtml::_('script', 'com_sellacious/field.text2d.js', array('version' => S_VERSION_CORE, 'relative' => true));
		JHtml::_('stylesheet', 'com_sellacious/field.text2d.css', array('version' => S_VERSION_CORE, 'relative' => true));

		$helper  = SellaciousHelper::getInstance();
		$options = array('client' => 2, 'debug' => 0);
		$data    = (object) (get_object_vars($this));
		$html    = JLayoutHelper::render('com_sellacious.formfield.text2d', $data, '', $options);

		$data->row_index = '##INDEX##';

		$tmpl = JLayoutHelper::render('com_sellacious.formfield.text2d.rowtemplate', $data, '', $options);
		$tmpl = json_encode(preg_replace('/[\t\r\n]+/', '', $tmpl));
		$rows = count($this->value);

		$doc  = JFactory::getDocument();
		$doc->addScriptDeclaration(<<<JS
			jQuery(document).ready(function () {
				var o = new JFormFieldText2d;
				o.setup({
					id : '{$this->id}',
					rowIndex : '{$rows}',
					rowTemplate : {
						html : {$tmpl},
						replacement : '##INDEX##'
					}
				});
			});
JS
);

		return $html;
	}

	/**
	 * Method to get the field label markup.
	 *
	 * @return  string  The field label markup.
	 *
	 * @since   11.1
	 */
	public function getLabel()
	{
		return '';
	}

	/**
	 * Method to get the lists for the dropdown lists.
	 *
	 * @return  array  The lists.
	 *
	 * @since   11.1
	 */
	protected function getLists()
	{
		$lists   = array();
		$helper  = SellaciousHelper::getInstance();

		// List of client categories
		$options = array();
		$filter  = array('list.select' => 'a.id, a.title, a.type', 'list.where' => 'a.level > 0', 'state' => 1, 'type' => 'client');
		$items   = $helper->category->loadObjectList($filter);

		foreach ($items as $item)
		{
			$level     = ($item->level > 1) ? ('|' . str_repeat('&mdash;', $item->level - 1) . ' ') : '';
			$options[] = JHtml::_('select.option', $item->id, $level . $item->title);
		}

		$lists['clients'] = $options;

		// List of variants
		$options = array();
		$filter  = array('list.select' => 'a.id, a.title', 'product_id' => $this->form->getValue('id'));
		$items   = $helper->variant->loadObjectList($filter);

		foreach ($items as $item)
		{
			$options[] = JHtml::_('select.option', $item->id, $item->title);
		}

		$lists['variants'] = $options;

		// Return collected lists
		return $lists;
	}

	/**
	 * Get an initialization script for an element id
	 *
	 * @param   string  $id  The html element/input id attribute
	 *
	 * @return  string  JavaScript that initialises the calendar on that input
	 *
	 * @since   1.5
	 */
	protected function getScript($id)
	{
		$day = JFactory::getLanguage()->getFirstDay();

		$js  = <<<JS
		jQuery(document).ready(function($) {
			Calendar.setup({
				inputField: "{$id}",
				ifFormat: "%Y-%m-%d",
				// button: "{$id}_img",
				align: "Tl",
				singleClick: true,
				firstDay: {$day}
			});
		});
JS;

		return $js;
	}
}
