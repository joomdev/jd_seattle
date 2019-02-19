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

/** @var $this SellaciousViewDashboard */
JHtml::_('jquery.framework');

JHtml::_('script', JPATH_SELLACIOUS_DIR . '/templates/sellacious/js/plugin/sparkline/jquery.sparkline.min.js', array('version' => S_VERSION_CORE));

JHtml::_('script', 'com_sellacious/view.dashboard.js', array('version' => S_VERSION_CORE, 'relative' => true));
JHtml::_('script', 'com_sellacious/view.dashboard.banners.js', array('version' => S_VERSION_CORE, 'relative' => true));

JHtml::_('stylesheet', 'com_sellacious/view.dashboard.css', array('version' => S_VERSION_CORE, 'relative' => true));
JHtml::_('stylesheet', 'com_sellacious/view.dashboard.banners.css', array('version' => S_VERSION_CORE, 'relative' => true));

$this->orderStats = $this->helper->report->getOrderStats(30, 'now', true);
$this->balances   = $this->helper->report->getWalletStats();

echo $this->loadTemplate('top');

if ($this->helper->access->check('config.edit'))
{
	echo $this->loadTemplate('bottom');
}
elseif ($this->helper->seller->is())
{
	echo $this->loadTemplate('bottom_seller');
}

// Full sitekey will not be exposed to public
$sitekey = $this->helper->core->getLicense('sitekey');
$sitekey = explode(':', $sitekey);
$sitekey = array_pop($sitekey);
?>
<script>
jQuery(document).ready(function () {
	var o = new SellaciousBanner;
	o.init('<?php echo $this->escape($sitekey) ?>');
});
</script>
<div class="w100p">
	<div class="col-md-8 col-xs-12 col-lg-9 col-sm-12">
		<?php echo $this->loadTemplate('graphs'); ?>
	</div>
	<div class="col-md-4 col-xs-12 col-lg-3 col-sm-12">
		<?php echo $this->loadTemplate('chart_progress'); ?>
	</div>
</div>

<div class="addproduct">
	<a href="<?php echo JRoute::_('index.php?option=com_sellacious&task=product.add'); ?>" title="<?php echo JText::_('COM_SELLACIOUS_DASHBOARD_QUICK_LINKS_CREATE_NEW_PRODUCT'); ?>">
		<i class="fa fa-plus"></i>
	</a>
</div>
<?php
