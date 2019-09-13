<?php
/**
 * @package	AcyMailing for Joomla
 * @version	6.2.2
 * @author	acyba.com
 * @copyright	(C) 2009-2019 ACYBA S.A.R.L. All rights reserved.
 * @license	GNU/GPLv3 http://www.gnu.org/licenses/gpl-3.0.html
 */

defined('_JEXEC') or die('Restricted access');
?><div id="acym_content" class="popup_size">
	<div id="acym__dynamics__popup">
		<div class="acym__dynamics__popup__menu grid-x">
			<div id="acym__dynamics__popup__menu__insert__tag" class="cell grid-x">
				<div class="medium-auto hide-for-small-only"></div>
				<input title="<?php echo acym_translation('ACYM_DYNAMIC_TEXT'); ?>" type="text" class="cell medium-5 small-12 margin-right-1" id="dtextcode" name="dtextcode" value="" onclick="this.select();">
				<div class="medium-2 small-12">
					<button class="button expanded smaller-button" id="insertButton"><?php echo acym_translation('ACYM_INSERT'); ?></button>
				</div>
				<div class="medium-auto hide-for-small-only"></div>
			</div>
			<div class="cell grid-x acym__content acym__content__tab">
                <?php
                foreach ($data['plugins'] as $id => $onePlugin) {
                    if (empty($onePlugin)) {
                        continue;
                    }
                    if ($data['automation']) $onePlugin->plugin = $onePlugin->plugin.'&automation=true';
                    $data['tab']->startTab($onePlugin->name, true, 'data-dynamics="'.$onePlugin->plugin.'"');
                    $data['tab']->endTab();
                }
                $data['tab']->display('popup__dynamics');
                ?>
			</div>
            <?php if (!empty($help) && acym_isAdmin()) { ?>
				<td valign="top">
					<div style="float:right;padding-right:5px;" class="toolbar">
                        <?php
                        $toolbar = acym_get('helper.toolbar');
                        $toolbar->help($help);
                        ?>
						<button onclick="displayDoc();return false;" class="toolbar acym_button" style="margin-bottom: 5px;"><i class="acyicon-help" style="margin: 0px 5px;" title="<?php echo acym_translation('ACY_HELP'); ?>"></i><?php echo acym_translation('ACY_HELP'); ?></button>
					</div>
				</td>
            <?php } ?>
		</div>
	</div>
</div>
