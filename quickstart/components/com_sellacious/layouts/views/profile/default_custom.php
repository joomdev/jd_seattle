<?php
/**
 * @version     1.6.1
 * @package     sellacious
 *
 * @copyright   Copyright (C) 2012-2018 Bhartiy Web Technologies. All rights reserved.
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 * @author      Izhar Aazmi <info@bhartiy.com> - http://www.bhartiy.com
 */
defined('_JEXEC') or die;

use Joomla\Registry\Registry;
use Joomla\Utilities\ArrayHelper;

/** @var  Registry  $registry */
$registry = $this->registry;

$msgBlank = JText::_('COM_SELLACIOUS_PROFILE_VALUE_NOT_FOUND');

$values = (array) $registry->get('custom_profile');
$data   = $this->helper->field->buildData($values);
$groups = ArrayHelper::getColumn($data, 'group', 'group_id');
$sets   = array();

foreach ($data as $item)
{
	$sets[(int) $item->group_id][] = $item;
}

foreach($sets as $gid => $group): ?>
	<fieldset class="w100p users_profile_custom_info form-horizontal">
		<legend>
			<?php echo ArrayHelper::getValue($groups, $gid); ?>
		</legend>
		<?php foreach ($group as $item): ?>
			<div class="control-group">
				<div class="control-label">
					<label><?php echo $item->label; ?></label>
				</div>
				<div class="controls">
					<?php echo $item->html ?: $msgBlank; ?>
				</div>
			</div>
		<?php endforeach; ?>
	</fieldset>
<?php endforeach; ?>
