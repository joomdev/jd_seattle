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
 * Form Field class for the a sortable list.
 *
 * @since   1.6.0
 */
class JFormFieldSortableList extends JFormFieldList
{
	/**
	 * The field type.
	 *
	 * @var  string
	 *
	 * @since   1.6.0
	 */
	protected $type = 'SortableList';

	/**
	 * Name of the layout being used to render the field
	 *
	 * @var    string
	 *
	 * @since   1.6.0
	 */
	protected $layout = 'com_sellacious.formfield.sortablelist';

	/**
	 * Method to get the user field input markup.
	 *
	 * @return  string  The field input markup.
	 *
	 * @since   1.6.0
	 */
	protected function getInput()
	{
		static $loaded;

		if (!$loaded)
		{
			JHtml::_('jquery.framework');

			$doc = JFactory::getDocument();
			$doc->addScriptDeclaration(<<<'JS'
	jQuery(document).ready(jq => jq('.jff-sortablelist-table')
		.find('.sortable-group').sortable({placeholder: 'placeholder'}).disableSelection()
	);
JS
);
			$loaded = true;
		}

		$data    = (object) array_merge(get_object_vars($this), array('options' => $this->getOptions()));
		$options = array('client' => 2, 'debug' => false);

		return JLayoutHelper::render($this->layout, $data, '', $options);
	}
}
