<?php
/**
 * @package	AcyMailing for Joomla!
 * @version	5.10.3
 * @author	acyba.com
 * @copyright	(C) 2009-2018 ACYBA S.A.R.L. All rights reserved.
 * @license	GNU/GPLv3 http://www.gnu.org/licenses/gpl-3.0.html
 */

defined('_JEXEC') or die('Restricted access');
?><?php $config = acymailing_config(); ?>
<div id="acy_content">
	<div id="iframedoc"></div>
	<form action="<?php echo acymailing_completeLink(acymailing_getVar('cmd', 'ctrl')); ?>" method="post" name="adminForm" enctype="multipart/form-data" id="adminForm">
		<?php if(!empty($this->Itemid)) echo '<input type="hidden" name="Itemid" value="'.$this->Itemid.'" />';
		acymailing_formOptions(); ?>
		<div style="width:100%;">
			<div id="import_mode_container">
				<div id="import_mode" class="<?php echo $this->isAdmin ? 'acyblockoptions' : 'onelineblockoptions'; ?>">
					<span class="acyblocktitle"><?php echo acymailing_translation('IMPORT_FROM'); ?></span>
					<?php echo acymailing_radio($this->importvalues, 'importfrom', 'class="inputbox" size="1" onclick="updateImport(this.value);"', 'value', 'text', acymailing_getVar('cmd', 'importfrom', 'textarea')); ?>
				</div>
			</div>
			<div id="import_options" class="<?php echo $this->isAdmin ? 'acyblockoptions' : 'onelineblockoptions'; ?>">
				<?php foreach($this->importdata as $div => $name){
					echo '<div id="'.$div.'"';
					if($div != acymailing_getVar('cmd', 'importfrom', 'textarea')) echo ' style="display:none"';
					echo '>';
					echo '<span class="acyblocktitle">'.$name.'</span>';
					include(dirname(__FILE__).DS.$div.'.php');
					echo '</div>';
				} ?>
			</div>
			<div class="<?php echo $this->isAdmin ? 'acyblockoptions' : 'onelineblockoptions'; ?>" id="importlists">
				<span class="acyblocktitle"><?php echo acymailing_translation('SUBSCRIPTION'); ?></span>
				<?php if(acymailing_isAllowed($this->config->get('acl_lists_manage', 'all'))){ ?>
					<table class="acymailing_table" cellpadding="1">
						<tr class="<?php echo "row1"; ?>" id="importcreatelist">
							<td colspan="2">
								<?php echo acymailing_translation('IMPORT_SUBSCRIBE_CREATE').' : <input type="text" name="createlist" placeholder="'.acymailing_translation('LIST_NAME').'" />'; ?>
							</td>
						</tr>
					</table>
				<?php }
				$currentPage = 'import';
				$currentValues = acymailing_getVar('none', 'importlists');
				$listid = acymailing_getVar('int', 'listid');
				include_once(ACYMAILING_BACK.'views'.DS.'list'.DS.'tmpl'.DS.'filter.lists.php');
				?>
			</div>
		</div>
	</form>
	<div style="clear: both;"></div>
</div>
