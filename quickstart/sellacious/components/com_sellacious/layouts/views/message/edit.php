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

/** @var  SellaciousViewMessage  $this */
JHtml::_('behavior.framework');
JHtml::_('jquery.framework');
JHtml::_('script', 'com_sellacious/view.message.js', array('version' => S_VERSION_CORE, 'relative' => true));
JHtml::_('stylesheet', 'com_sellacious/view.message.css', array('version' => S_VERSION_CORE, 'relative' => true));
?>
<script type="text/javascript">
	(function ($) {
		$(document).ready(function () {
			var o = new SellaciousViewMessage;
			$('#jform_recipients').attr('type', 'hidden');
			o.init('#jform_recipients',
				'<?php echo JSession::getFormToken() ?>',
				<?php echo json_encode($this->tags) ?>,
				<?php echo json_encode($this->selected) ?>
			);
		});
	})(jQuery);
</script>
<?php

$data = array(
	'name'  => $this->getName(),
	'state' => $this->state,
	'item'  => $this->item,
	'form'  => $this->form,
);

$options = array(
	'client' => 2,
	'debug'  => 0,
);

echo JLayoutHelper::render('com_sellacious.view.edit', $data, '', $options);
