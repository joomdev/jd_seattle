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
	<form action="<?php echo acymailing_completeLink('template'); ?>" method="post" name="adminForm" id="adminForm" class="templateManagement" enctype="multipart/form-data">
		<div class="acyblockoptions" id="sendtest" style="float:none;<?php if(acymailing_getVar('cmd', 'task') != 'test') echo 'display:none;'; ?>">
			<span class="acyblocktitle"><?php echo acymailing_translation('SEND_TEST'); ?></span>
			<table>
				<tr>
					<td valign="top">
						<?php echo acymailing_translation('SEND_TEST_TO'); ?>
					</td>
					<td>
						<?php echo $this->testreceiverType->display($this->infos->test_selection, $this->infos->test_group, $this->infos->test_emails); ?>
					</td>
				</tr>
				<tr>
					<td/>
					<td>
						<button type="submit" class="acymailing_button" onclick="var val = document.getElementById('message_receivers').value; if(val != ''){ setUser(val); } acymailing.submitbutton('test');return false;"><?php echo acymailing_translation('SEND_TEST') ?></button>
					</td>
				</tr>
			</table>
		</div>

		<div class="acyblockoptions">
			<span class="acyblocktitle"><?php echo acymailing_translation('ACY_TEMPLATE_INFORMATIONS'); ?></span>
			<table>
				<tr>
					<td>
						<label for="name">
							<?php echo acymailing_translation('TEMPLATE_NAME'); ?>
						</label>
					</td>
					<td>
						<input type="text" name="data[template][name]" id="name" class="inputbox" style="width:200px" value="<?php echo $this->escape(@$this->template->name); ?>"/>
					</td>
				</tr>
				<tr>
					<td>
						<label for="published">
							<?php echo acymailing_translation('ACY_PUBLISHED'); ?>
						</label>
					</td>
					<td>
						<?php echo acymailing_boolean("data[template][published]", '', @$this->template->published); ?>
					</td>
				</tr>
				<tr>
					<td>
						<label for="default">
							<?php echo acymailing_translation('ACY_DEFAULT'); ?>
						</label>
					</td>
					<td>
						<?php echo acymailing_boolean("data[template][premium]", '', @$this->template->premium); ?>
					</td>
				</tr>
				<?php if(acymailing_level(3)){ ?>
					<tr>
						<td>
							<label for="datatemplatecategory">
								<?php echo acymailing_translation('ACY_CATEGORY'); ?>
							</label>
						</td>
						<td>
							<?php $catType = acymailing_get('type.categoryfield');
							echo $catType->display('template', 'data[template][category]', $this->template->category); ?>
						</td>
					</tr>
				<?php } ?>
				<tr>
					<td>
						<label for="thumb">
							<?php echo acymailing_translation('ACY_THUMBNAIL'); ?>
						</label>
					</td>
					<td>
						<?php
						$uploadfileType = acymailing_get('type.uploadfile');
						echo $uploadfileType->display(true, 'thumb', $this->template->thumb, 'data[template][thumb]');
						?>
					</td>
				</tr>
				<tr>
					<td valign="top">
						<label for="description">
							<?php echo acymailing_translation('ACY_DESCRIPTION'); ?>
						</label>
					</td>
					<td>
						<textarea id="description" name="editor_description" style="width:90%;height:80px;"><?php echo @$this->template->description; ?></textarea>
					</td>
				</tr>
				<tr>
					<td>
						<label for="subject">
							<?php echo acymailing_translation('JOOMEXT_SUBJECT'); ?>
						</label>
					</td>
					<td>
						<div>
							<input onClick="zoneToTag='subject';" type="text" id="subject" name="data[template][subject]" class="inputbox" style="width:80%" value="<?php echo $this->escape(@$this->template->subject); ?>"/>
						</div>
					</td>
				</tr>
				<tr>
					<td class="paramlist_key">
						<label for="fromname"><?php echo acymailing_translation('FROM_NAME'); ?></label>
					</td>
					<td class="paramlist_value">
						<input class="inputbox" id="fromname" type="text" name="data[template][fromname]" style="width:200px" value="<?php echo $this->escape(@$this->template->fromname); ?>"/>
					</td>
				</tr>
				<tr>
					<td class="paramlist_key">
						<label for="fromemail"><?php echo acymailing_translation('FROM_ADDRESS'); ?></label>
					</td>
					<td class="paramlist_value">
						<input onchange="validateEmail(this.value, '<?php echo addslashes(acymailing_translation('FROM_ADDRESS')); ?>')" class="inputbox" id="fromemail" type="text" name="data[template][fromemail]" style="width:200px" value="<?php echo $this->escape(@$this->template->fromemail); ?>"/>
					</td>
				</tr>
				<tr>
					<td class="paramlist_key">
						<label for="replyname"><?php echo acymailing_translation('REPLYTO_NAME'); ?></label>
					</td>
					<td class="paramlist_value">
						<input class="inputbox" id="replyname" type="text" name="data[template][replyname]" style="width:200px" value="<?php echo $this->escape(@$this->template->replyname); ?>"/>
					</td>
				</tr>
				<tr>
					<td class="paramlist_key">
						<label for="replyemail"><?php echo acymailing_translation('REPLYTO_ADDRESS'); ?></label>
					</td>
					<td class="paramlist_value">
						<input onchange="validateEmail(this.value, '<?php echo addslashes(acymailing_translation('REPLYTO_ADDRESS')); ?>')" class="inputbox" id="replyemail" type="text" name="data[template][replyemail]" style="width:200px" value="<?php echo $this->escape(@$this->template->replyemail); ?>"/>
					</td>
				</tr>
			</table>
		</div>
		<?php echo acymailing_getFunctionsEmailCheck(); ?>

		<div class="acyblockoptions">
			<span class="acyblocktitle"><?php echo acymailing_translation('ACY_STYLES'); ?></span>
			<?php
			echo $this->tabs->startPane('template_css');
			echo $this->tabs->startPanel(acymailing_translation('STYLE_IND'), 'template_css_classes'); ?>
			<br style="font-size:1px"/>

			<table width="100%">
				<tbody id="classtable">
				<tr>
					<td>
						<label for="bgcolor">
							<?php echo acymailing_translation('BACKGROUND_COLOUR'); ?>
						</label>
					</td>
					<td>
						<?php echo $this->colorBox->displayAll('', 'styles[color_bg]', @$this->template->styles['color_bg']); ?>
					</td>
				</tr>
				<?php $tagList = array('tag_h1' => 'Title h1', 'tag_h2' => 'Title h2', 'tag_h3' => 'Title h3', 'tag_h4' => 'Title h4', 'tag_h5' => 'Title h5', 'tag_h6' => 'Title h6', 'tag_a' => acymailing_translation('ACY_LINK_STYLE'), 'acymailing_unsub' => acymailing_translation('STYLE_UNSUB'), 'acymailing_content' => acymailing_translation('CONTENT_AREA'), 'acymailing_title' => acymailing_translation('CONTENT_HEADER'), 'acymailing_readmore' => acymailing_translation('CONTENT_READMORE'), 'acymailing_online' => acymailing_translation('STYLE_VIEW'));
				foreach($tagList as $value => $text){ ?>
					<tr>
						<td><span id="name_<?php echo $value; ?>" style="<?php echo str_replace('!important', '', $this->escape(@$this->template->styles[$value])); ?>"><?php echo $text; ?></span></td>
						<td><input id="style_<?php echo $value; ?>" type="text" style="width:200px" onclick="showthediv('<?php echo $value; ?>',event);" name="styles[<?php echo $value; ?>]" value="<?php echo $this->escape(@$this->template->styles[$value]); ?>"/></td>
					</tr>
					<?php
					if($value == 'acymailing_readmore'){
						?>
						<tr>
							<td><?php echo acymailing_translation('READMORE_PICTURE'); ?></span></td>
							<td>
								<?php echo $uploadfileType->display(true, 'readmore', $this->template->readmore, 'data[template][readmore]'); ?>
							</td>
						</tr>
					<?php
					}
				}
				?>
				<tr>
					<td>
						<ul id="name_tag_ul" style="<?php echo $this->escape(@$this->template->styles['tag_ul']); ?>">
							<li id="name_tag_li2" style="<?php echo $this->escape(@$this->template->styles['tag_li']); ?>">ul</li>
							<li id="name_tag_li" style="<?php echo $this->escape(@$this->template->styles['tag_li']); ?>">li</li>
						</ul>
					</td>
					<td><input type="text" id="style_tag_ul" onclick="showthediv('tag_ul',event);" style="width:200px" name="styles[tag_ul]" value="<?php echo $this->escape(@$this->template->styles['tag_ul']); ?>"/>
						<br/><input type="text" id="style_tag_li" onclick="showthediv('tag_li',event);" style="width:200px" name="styles[tag_li]" value="<?php echo $this->escape(@$this->template->styles['tag_li']); ?>"/></td>
				</tr>
				<?php
				unset($this->template->styles['color_bg']);
				unset($this->template->styles['tag_ul']);
				unset($this->template->styles['tag_li']);
				if(!empty($this->template->styles)){
					foreach($this->template->styles as $className => $style){
						if(isset($tagList[$className])) continue;
						?>
						<tr>
							<td><span id="name_<?php echo $className ?>" style="<?php echo $this->escape($style); ?>"><?php echo $className ?></span></td>
							<td><input id="style_<?php echo $className ?>" type="text" style="width:200px" onclick="showthediv('<?php echo $className; ?>',event);" name="styles[<?php echo $className; ?>]" value="<?php echo $this->escape($style); ?>"/></td>
						</tr>
					<?php
					} ?>

				<?php }
				?>
				</tbody>
			</table>

			<a onclick="addStyle();return false;" href="#"><?php echo acymailing_translation('ADD_STYLE'); ?></a>
			<?php echo $this->tabs->startPanel(acymailing_translation('TEMPLATE_STYLESHEET'), 'template_css_stylesheet'); ?>
			<br style="font-size:1px"/>
			<?php
			$messages = array();
			if(version_compare(PHP_VERSION, '5.0.0', '<')) $messages[] = 'Please make sure you use at least PHP 5.0.0';
			if(!class_exists('DOMDocument')){
				$messages[] = 'DOMDocument class not found';
			}else{
				$xmldoc = @ new DOMDocument;
				if(!is_object($xmldoc) || !method_exists($xmldoc, 'loadHTML')){
					$messages[] = 'Please make sure that php_domxml.dll on windows is removed before using the domdocument class as they cannot coexist.';
				}
			}
			if(!function_exists('mb_convert_encoding')) $messages[] = 'The php extension mbstring is not installed';
			if(!empty($messages)){
				$messages[] = 'The stylesheet can not be used';
				acymailing_display($messages, 'warning');
			}else{ ?>
				<textarea onmouseover="document.getElementById('wysija').style.display = 'none'" name="data[template][stylesheet]" style="width:98%; min-width: 300px; min-height: 300px;" rows="25" id="acystylesheettextarea"><?php echo @$this->template->stylesheet; ?></textarea>
			<?php }
			echo $this->tabs->endPanel();
			echo $this->tabs->startPanel(acymailing_translation('ACY_HEADER'), 'template_css_header'); ?>
			<textarea name="data[template][header]" id="headertags" cols="10" rows="24" style="width: 98%; margin-top: 30px; font-size: 15px;"><?php echo $this->template->header ?></textarea>
			<?php
			echo $this->tabs->endPanel();
			echo $this->tabs->endPane(); ?>
		</div>
		<?php if(acymailing_level(3)){
			$acltype = acymailing_get('type.acl'); ?>
			<div class="acyblockoptions">
				<span class="acyblocktitle"><?php echo acymailing_translation('ACCESS_LEVEL'); ?></span>
				<?php echo $acltype->display('data[template][access]', $this->template->access); ?>
			</div>
		<?php } ?>
		<div class="acyblockoptions" style="width:90%" id="htmlfieldset">
			<span class="acyblocktitle"><?php echo acymailing_translation('HTML_VERSION'); ?></span>
			<?php echo $this->editor->display(); ?>
		</div>
		<div class="acyblockoptions" style="width:90%;" id="textfieldset">
			<span class="acyblocktitle"><?php echo acymailing_translation('TEXT_VERSION'); ?></span>
			<textarea onClick="zoneToTag='altbody';" style="width:98%;min-height:250px;" rows="20" name="data[template][altbody]" id="altbody" placeholder="<?php echo acymailing_translation('AUTO_GENERATED_HTML'); ?>"><?php echo @$this->template->altbody; ?></textarea>
		</div>
		<div class="clr"></div>
		<input type="hidden" name="cid[]" value="<?php echo @$this->template->tempid; ?>"/>
		<?php acymailing_formOptions(); ?>
	</form>
	<div style="display:none;position:absolute;background-color:transparent;" id="wysija">
		<?php echo $this->colorBox->displayOne('wysijacolor', "", ""); ?>
		<select style="width:75px;height:17px;margin:0px;font-size:11px;" class="chzn-done" id="style_select_wysija" onchange="getValueSelect()">
			<?php $nbs = array('8', '10', '11', '12', '14', '16', '18', '20', '22', '24', '26', '36');
			echo "<option value=''>Font Size</option>";
			foreach($nbs as $nb){
				echo "<option value='".$nb."px'>$nb px.</option>";
			} ?>
		</select>

		<span id="B" onclick="spanChange('B')" class="belement"></span><span id="I" class="ielement" onclick="spanChange('I')"></span><span class="uelement" id="U" onclick="spanChange('U')"></span>
	</div>
</div>
