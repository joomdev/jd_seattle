<?php
/**
 * @package	AcyMailing for Joomla!
 * @version	5.10.3
 * @author	acyba.com
 * @copyright	(C) 2009-2018 ACYBA S.A.R.L. All rights reserved.
 * @license	GNU/GPLv3 http://www.gnu.org/licenses/gpl-3.0.html
 */

defined('_JEXEC') or die('Restricted access');
?><div id="acy_content">
	<div id="iframedoc"></div>
	<form action="<?php echo acymailing_completeLink(acymailing_getVar('cmd', 'ctrl')); ?>" method="post" name="adminForm" enctype="multipart/form-data" id="adminForm">
		<input type="hidden" name="import_type" id="import_type" value="<?php echo $this->type; ?>"/>
		<input type="hidden" name="filename" id="filename" value="<?php echo acymailing_getVar('cmd', 'filename'); ?>"/>
		<input type="hidden" name="import_columns" id="import_columns" value=""/>
		<input type="hidden" name="createlist" id="createlist" value="<?php echo acymailing_getVar('string', 'createlist'); ?>"/>
		<?php
		$checkedLists = acymailing_getVar('array', 'importlists', array(), '');
		foreach($checkedLists as $key => $oneList){
			echo '<input type="hidden" name="importlists['.intval($key).']" id="importlists'.intval($key).'-'.intval($oneList).'" value="'.intval($oneList).'"/>';
		}

		if(!empty($this->Itemid)) echo '<input type="hidden" name="Itemid" value="'.$this->Itemid.'" />';
		acymailing_formOptions(); ?>

		<div class="onelineblockoptions" id="matchdata">
			<?php include_once(ACYMAILING_BACK.'views'.DS.'data'.DS.'tmpl'.DS.'ajaxencoding.php'); ?>
			<div class="loading" align="center"><?php echo acymailing_translation_sprintf('ACY_FIRST_LINES', ($nbLines < 11 - $noHeader ? ($nbLines - 1 + $noHeader) : 10)); ?></div>
		</div>

		<div class="onelineblockoptions">
			<span class="acyblocktitle">Parameters</span>
			<table class="acymailing_table" cellspacing="1">
				<tr id="trfilecharset">
					<td class="acykey">
						<?php echo acymailing_translation('CHARSET_FILE'); ?>
					</td>
					<td>
						<?php
						$charsetType = acymailing_get('type.charset');
						$charsetType->addinfo = 'onchange="changeCharset();"';
						$this->type = empty($this->type) ? '' : $this->type;
						if($this->type == 'textarea'){
							$default = 'UTF-8';
						}elseif($this->type == 'file'){
							$default = $encodingHelper->detectEncoding($this->content);
						}
						echo $charsetType->display('charsetconvert', $default);
						?>
						<span id="loadingEncoding"></span>
					</td>
				</tr>
				<?php if($this->config->get('require_confirmation')){ ?>
					<tr id="trfileconfirm">
						<td class="acykey">
							<?php echo acymailing_translation('IMPORT_CONFIRMED'); ?>
						</td>
						<td>
							<?php echo acymailing_boolean("import_confirmed", '', in_array('import_confirmed', $this->selectedParams) ? 1 : 0, acymailing_translation('JOOMEXT_YES'), acymailing_translation('JOOMEXT_NO')); ?>
						</td>
					</tr>
				<?php } ?>
				<tr id="trfilegenerate">
					<td class="acykey">
						<?php echo acymailing_translation('GENERATE_NAME'); ?>
					</td>
					<td>
						<?php echo acymailing_boolean("generatename", '', in_array('generatename', $this->selectedParams) ? 1 : 0, acymailing_translation('JOOMEXT_YES'), acymailing_translation('JOOMEXT_NO')); ?>
					</td>
				</tr>
				<tr id="trfileblock">
					<td class="acykey">
						<?php echo acymailing_translation('IMPORT_BLOCKED'); ?>
					</td>
					<td>
						<?php echo acymailing_boolean("importblocked", '', in_array('importblocked', $this->selectedParams) ? 1 : 0, acymailing_translation('JOOMEXT_YES'), acymailing_translation('JOOMEXT_NO')); ?>
					</td>
				</tr>
				<tr id="trfileoverwrite">
					<td class="acykey">
						<?php echo acymailing_translation('OVERWRITE_EXISTING'); ?>
					</td>
					<td>
						<?php echo acymailing_boolean("overwriteexisting", '', in_array('overwriteexisting', $this->selectedParams) ? 1 : 0, acymailing_translation('JOOMEXT_YES'), acymailing_translation('JOOMEXT_NO')); ?>
					</td>
				</tr>
			</table>
		</div>
		<div class="onelineblockoptions">
			<span class="acyblocktitle"><?php echo acymailing_translation('SUBSCRIPTION'); ?></span>
			<table class="acymailing_table" cellspacing="1">
				<tr id="trsumup">
					<td>
						<?php
						echo acymailing_translation('ACY_IMPORT_LISTS').' : '.(empty($this->lists) ? acymailing_translation('ACY_NONE') : htmlspecialchars($this->lists, ENT_COMPAT, 'UTF-8'));
						echo '<br />'.acymailing_translation('ACY_IMPORT_UNSUB_LISTS').' : '.(empty($this->unsublists) ? acymailing_translation('ACY_NONE') : htmlspecialchars($this->unsublists, ENT_COMPAT, 'UTF-8'));
						?>
					</td>
				</tr>
			</table>
		</div>
	</form>
	<script language="javascript" type="text/javascript">
		<!--
		document.addEventListener("DOMContentLoaded", function(){
			acymailing.submitbutton = function(pressbutton){
				if(pressbutton == 'finalizeimport'){
					var subval = true;
					var errors = "";
					var string = "";
					var emailField = false;
					var columns = "";
					var selectedFields = Array();
					var fieldNb = <?php echo $nbColumns; ?>;
					if(isNaN(fieldNb)) fieldNb = 1;

					for(var i = 0; i < fieldNb; i++){
						if(document.getElementById("newcustom" + i).required){
							string = document.getElementById("newcustom" + i).value;
							if(string == ""){
								subval = false;
								errors += "\nNew custom field's name (column " + (i + 1) + ")";
							}else{
								if(!string.match(/^[A-Za-z][A-Za-z0-9_]+$/)){
									subval = false;
									errors += "\nPlease enter a valid field name for the column n°" + (i + 1) + ": spaces, uppercase and special characters are not allowed";
								}else{
									if(string != 1 && selectedFields.indexOf(string) != -1){
										subval = false;
										errors += "\nDuplicate field \"" + string + "\" for the column n°" + (i + 1);
									}else{
										if(string != 0){
											selectedFields.push(string);
										}
									}
									columns += "," + string;
								}
							}
						}else{
							string = document.getElementById("fieldAssignment" + i).value;
							if(string == 0){
								subval = false;
								errors += "\nAssign the column " + (i + 1) + " to a field";
							}

							if(string == 'email'){
								emailField = true;
							}

							if(string != 1 && selectedFields.indexOf(string) != -1){
								subval = false;
								errors += "\nDuplicate field \"" + string + "\" for the column " + (i + 1);
							}else{
								selectedFields.push(string);
							}

							columns += "," + string;
						}
					}

					if(!emailField){
						subval = false;
						errors += "\nPlease assign a column for the e-mail field";
					}

					if(subval == false){
						alert("<?php echo acymailing_translation('FILL_ALL'); ?>:\n" + errors);
						return false;
					}

					if(columns.substr(0, 1) == ","){
						columns = columns.substring(1);
					}

					document.getElementById("import_columns").value = columns;
				}

				acymailing.submitform(pressbutton, document.adminForm);
			}
		});

		function checkNewCustom(key){
			if(document.getElementById("fieldAssignment" + key).value == 2){
				document.getElementById("newcustom" + key).style.display = "";
				document.getElementById("newcustom" + key).required = true;
			}else{
				document.getElementById("newcustom" + key).style.display = "none";
				document.getElementById("newcustom" + key).required = false;
			}
		}

		function changeCharset(){
			var URL = "<?php echo acymailing_prepareAjaxURL((acymailing_isAdmin() ? '' : 'front').'data'); ?>&encoding=" + document.getElementById("charsetconvert").value + "&task=ajaxencoding&filename=<?php echo urlencode($filename); ?>";
			var selectedDropdowns = "";
			var fieldNb = <?php echo $nbColumns; ?>;
			if(isNaN(fieldNb)) fieldNb = 1;

			for(var i = 0; i < fieldNb; i++){
				selectedDropdowns += "&fieldAssignment" + i + "=" + document.getElementById("fieldAssignment" + i).value;
				if(document.getElementById("newcustom" + i).required){
					selectedDropdowns += "&newcustom" + i + "=" + document.getElementById("newcustom" + i).value;
				}
			}

			URL += selectedDropdowns;


			document.getElementById("loadingEncoding").innerHTML = '<span class=\"onload\"></span>';
			document.getElementById("importdata").style.opacity = "0.5";
			document.getElementById("importdata").style.filter = 'alpha(opacity=50)';

			var xhr = new XMLHttpRequest();
			xhr.open("GET", URL);
			xhr.onload = function(){
				document.getElementById("matchdata").innerHTML = xhr.responseText;
				document.getElementById("loadingEncoding").innerHTML = '';
			}
			xhr.send();
		}

		function ignoreAllOthers(){
			var fieldNb = document.adminForm.newcustom.length;
			if(isNaN(fieldNb)) fieldNb = 1;

			for(var i = 0; i < fieldNb; i++){
				if(document.getElementById("fieldAssignment" + i).value == 0){
					document.getElementById("fieldAssignment" + i).value = 1;
				}
			}
		}
		-->
	</script>
</div>
