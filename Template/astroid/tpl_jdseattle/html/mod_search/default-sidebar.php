<?php
/**
 * @package     Joomla.Site
 * @subpackage  mod_search
 *
 * @copyright   Copyright (C) 2005 - 2018 Open Source Matters, Inc. All rights reserved.
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 */

defined('_JEXEC') or die;

// Including fallback code for the placeholder attribute in the search field.
JHtml::_('jquery.framework');
JHtml::_('script', 'system/html5fallback.js', array('version' => 'auto', 'relative' => true, 'conditional' => 'lt IE 9'));

if ($width)
{
	$moduleclass_sfx .= ' ' . 'mod_search' . $module->id;
	$css = 'div.mod_search' . $module->id . ' input[type="search"]{ width:auto; }';
	JFactory::getDocument()->addStyleDeclaration($css);
	$width = ' size="' . $width . '"';
}
else
{
	$width = '';
}
?>
<div class="search<?php echo $moduleclass_sfx; ?> input-group">
	<form action="<?php echo JRoute::_('index.php'); ?>" method="post" class="form-inline">
		<?php
		    $output = '<input type="text" class="form-control" name="searchword" id="mod-search-searchword' . $module->id . '">';

		    if (!$button) :
		    $output .= '<div class="input-group-append">
			<span class="input-group-text"><i class="fas fa-search"></i></span>
			</div>';
			endif;			

			if ($button) :
				if ($imagebutton) :
					$btn_output = ' <input type="image" alt="' . $button_text . '" class="button search-btn" src="' . $img . '" onclick="this.form.searchword.focus();"/>';
				else :
					$btn_output = ' <button class="button btn btn-primary" onclick="this.form.searchword.focus();">' . $button_text . '</button>';
				endif;

				switch ($button_pos) :
					case 'top' :
						$output = $btn_output . '<br />' . $output;
						break;

					case 'bottom' :
						$output .= '<br />' . $btn_output;
						break;

					case 'right' :
						$output .= $btn_output;
						break;

					case 'left' :
					default :
						$output = $btn_output . $output;
						break;
				endswitch;
			endif;

			echo $output;
		?>
		<input type="hidden" name="task" value="search" />
		<input type="hidden" name="option" value="com_search" />
		<input type="hidden" name="Itemid" value="<?php echo $mitemid; ?>" />
	</form>
</div>