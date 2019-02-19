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

JHtml::_('stylesheet', 'mod_usercurrency/style.css', null, true);
JHtml::_('script', 'mod_usercurrency/jquery.jqtransform.js', false, true);
JHtml::_('script', 'mod_usercurrency/default.js', false, true);

$helper  = SellaciousHelper::getInstance();
$filter  = array('state' => 1, 'list.order' => 'a.code_3', 'list.select' => 'a.code_3');
$options = $helper->currency->loadObjectList($filter);
$current = $helper->currency->current('code_3');
?>
<div class="mod_usercurrency_module">
	<label for="mod_usercurrency_list" class="mod_usercurrency_title"><?php echo $module->title; ?></label>
	<div class="mod_usercurrency_block" id="mod_usercurrency_block">
		<div class="mod_usercurrency">
			<select id="mod_usercurrency_list">
				<?php echo JHtml::_('select.options', $options, 'code_3', 'code_3', $current); ?>
			</select>
		</div>
	</div>
</div>
