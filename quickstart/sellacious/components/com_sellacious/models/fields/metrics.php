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

class JFormFieldMetrics extends JFormField
{
	/**
	 * The field type.
	 *
	 * @var		string
	 */
	protected $type = 'metrics';

	/**
	 * Method to get a list of options for a list input.
	 *
	 * @return	array		An array of JHtml options.
	 */
	protected function getInput()
	{
		$helper		= SellaciousHelper::getInstance();
		$value  	= array('length'=>0, 'width'=>0, 'height'=>0, 'weight'=>0, 'volweight'=>0);

		if (is_array($this->value))
		{
			$value  = array_merge($value, array_intersect_key($this->value, $value));
		}

		$html		= '<div class="row">
							<div class="hidden-xs hidden-sm col-md-2 col-lg-2">
								<label>' . JText::_('COM_SELLACIOUS_PRODUCT_METRICS_LENGTH') . '<label>
							</div>
							<div class="col-sm-8 col-md-2 col-lg-2">
								<input name="'.$this->name.'[length]" id="'.$this->id.'_length" class="form-control" value="'.$value['length'].'" placeholder="' . JText::_('COM_SELLACIOUS_PRODUCT_METRICS_LENGTH') . '"/>
							</div>
							<div class="hidden-xs hidden-sm col-md-2 col-lg-2">
								<label>' . JText::_('COM_SELLACIOUS_PRODUCT_METRICS_WIDTH') . '<label>
							</div>
							<div class="col-sm-8 col-md-2 col-lg-2">
								<input name="'.$this->name.'[width]"  id="'.$this->id.'_width"  class="form-control" value="'.$value['width'].'" placeholder="' . JText::_('COM_SELLACIOUS_PRODUCT_METRICS_WIDTH') . '"/>
							</div>
							<div class="hidden-xs hidden-sm col-md-2 col-lg-2">
								<label>' . JText::_('COM_SELLACIOUS_PRODUCT_METRICS_HEIGHT') . '<label>
							</div>
							<div class="col-sm-8 col-md-2 col-lg-2">
								<input name="'.$this->name.'[height]" id="'.$this->id.'_height" class="form-control" value="'.$value['height'].'" placeholder="' . JText::_('COM_SELLACIOUS_PRODUCT_METRICS_HEIGHT') . '"/>
							</div>
							<div class="hidden-xs hidden-sm col-md-2 col-lg-2">
								<label>' . JText::_('COM_SELLACIOUS_PRODUCT_METRICS_WEIGHT') . '<label>
							</div>
							<div class="col-sm-8 col-md-2 col-lg-2">
								<input name="'.$this->name.'[weight]" id="'.$this->id.'_weight" class="form-control" value="'.$value['weight'].'" placeholder="' . JText::_('COM_SELLACIOUS_PRODUCT_METRICS_WEIGHT') . '"/>
							</div>
							<div class="hidden-xs hidden-sm col-md-2 col-lg-2">
								<label>' . JText::_('COM_SELLACIOUS_PRODUCT_METRICS_VOLWEIGHT') . '<label>
							</div>
							<div class="col-sm-8 col-md-2 col-lg-2">
								<input name="'.$this->name.'[volweight]" id="'.$this->id.'_volweight" class="form-control" value="'.$value['volweight'].'" placeholder="' . JText::_('COM_SELLACIOUS_PRODUCT_METRICS_VOLWEIGHT') . '"/>
							</div>
						</div>';

		return $html;
	}

}
