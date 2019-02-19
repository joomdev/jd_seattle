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

/** @var SellaciousViewOrders $this */

$app = JFactory::getApplication();

$listOrder = $this->escape($this->state->get('list.ordering'));
$listDirn  = $this->escape($this->state->get('list.direction'));

// Load the behaviors.
JHtml::_('behavior.framework');
JHtml::_('jquery.framework');
JHtml::_('bootstrap.tooltip');
JHtml::_('behavior.formvalidator');

JHtml::_('script', 'com_sellacious/fe.view.downloads.js', true, true);

JHtml::_('stylesheet', 'com_sellacious/fe.component.css', null, true);
JHtml::_('stylesheet', 'com_sellacious/fe.view.downloads.css', null, true);
JHtml::_('stylesheet', 'com_sellacious/font-awesome.min.css', null, true);
?>
<div class="toggle-frame">
	<?php
	foreach ($this->items as $delivery)
	{
		echo $this->loadTemplate('tile', $delivery);
	}
	?>
	<input type="hidden" name="<?php echo JSession::getFormToken() ?>" id="formToken" value="1"/>
</div>

<form action="<?php echo JUri::getInstance()->toString(array('path', 'query', 'fragment')) ?>"
	method="post" name="adminForm" id="adminForm">
	<table class="w100p">
		<tr>
			<td class="text-center">
				<div class="pagination"><?php echo $this->pagination->getPagesLinks(); ?></div>
			</td>
		</tr>
		<tr>
			<td class="text-center">
				<?php echo $this->pagination->getResultsCounter(); ?>
			</td>
		</tr>
	</table>

	<input type="hidden" name="task" value=""/>
	<input type="hidden" name="filter_order" value="<?php echo $listOrder; ?>"/>
	<input type="hidden" name="filter_order_Dir" value="<?php echo $listDirn; ?>"/>

	<?php
	if ($tmpl = $app->input->get('tmpl'))
	{
		?><input type="hidden" name="tmpl" value="<?php echo $tmpl ?>"/><?php
	}

	if ($layout = $app->input->get('layout'))
	{
		?><input type="hidden" name="layout" value="<?php echo $layout ?>"/><?php
	}

	echo JHtml::_('form.token');
	?>
</form>
<div class="clearfix"></div>

