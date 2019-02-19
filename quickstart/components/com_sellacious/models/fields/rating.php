<?php
/**
 * @version     1.6.1
 * @package     sellacious
 *
 * @copyright   Copyright (C) 2012-2018 Bhartiy Web Technologies. All rights reserved.
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 * @author      Izhar Aazmi <info@bhartiy.com> - http://www.bhartiy.com
 */
defined('_JEXEC') or die;

/**
 * Field to select a user ID from a modal list.
 *
 * @package     Joomla.Libraries
 * @subpackage  Form
 * @since       1.6
 */
class JFormFieldRating extends JFormField
{
	/**
	 * The form field type.
	 *
	 * @var    string
	 * @since  1.6
	 */
	public $type = 'Rating';

	/**
	 * Method to get the user field input markup.
	 *
	 * @return  string  The field input markup.
	 *
	 * @since   1.6
	 */
	protected function getInput()
	{
		$checks = array('', '', '', '', '', '');

		$checks[(int) $this->value] = 'checked';

		$html = <<<HTML
			<span class="rating">
				<input type="radio" class="rating-input" id="{$this->id}-5" name="{$this->name}" value="5" {$checks[5]}>
				<label for="{$this->id}-5" class="rating-star"></label>

				<input type="radio" class="rating-input" id="{$this->id}-4" name="{$this->name}" value="4" {$checks[4]}>
				<label for="{$this->id}-4" class="rating-star"></label>

				<input type="radio" class="rating-input" id="{$this->id}-3" name="{$this->name}" value="3" {$checks[3]}>
				<label for="{$this->id}-3" class="rating-star"></label>

				<input type="radio" class="rating-input" id="{$this->id}-2" name="{$this->name}" value="2" {$checks[2]}>
				<label for="{$this->id}-2" class="rating-star"></label>

				<input type="radio" class="rating-input" id="{$this->id}-1" name="{$this->name}" value="1" {$checks[1]}>
				<label for="{$this->id}-1" class="rating-star"></label>
			</span>
HTML;

		return $html;
	}
}
