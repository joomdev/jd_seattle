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
JHtml::_('bootstrap.tooltip');
JHtml::_('stylesheet', 'com_sellacious/view.activation.info.css', array('version' => S_VERSION_CORE, 'relative' => true));
?>
<div class="row">

	<div class="col-sm-12">

			<div class="center"><img src="templates/sellacious/images/sellacious-logo-large.png" alt="" width="180px"></div>

			<div class="padding-20">

				<!-- Enter license key -->
				<?php echo $this->loadTemplate('licenseform'); ?>
				<!-- Enter license key -->

				<div class="clearfix"></div>

			</div>

	</div>

</div>
