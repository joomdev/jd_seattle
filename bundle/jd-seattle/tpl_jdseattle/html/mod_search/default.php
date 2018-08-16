<?php
/**
 * @package   Astroid Framework
 * @author    JoomDev https://www.joomdev.com
 * @copyright Copyright (C) 2009 - 2018 JoomDev.
 * @license http://www.gnu.org/licenses/gpl-2.0.html GNU/GPLv2 or Later
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
<div class="search<?php echo $moduleclass_sfx; ?> search-wrapper shadow-lg p-4 mb-5">
	<h4><?php echo $module->title ?></h4>
	<form action="<?php echo JRoute::_('index.php'); ?>" method="post">
	<div class="form-row align-items-center">
		<?php
				$output = '<div class="col pr-0">';
					$output .= '<input name="searchword" id="mod-search-searchword' . $module->id . '" maxlength="' . $maxlength . '"  class="form-control border-right-0 rounded-left" type="search"' . $width;
					$output .= ' placeholder="' . $text . '" />';
				$output .= '</div>';
				if ($button) :
					if($button_text == 'Search'){
						$button_text='';
					}else{
						$button_text;
					}
				

					if ($button) :
						if ($imagebutton) :
							$btn_output = ' <input type="image" alt="' . $button_text . '" class="button" src="' . $img . '" onclick="this.form.searchword.focus();"/>';
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

				$output .= '<div class="col-auto pl-0">';
					$output .= '<button type="submit" class="btn btn-dark border-left-0 rounded-right"  onclick="this.form.searchword.focus();">
									<i class="lni-search">'. $button_text  .'</i>
								</button>';
					$output .= '</div>';
				endif;	

			
			echo $output;
		?>
		<input type="hidden" name="task" value="search" />
		<input type="hidden" name="option" value="com_search" />
		<input type="hidden" name="Itemid" value="<?php echo $mitemid; ?>" />
		</div>
	</form>
</div>