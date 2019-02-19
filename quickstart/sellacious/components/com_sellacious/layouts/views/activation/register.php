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

/** @var  SellaciousViewActivation  $this */
JHtml::_('behavior.keepalive');
JHtml::_('jquery.framework');
JHtml::_('stylesheet', 'com_sellacious/view.activation.register.css', array('version' => S_VERSION_CORE, 'relative' => true));
?>
<div class="row">
	<div class="col-sm-12">
		<div class="well well-light padding-20" style="position: relative;">

			<?php if($this->item->get('license.active')): ?>
				<a href="<?php echo JRoute::_('index.php'); ?>" style="position: fixed; right: 15px; top: 15px; left: auto; border-radius: 15px;"
				   class="btn btn-primary pull-right"><i class="fa fa-home"></i> &nbsp;<?php
					echo JText::_('COM_SELLACIOUS_LICENSE_BACK_TO_DASHBOARD_LABEL', true) ?> </a>
			<?php endif; ?>

			<div class="center"><img src="templates/sellacious/images/sellacious-logo-large.png" alt="" width="180px"></div>

			<div class="activation-register padding-20">

				<!-- Enter license key -->
				<?php echo $this->loadTemplate('keyform'); ?>
				<!-- Enter license key -->

				<div class="or-separator text-center">
					<div class="pull-left or-marker">OR</div>Choose a Subscription Plan</div>

				<!-- Choose a plan -->
				<?php echo $this->loadTemplate('plans'); ?>
				<!-- Choose a plan -->

			</div>
		</div>
	</div>
</div>
