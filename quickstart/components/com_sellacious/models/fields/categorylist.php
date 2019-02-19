<?php
/**
 * @version     1.6.1
 * @package     sellacious
 *
 * @copyright   Copyright (C) 2012-2018 Bhartiy Web Technologies. All rights reserved.
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 * @author      Izhar Aazmi <info@bhartiy.com> - http://www.bhartiy.com
 */
// no direct access.
defined('_JEXEC') or die;

JFormHelper::loadFieldClass('List');

/**
 * Form Field class for the sellacious category list.
 *
 * @since   1.6
 */
class JFormFieldCategoryList extends JFormFieldList
{
	/**
	 * The field type.
	 *
	 * @var  string
	 */
	protected $type = 'CategoryList';

	/**
	 * Method to get the field options.
	 *
	 * @return  array  The field option objects
	 *
	 * @throws  Exception
	 *
	 * @since   1.6
	 */
	protected function getOptions()
	{
		// This may be called from outer context so load helpers explicitly.
		jimport('sellacious.loader');

		if (!class_exists('SellaciousHelper'))
		{
			$app = JFactory::getApplication();
			$app->enqueueMessage('COM_SELLACIOUS_LIBRARY_NOT_FOUND', 'error');

			return parent::getOptions();
		}

		$show    = (string) $this->element['show_all'] == 'true';
		$trans   = (string) $this->element['translate'] == 'true';   // Attribute to whether translate the categories or not
		$items   = $this->getItems();
		$options = array();
		$opts    = array();

		$language = JFactory::getLanguage()->getTag();
		$helper   = SellaciousHelper::getInstance();

		if ($trans)
		{
			$language = $this->form->getValue('language', null, $language);
		}

		foreach ($items as $item)
		{
			// Translate
			if ($trans)
			{
				$helper->translation->translateRecord($item, 'sellacious_categories', $language);
			}

			// We enable only leaf nodes for selection
			$level   = ($item->level > 1) ? (str_repeat('|&mdash; ', $item->level - 1)) : '';
			$disable = $show ? false : ($item->rgt - $item->lft) > 1;

			$opts[$item->id] = implode(' / ', $item->tree) ?: $item->title;

			$options[] = JHtml::_('select.option', $item->id, $level . $item->title, 'value', 'text', $disable);
		}

		$this->addScript($opts);

		return array_merge(parent::getOptions(), $options);
	}

	/**
	 * Method to get the field input markup for a generic list
	 * Use the multiple attribute to enable multiselect
	 *
	 * @return  string  The field input markup
	 *
	 * @since   1.6.0
	 */
	protected function getInput()
	{
		$items = $this->getItems();

		if ($items)
		{
			return parent::getInput();
		}

		return '<div class="bordered padding-7 bg-color-blueLight txt-color-white ' . $this->class . '">' . JText::_('COM_SELLACIOUS_CATEGORY_NOT_FOUND_CREATE_MESSAGE') . '</div>';
	}

	/**
	 * Get the list items to be added as options
	 *
	 * @return  stdClass[]
	 *
	 * @since   1.6.0
	 */
	protected function getItems()
	{
		static $cache = array();

		$type = (string) $this->element['group'];

		if (!isset($cache[$type]))
		{
			$cache[$type] = null;

			if (class_exists('SellaciousHelper'))
			{
				try
				{
					$helper = SellaciousHelper::getInstance();
					$types  = explode(';', $type);
					$filter = array(
						'list.select' => 'a.id, a.title, a.type, a.lft, a.rgt',
						'list.where'  => array('a.level > 0'),
						'type'        => $types,
						'state'       => 1
					);
					$items  = $helper->category->loadObjectList($filter);

					foreach ($items as $item)
					{
						$item->tree = $helper->category->getTreeLevels($item->id, true, 'b.title');
					}

					$cache[$type] = $items;
				}
				catch (Exception $e)
				{
				}
			}
		}

		return $cache[$type];
	}

	protected function addScript($opts)
	{
		$doc    = JFactory::getDocument();
		$opts   = json_encode($opts);
		$script = <<<JS
			jQuery(function(q) {
				var opts = {$opts};
			    q(window).load(function() {
			    	var l = q('#{$this->id}');
			        var m = l.prop('multiple');
			        var d = l.select2('data');
			        q.each(m ? d : [d], function(i, v) {
			            v.text = typeof opts[v.id] === 'undefined' ? v.text : opts[v.id];
			        });
			        l.select2('data', d);
			    });
			});
JS;

		$doc->addScriptDeclaration($script);
	}
}
