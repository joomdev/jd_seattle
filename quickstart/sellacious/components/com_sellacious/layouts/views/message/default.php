<?php
/**
 * @version     1.6.1
 * @package     sellacious
 *
 * @copyright   Copyright (C) 2012-2018 Bhartiy Web Technologies. All rights reserved.
 * @license     SPL Sellacious Private License; see http://www.sellacious.com/spl.html
 * @author      Izhar Aazmi <info@bhartiy.com> - http://www.bhartiy.com
 */
// no direct access
defined('_JEXEC') or die;

/** @var  SellaciousViewMessage $this */
JHtml::_('behavior.framework');
JHtml::_('jquery.framework');
JHtml::_('behavior.formvalidator');

JHtml::_('script', 'com_sellacious/view.message.js', array('version' => S_VERSION_CORE, 'relative' => true));
JHtml::_('stylesheet', 'com_sellacious/view.message.css', array('version' => S_VERSION_CORE, 'relative' => true));
?>
<script>
	Joomla.submitbutton = function (task) {
		var form = document.getElementById('adminForm');
		if (task === 'message.cancel' || document.formvalidator.isValid(form)) {
			Joomla.submitform(task, form);
		} else {
			alert(Joomla.JText._('COM_SELLACIOUS_VALIDATION_FORM_FAILED'));
		}
	};
</script>
<form action="<?php echo JUri::getInstance()->toString(); ?>" method="post"
	name="adminForm" id="adminForm" class="form-validate form-horizontal">
	<?php
	$thread = $this->item->get('thread');
	$today  = JHtml::_('date', 'now', 'Y-m-d');

	if (is_array($thread) && count($thread)):
		$m = $thread[0];
		?>
		<div class="thread-subject"><i class="fa fa-envelope"></i> <?php echo $m->title ?>
			<label class="label label-success"><?php echo $m->context ? $m->context : 'message' ?></label></div>

		<div class="clearfix"></div>

		<div class="panel-group smart-accordion-default" id="message-thread">
			<?php
			foreach ($thread as $message):
				$b = $message->id == $this->item->get('id');
				?>
				<div class="panel panel-default">
					<div class="panel-heading">
						<h4 class="panel-title">
							<a data-toggle="collapse" data-parent="#message-thread"
								href="#collapse-<?php echo $message->id ?>" class="<?php echo $b ? '' : 'collapsed' ?>">
								<i class="fa fa-lg fa-angle-down pull-left"></i>
								<i class="fa fa-lg fa-angle-up pull-left"></i>
								<strong>From: </strong><?php echo $message->sender ? $this->helper->user->getFieldValue($message->sender, 'name') : JText::_('COM_SELLACIOUS_USER_IDENTITY_GUEST_LABEL') ?>
								<!--<span class="message-summary"><?php /*echo strip_tags($message->body) */
								?></span>-->
								<?php $tip = JHtml::_('date', $message->date_sent, 'M dS, Y h:i A'); ?>
								<?php $date = $this->helper->core->shortDateTime($message->date_sent, 'now', null); ?>
								<span class="message-date hasTooltip" data-placement="left" title="<?php echo $tip ?>">
								<i class="fa fa-calendar"></i> &nbsp; <?php echo $date; ?></span>
							</a>
						</h4>
					</div>

					<div id="collapse-<?php echo $message->id ?>"
						class="panel-collapse collapse <?php echo $b ? 'in' : '' ?>" <?php echo $b ? '' : 'style="height: 0;"'; ?>>
						<div class="panel-body">
							<?php echo $message->body; ?>
						</div>
					</div>
				</div>
				<?php
			endforeach;
			?>
		</div>
		<?php

		// There may not a form available where reply is not possible such as broadcast.
		if ($this->form instanceof JForm)
		{
			echo $this->loadTemplate('reply');
		}
	endif;
	?>
	<input type="hidden" name="task" value="" />
	<?php echo JHtml::_('form.token'); ?>

</form>
<div class="clearfix"></div>

