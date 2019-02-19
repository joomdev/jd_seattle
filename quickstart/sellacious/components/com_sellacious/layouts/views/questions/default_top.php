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

/** @var  SellaciousViewQuestions $this */

?>
<ul id="sparks" class="pull-right transaction-summary" style="margin-top: 0">
	<li></li>
	<li class="sparks-info span-0">
		<h5> <?php echo JText::_('COM_SELLACIOUS_QUESTIONS_TOTAL_QUESTIONS_LABEL'); ?> <span class="txt-color-greenDark">
				<?php echo (int) $this->total_questions ?>
			</span>
		</h5>
	</li>
	<li class="sparks-info span-0">
		<h5 class="txt-color-red"> <?php echo JText::_('COM_SELLACIOUS_QUESTIONS_TOTAL_REPLIED_LABEL'); ?> <span class="txt-color-red">
				<?php echo (int) $this->total_unreplied ?>
			</span>
		</h5>
	</li>
</ul>

