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

use Joomla\Utilities\ArrayHelper;

/** @var   SellaciousViewProduct $this */

$nullDt = JFactory::getDbo()->getNullDate();
$questions = $this->getQuestions();
if (!$questions)
{
	return;
}
?>
<div class="questionanswerbox">
	<h4><?php echo JText::_('COM_SELLACIOUS_TITLE_QA') ?></h4>
	<div class="table-questions">
		<?php
		foreach ($questions as $question)
		{
			?>
			<dl class="dl-horizontal dl-leftside">
				<dt>
					<span class="pqn-title pqn-block">
						<strong><?php echo JText::_('COM_SELLACIOUS_PRODUCT_QUESTION_QUESTION_LABEL') ?>: </strong>
					</span>
				</dt>
				<dd>
					<span class="pqn-question pqn-block"><?php echo $question->question ?></span>
					<span class="pqn-author"><?php echo $question->questioner_name ?></span>
					<span class="pqn-date"><?php echo JHtml::_('date', $question->created, 'M d, Y'); ?></span>
				</dd>
				<dt><span class="pqn-title pqn-block"><strong><?php echo JText::_('COM_SELLACIOUS_PRODUCT_QUESTION_ANSWER_LABEL') ?>: </strong></span></dt>
				<dd>
					<span class="pqn-answer pqn-block">
						<?php echo $question->answer ?>
					</span>
					<span class="answeredbyseller">
						<?php if(isset($question->seller)) :
							$repliedBy = $question->seller->store_name ?: $question->seller->title ?: $question->seller->name ?: $question->seller->username ?: '';
							?>
							<span class="pqn-author"><?php echo JText::_('COM_SELLACIOUS_PRODUCT_QUESTION_REPLIED_BY') . $repliedBy ?></span>
							<span class="pqn-date">
								<?php
								if ($question->replied != $nullDt && !empty($question->replied))
								{
									echo JHtml::_('date', $question->replied, 'M d, Y');
								}
								?>
							</span>

						<?php endif; ?>
					</span>
				</dd>
			</dl>
			<?php
		}
		?>
	</div>
</div>
