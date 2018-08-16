<?php
/**
 * @package	AcyMailing for Joomla!
 * @version	5.10.3
 * @author	acyba.com
 * @copyright	(C) 2009-2018 ACYBA S.A.R.L. All rights reserved.
 * @license	GNU/GPLv3 http://www.gnu.org/licenses/gpl-3.0.html
 */

defined('_JEXEC') or die('Restricted access');
?><style type="text/css">
	body{
		height: auto;
		min-width: 650px !important;
	}

	html{
		overflow-y: auto;
	}

	.rt-container, .rt-block{
		width: auto !important;
		background-color: #f6f7f9 !important;
	}
</style>
<div id="acy_content">
	<div id="acymailing_edit" class="acytagpopup">
		<?php
		if(empty($this->tagsfamilies)) acymailing_checkPluginsFolders();
		?>
		<table width="100%">
			<tr>
				<td class="familymenu" valign="top">
					<?php
					foreach($this->tagsfamilies as $id => $oneFamily){
						if(empty($oneFamily)) continue;
						if($oneFamily->function == $this->fctplug){
							$help = empty($oneFamily->help) ? '' : $oneFamily->help;
							$class = ' class="selected" ';
						}else $class = '';
						echo '<a'.$class.' href="'.acymailing_completeLink($this->ctrl.'&task=tag&type='.$this->type.'&fctplug='.$oneFamily->function, true).'" >'.$oneFamily->name.'</a>';
					}
					?>
				</td>
				<?php if(!empty($help) AND acymailing_isAdmin()){ ?>
					<td valign="top">
						<div style="float:right;padding-right:5px;" class="toolbar">
							<?php
							$toolbar = acymailing_get('helper.toolbar');
							$toolbar->help($help);
							?>
							<button onclick="displayDoc();return false;" class="toolbar acymailing_button" style="margin-bottom: 5px;"><i class="acyicon-help" style="margin: 0px 5px;" title="<?php echo acymailing_translation('ACY_HELP'); ?>"></i><?php echo acymailing_translation('ACY_HELP'); ?></button>
						</div>
					</td>
				<?php } ?>
			</tr>
		</table>
		<div id="iframedoc" style="clear:both;position:relative;"></div>
		<div id="inserttagdiv">
			<input type="text" class="inputbox" style="width:300px;" id="tagstring" name="tagstring" value="" onclick="this.select();">
			<button class="acymailing_button" id="insertButton" onclick="insertTag();"><?php echo acymailing_translation('INSERT_TAG') ?></button>
		</div>
		<form action="<?php echo acymailing_completeLink(acymailing_getVar('cmd', 'ctrl')); ?>" method="post" name="adminForm" id="adminForm" autocomplete="off" enctype="multipart/form-data">
			<div id="plugarea">
				<?php echo $this->defaultContent; ?>
			</div>
			<div class="clr"></div>

			<input type="hidden" id="fctplug" name="fctplug" value="<?php echo $this->fctplug; ?>"/>
			<input type="hidden" name="type" value="<?php echo $this->type; ?>"/>
			<input type="hidden" name="defaulttask" value="tag"/>
			<?php acymailing_formOptions(); ?>
		</form>
	</div>
</div>
