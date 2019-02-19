<?php
/**
 * @version     1.6.1
 * @package     sellacious
 *
 * @copyright   Copyright (C) 2012-2018 Bhartiy Web Technologies. All rights reserved.
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 * @author      Izhar Aazmi <info@bhartiy.com> - http://www.bhartiy.com
 */
// no direct access
defined('_JEXEC') or die;

/**
 * Field to select a user ID from a modal list.
 *
 * @package     Joomla.Libraries
 * @subpackage  Form
 * @since       1.6
 */
class JFormFieldEwalletBalance extends JFormField
{
	/**
	 * The form field type.
	 *
	 * @var    string
	 * @since  1.6
	 */
	public $type = 'EwalletBalance';

	/**
	 * Method to get the user field input markup.
	 *
	 * @return  string  The field input markup.
	 *
	 * @since   1.6
	 */
	protected function getInput()
	{
		if (is_numeric($this->value))
		{
			$user = JFactory::getUser($this->value);
		}
		else // if (strtolower($this->value) == 'current')
		{
			$user        = JFactory::getUser();
			$this->value = $user->id;
		}

		JHtml::_('behavior.framework');
		JHtml::_('jquery.framework');

		$jsFile = JHtml::_('script', 'com_sellacious/field.ewallet-balance.js', false, true, true);
		$token  = JSession::getFormToken();

		$html = <<<HTML
		<style>
		#{$this->id}_wallet-info {
			font-size: 18px;
		}
		#{$this->id}_wallet-info .wallet-amounts td {
			text-align: right;
			color: #dd0000;
			padding: 2px;
		}
		</style>
		<script>
		jQuery(function($) {
		    if ($('script[src="{$jsFile}"]').length == 0) {
		        $.getScript('{$jsFile}', function() {
		            jQuery(document).ready(function() {
					    if (typeof JFormFieldEwalletBalance != 'undefined') {
							var o = new JFormFieldEwalletBalance;
							o.setup({id: '{$this->id}', token: '{$token}', user_id: '{$user->id}'});
						}
					})
		        });
		    }
		});
		</script>
		<div id="{$this->id}_wallet-info" class="pull-left">
			<div class="text-right">
				<button type="button" id="{$this->id}_reload" style="margin: 5px"
					class="btn btn-xs btn-mini btn-primary"><i class="fa fa-refresh"></i></button>
				<div class="wallet-amounts pull-right"><!-- content to be injected via ajax --></div>
			</div>
		</div>
HTML;

		return $html;
	}
}
