<?php
/*
 * Copyright (C) joomla-monster.com
 * Website: http://www.joomla-monster.com
 * Support: info@joomla-monster.com
 *
 * JM Module is free software: you can redistribute it and/or modify
 * it under the terms of the GNU General Public License as published by
 * the Free Software Foundation, either version 3 of the License, or
 * (at your option) any later version.
 *
 * JM Module is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE. See the
 * GNU General Public License for more details.
 *
 * You should have received a copy of the GNU General Public License
 * along with JM Module. If not, see <http://www.gnu.org/licenses/>.
*/

defined('JPATH_PLATFORM') or die;

/**
 * Form Field class for the Joomla Platform.
 * Provides spacer markup to be used in form layouts.
 *
 * @package     Joomla.Platform
 * @subpackage  Form
 * @since       11.1
 */
class JFormFieldJMElements extends JFormField
{
	/**
	 * The form field type.
	 *
	 * @var    string
	 * @since  11.1
	 */
	protected $type = 'JMElements';

	protected $lang = array();

	protected static $js_initialised = array();

	/**
	 * Method to attach a JForm object to the field.
	 *
	 * @param   SimpleXMLElement  $element  The SimpleXMLElement object representing the `<field>` tag for the form field object.
	 * @param   mixed             $value    The form field value to validate.
	 * @param   string            $group    The field name group control value. This acts as as an array container for the field.
	 *                                      For example if the field has name="foo" and the group value is set to "bar" then the
	 *                                      full field name would end up being "bar[foo]".
	 *
	 * @return  boolean  True on success.
	 *
	 * @since   11.1
	 */
	public function setup(SimpleXMLElement $element, $value, $group = null)
	{
		if (!parent::setup($element, $value, $group)){
			return false;
		}

		$app = JFactory::getApplication();
		$doc = JFactory::getDocument();
		$fields = explode(',', $this->element['element_fields']);

		$jsFields = array();
		foreach($fields as $field) {
			 $jsFields[] = '[name^="'.str_replace($this->fieldname, $field, $this->name).'"]';
		}
		$jsFields = implode(',', $jsFields);

		//$app->enqueueMessage($jsFields);

		$lang = array();
		$lang['element_name'] = 'Item';
		$lang['elements_heading'] = 'Items';

		if (!empty($this->element['element_name'])) {
			$lang['element_name'] = (string)JText::_($this->element['element_name']);
		}
		if (!empty($this->element['label'])) {
			$lang['elements_heading'] = (string)JText::_($this->element['label']);
		}

		$element_field = '';
		if (!empty($this->element['element_field'])) {
			$element_field = $this->element['element_field'];
			$element_field = str_replace($this->fieldname, $element_field, $this->name);
		}

		$this->lang = $lang;

		$json_lang = json_encode($lang);

		$module_name = basename(realpath(dirname(__FILE__).'/../'));

		if (isset(self::$js_initialised[$this->id])) {
			return true;
		}

		if (empty(self::$js_initialised)) {
			JHtml::_('jquery.ui', array('core', 'sortable'));
			$doc->addScript(JUri::root(true).'/modules/' . $module_name . '/assets/jmelements.js');
		}

		$doc->addScriptDeclaration('
			jQuery(document).ready(function(){
				window.JmEl'.$this->id.' = new JMElements("'.$this->id.'", "'.addslashes($jsFields).'", '.($json_lang).', "'.addslashes($element_field).'");

				var form = document.adminForm;
				if(!form){
					return false;
				}

				var onsubmit = form.onsubmit;

				form.onsubmit = function(e){
					(form.task.value && form.task.value.indexOf(".cancel") != -1) ?
						(jQuery.isFunction(onsubmit) ? onsubmit() : false) : JmEl'.$this->id.'.clearFormSubmit(onsubmit);
				};

			});');

		self::$js_initialised[$this->id] = true;

		return true;
	}

		/**
		 * Method to get the field input markup for a spacer.
		 * The spacer does not have accept input.
		 *
		 * @return  string  The field input markup.
		 *
		 * @since   11.1
		 */
		protected function getInput()
		{

			$html = array();

			$html[] = '<div class="jm-element-items"><table id="'.$this->id.'_items" class="table-condensed"></table></div>';

			$html[] = '<textarea name="' . $this->name . '" id="' . $this->id . '" style="display: none;">' . htmlspecialchars($this->value, ENT_COMPAT, 'UTF-8') . '</textarea>';
			$html[] = '<button id="' . $this->id . '_btn_add" class="btn btn-primary">'.JText::_('MOD_JM_UI_ADD_NEW_BTN').'</button>';
			$html[] = '<button style="display: none;" id="' . $this->id . '_btn_cancel" class="btn">'.JText::_('MOD_JM_UI_CANCEL_BTN').'</button>';

			$html[] = '<button style="display: none;" id="' . $this->id . '_btn_save" class="btn btn-success" >'.JText::_('MOD_JM_UI_SAVE_BTN').'</button>';

			return implode($html);
		}

		/**
		 * Method to get the field label markup for a spacer.
		 * Use the label text or name from the XML element as the spacer or
		 * Use a hr="true" to automatically generate plain hr markup
		 *
		 * @return  string  The field label markup.
		 *
		 * @since   11.1
		 */
		protected function getLabel()
		{
			$html = array();

			$html[] = '<label>'.$this->lang['elements_heading'].'</label>';

			return implode($html);
		}

		/**
		 * Method to get the field title.
		 *
		 * @return  string  The field title.
		 *
		 * @since   11.1
		 */
		protected function getTitle()
		{
			return $this->getLabel();
		}
}
