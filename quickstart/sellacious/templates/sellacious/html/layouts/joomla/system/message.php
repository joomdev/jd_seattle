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

use Joomla\Utilities\ArrayHelper;

/** @var  array  $displayData */
$msgList = $displayData['msgList'];

$alert = array('message' => 'success', 'warning' => 'warning', 'error' => 'danger', 'notice' => 'info', 'premium' => 'premium');
$icons = array('message' => 'check', 'warning' => 'warning', 'error' => 'times', 'notice' => 'info', 'premium' => 'star');
$types = array('message' => 'Success', 'warning' => 'Warning', 'error' => 'Error', 'notice' => 'Notice', 'premium' => 'Premium Feature');
?>
<!-- NEW WIDGET START -->
<article class="col-sm-12 no-padding">
	<?php if (is_array($msgList) && !empty($msgList)) : ?>
		<?php foreach ($msgList as $type => $messages) : ?>
			<?php
			$c = ArrayHelper::getValue($alert, $type, 'info');
			$i = ArrayHelper::getValue($icons, $type, 'gear');
			$t = ArrayHelper::getValue($types, $type, 'info');
			?>
			<div class="alert alert-<?php echo $c; ?> fade in">
				<button class="close" data-dismiss="alert">×</button>
				<?php if (!empty($messages)) : ?>
						<?php foreach ($messages as $message) : ?>
							<div>
								<i class="fa-fw fa fa-<?php echo $i; ?>"></i>&nbsp;
								<strong><?php echo JText::_($t); ?></strong>&nbsp;&nbsp;&nbsp;&nbsp;<?php echo $message; ?>
							</div>
						<?php endforeach; ?>
				<?php endif; ?>
			</div>
		<?php endforeach; ?>
	<?php endif; ?>
</article>
<!-- WIDGET END -->
