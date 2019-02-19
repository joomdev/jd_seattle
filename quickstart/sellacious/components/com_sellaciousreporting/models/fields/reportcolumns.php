<?php
/**
 * @version     1.6.1
 * @package     com_sellaciousreporting
 *
 * @copyright   Copyright (C) 2012-2018 Bhartiy Web Technologies. All rights reserved.
 * @license     SPL Sellacious Private License; see http://www.sellacious.com/spl.html
 * @author      Aditya Chakraborty <info@bhartiy.com> - http://www.bhartiy.com
 */
// no direct access.
defined('_JEXEC') or die;

use Joomla\Utilities\ArrayHelper;
use Sellacious\Report\ReportHelper;

JFormHelper::loadFieldClass('list');

/**
 * Form Field class for the reports columns.
 *
 * @since  1.6.0
 */
class JFormFieldReportcolumns extends JFormFieldList
{
	/**
	 * The field type.
	 *
	 * @var   string
	 *
	 * @since  1.6.0
	 */
	protected $type = 'reportcolumns';
	/**
	 * Method to get the field input markup.
	 *
	 * @return   string  The field input markup.
	 * @since    1.6.0
	 */
	protected function getInput()
	{
		JHtml::_('jquery.framework');
		JHtml::_('script', 'com_sellaciousreporting/jquery-ui-sortable.min.js', array('version' => S_VERSION_CORE, 'relative' => true));

		$js = <<<JS
	jQuery(document).ready(function(j) {
	    var showCols = j('.table-column-map').find('ul.show-columns');
		var buildList = function() {
		    var selected = showCols.find('input[data-name]').map(function() {
		        return j(this).data('name');
		    }).get();
		    j('#{$this->id}').val(JSON.stringify(selected));
		};
	    j("#{$this->id}_visible,#{$this->id}_hidden").sortable({
		    connectWith: ".connectedSortable",
		    update: buildList
	    }).disableSelection();
	    buildList();

	    var outerHeight = 60;
	    j('.sortable-item').each(function(){
	    	outerHeight += j(this).outerHeight();
	    });

	    j('.connectedSortable').height(outerHeight);
	})
JS;

		JFactory::getDocument()->addScriptDeclaration($js);

		try
		{
			$handlerName   = (string) $this->element['handler'] ?: null;
			$reportHandler = $handlerName ? ReportHelper::getHandler($handlerName) : null;
		}
		catch (Exception $e)
		{
			$reportHandler = null;
		}

		$this->value = is_array($this->value) ? $this->value : (json_decode($this->value, true) ?: array());

		$data    = array_merge(get_object_vars($this), array('handler' => $reportHandler));
		$options = array('client' => 2, 'debug' => false);
		$input   = JLayoutHelper::render('com_sellaciousreporting.forms.fields.reportcolumns', $data, '', $options);

		return $input;
	}
}
