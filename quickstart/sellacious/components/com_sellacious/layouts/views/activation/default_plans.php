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

$jarvisSite = $this->helper->core->getJarvisSite();

JHtml::_('script', 'com_sellacious/view.activation.plans.js', array('version' => S_VERSION_CORE, 'relative' => true));

// This css/script should frequently refreshed
JHtml::_('script', $jarvisSite . '/media/com_jarvis/installation/js/activation.plans.js', array('version' => date('YmdA')));
JHtml::_('script', $jarvisSite . '/media/com_jarvis/installation/css/activation.plans.css', array('version' => date('YmdA')));

$link = JRoute::_('index.php?option=com_sellacious&view=activation&layout=free');

$this->document->addScriptOptions('sellacious.site_id', $this->helper->core->getLicense('site_id'));
?>
<div class="row margin-top-10" id="activation-plans"
	 data-siteurl="<?php echo $this->item->get('siteurl'); ?>"
	 data-free-activation="<?php echo $link ?>"
	 data-baseurl="<?php echo JUri::base(); ?>index.php">
	<div style="width: auto; text-align: center;">
		<i class="fa fa-4x fa-spin fa-spinner"></i>
	</div>
</div>
