<?php
/**
 * @version     1.6.1
 * @package     sellacious
 *
 * @copyright   Copyright (C) 2012-2018 Bhartiy Web Technologies. All rights reserved.
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 * @author      Izhar Aazmi <info@bhartiy.com> - http://www.bhartiy.com
 */
// no direct access.
use Joomla\Utilities\ArrayHelper;

defined('_JEXEC') or die;


/** @var  object  $displayData */
$field      = $displayData;
$extensions = $field->extensions;
?>
<script>
	jQuery(document).ready(function ($) {
		$('#<?php echo $field->id ?>').find('.jff-extension-group-input').select2({
			tags: [],
			tokenizer: function(input, selection, callback) {
				if (input.indexOf(',') < 0 && input.indexOf(' ') < 0)
					return;

				var parts = input.split(/,/);
				for (var i = 0; i < parts.length; i++) {
					var part = parts[i];
					part = part.trim();

					callback({id:part,text:part});
				}
			}
		});
	});
</script>
<table class="table bg-color-white table-bordered" id="<?php echo $field->id ?>">
<?php
foreach ($extensions as $extension)
{
	$v = ArrayHelper::getValue($field->value, $extension);
	?>
	<tr>
		<td style="width: 30%;"><?php echo $extension ?></td>
		<td><input type="hidden" name="<?php echo $field->name ?>[<?php echo $extension ?>]"
				   id="<?php echo $field->id ?>_<?php echo $extension ?>" class="jff-extension-group-input w100p" value="<?php  echo $v ?>"></td>
	</tr>
	<?php
}
?>
</table>
