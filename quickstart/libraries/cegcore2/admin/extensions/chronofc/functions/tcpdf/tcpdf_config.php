<?php
/* @copyright:ChronoEngine.com @license:GPLv2 */defined('_JEXEC') or die('Restricted access');
defined("GCORE_SITE") or die;
?>
<div class="ui segment tab functions-tab active" data-tab="function-<?php echo $n; ?>">

	<div class="ui top attached tabular menu small G2-tabs">
		<a class="item active" data-tab="function-<?php echo $n; ?>-general"><?php el('General'); ?></a>
		<a class="item" data-tab="function-<?php echo $n; ?>-advanced"><?php el('Advanced'); ?></a>
		<a class="item" data-tab="function-<?php echo $n; ?>-permissions"><?php el('Permissions'); ?></a>
	</div>
	
	<div class="ui bottom attached tab segment active" data-tab="function-<?php echo $n; ?>-general">
		<input type="hidden" value="tcpdf" name="Connection[functions][<?php echo $n; ?>][type]">
		
		<div class="two fields advanced_conf">
			<div class="field">
				<label><?php el('Name'); ?></label>
				<input type="text" value="" name="Connection[functions][<?php echo $n; ?>][name]">
			</div>
		</div>
		
		<div class="ui segment active" data-tab="function-<?php echo $n; ?>">
			
			<div class="two fields">
				
				<div class="field">
					<label><?php el('Action'); ?></label>
					<select name="Connection[functions][<?php echo $n; ?>][pdf_view]" class="ui fluid dropdown">
						<option value="D"><?php el('Download'); ?></option>
						<option value="F"><?php el('Store'); ?></option>
						<option value="I"><?php el('Inline display'); ?></option>
						<option value="FI"><?php el('Store and Inline display'); ?></option>
						<option value="FD"><?php el('Store and download'); ?></option>
						<option value="S"><?php el('String data'); ?></option>
					</select>
				</div>
				
			</div>
			
			<div class="field">
				<label><?php el('Content'); ?>
				<i class="icon green write circular" onclick="jQuery.G2.tinymce.init('#tcpdf_editor<?php echo $n; ?>');" data-hint="<?php el('Enable WYSIWYG editor'); ?>"></i>
				<i class="icon red cancel circular" onclick="jQuery.G2.tinymce.remove('#tcpdf_editor<?php echo $n; ?>');" data-hint="<?php el('Disable WYSIWYG editor'); ?>"></i>
				</label>
				<textarea name="Connection[functions][<?php echo $n; ?>][content]" rows="8" data-editor="1" id="tcpdf_editor<?php echo $n; ?>"></textarea>
			</div>
			
			<div class="field">
				<label><?php el('File name'); ?></label>
				<input type="text" value="pdf<?php echo $n; ?>.pdf" name="Connection[functions][<?php echo $n; ?>][file_name]">
			</div>
			
			<div class="field private_config">
				<label><?php el('Storage path'); ?></label>
				<input type="text" value="{path:front}<?php echo DS.'pdf'.DS.'pdf'.$n.'.pdf'; ?>" name="Connection[functions][<?php echo $n; ?>][file_path]">
			</div>
			
			<div class="field">
				<label><?php el('Title'); ?></label>
				<input type="text" value="" name="Connection[functions][<?php echo $n; ?>][pdf_title]">
			</div>
			
			<div class="field">
				<label><?php el('Header'); ?></label>
				<input type="text" value="" name="Connection[functions][<?php echo $n; ?>][pdf_header]">
			</div>
			
		</div>
		
	</div>
	
	<div class="ui bottom attached tab segment" data-tab="function-<?php echo $n; ?>-advanced">
		
		<div class="two fields">
			<div class="field">
				<label><?php el('Orientation'); ?></label>
				<select name="Connection[functions][<?php echo $n; ?>][pdf_page_orientation]" class="ui fluid dropdown">
					<option value="P"><?php el('Portrait'); ?></option>
					<option value="L"><?php el('Landscape'); ?></option>
				</select>
			</div>
			<div class="field">
				<label><?php el('Page format'); ?></label>
				<input type="text" value="A4" name="Connection[functions][<?php echo $n; ?>][pdf_page_format]">
			</div>
		</div>
		
		<div class="ui header dividing"><?php el('Page margins'); ?></div>
		
		<div class="four fields">
			<div class="field">
				<label><?php el('Top margin'); ?></label>
				<input type="text" value="27" name="Connection[functions][<?php echo $n; ?>][pdf_margin_top]">
			</div>
			<div class="field">
				<label><?php el('Bottom margin'); ?></label>
				<input type="text" value="25" name="Connection[functions][<?php echo $n; ?>][pdf_margin_bottom]">
			</div>
			<div class="field">
				<label><?php el('Right margin'); ?></label>
				<input type="text" value="15" name="Connection[functions][<?php echo $n; ?>][pdf_margin_right]">
			</div>
			<div class="field">
				<label><?php el('Left margin'); ?></label>
				<input type="text" value="15" name="Connection[functions][<?php echo $n; ?>][pdf_margin_left]">
			</div>
		</div>
		
		<div class="two fields">
			<div class="field">
				<label><?php el('Header margin'); ?></label>
				<input type="text" value="5" name="Connection[functions][<?php echo $n; ?>][pdf_margin_header]">
			</div>
			<div class="field">
				<label><?php el('Footer margin'); ?></label>
				<input type="text" value="10" name="Connection[functions][<?php echo $n; ?>][pdf_margin_footer]">
			</div>
		</div>
		
		<div class="ui header dividing"><?php el('Document info'); ?></div>
		
		<div class="two fields">
			<div class="field">
				<label><?php el('Author'); ?></label>
				<input type="text" value="Chronoforms" name="Connection[functions][<?php echo $n; ?>][pdf_author]">
			</div>
			<div class="field">
				<label><?php el('Subject'); ?></label>
				<input type="text" value="Powered by Chronoforms & TCPDF" name="Connection[functions][<?php echo $n; ?>][pdf_subject]">
			</div>
		</div>
		
		<div class="field">
			<label><?php el('Keywords'); ?></label>
			<input type="text" value="Chronoforms, TCPDF Plugin, TCPDF, PDF" name="Connection[functions][<?php echo $n; ?>][pdf_keywords]">
		</div>
		
		<div class="ui header dividing"><?php el('Fonts'); ?></div>
		
		<div class="two fields">
			<div class="field">
				<label><?php el('Body font'); ?></label>
				<input type="text" value="courier" name="Connection[functions][<?php echo $n; ?>][pdf_body_font]">
			</div>
			<div class="field">
				<label><?php el('Body font size'); ?></label>
				<input type="text" value="14" name="Connection[functions][<?php echo $n; ?>][pdf_body_font_size]">
			</div>
		</div>
		
		<div class="two fields">
			<div class="field">
				<label><?php el('Header font'); ?></label>
				<input type="text" value="helvetica" name="Connection[functions][<?php echo $n; ?>][pdf_header_font]">
			</div>
			<div class="field">
				<label><?php el('Header font size'); ?></label>
				<input type="text" value="10" name="Connection[functions][<?php echo $n; ?>][pdf_header_font_size]">
			</div>
		</div>
		
		<div class="two fields">
			<div class="field">
				<label><?php el('Footer font'); ?></label>
				<input type="text" value="helvetica" name="Connection[functions][<?php echo $n; ?>][pdf_footer_font]">
			</div>
			<div class="field">
				<label><?php el('Footer font size'); ?></label>
				<input type="text" value="8" name="Connection[functions][<?php echo $n; ?>][pdf_footer_font_size]">
			</div>
		</div>
		
		<div class="ui header dividing"><?php el('Security'); ?></div>
		
		<div class="three fields">
			<div class="field">
				<label><?php el('Enable password'); ?></label>
				<select name="Connection[functions][<?php echo $n; ?>][enable_protection]" class="ui fluid dropdown">
					<option value="0"><?php el('No'); ?></option>
					<option value="1"><?php el('Yes'); ?></option>
				</select>
			</div>
			<div class="field">
				<label><?php el('User pass'); ?></label>
				<input type="text" value="" name="Connection[functions][<?php echo $n; ?>][user_pass]">
			</div>
			<div class="field">
				<label><?php el('Owner pass'); ?></label>
				<input type="text" value="" name="Connection[functions][<?php echo $n; ?>][owner_pass]">
			</div>
		</div>
		
	</div>
	
	<div class="ui bottom attached tab segment" data-tab="function-<?php echo $n; ?>-permissions">
		<div class="two fields">
			<div class="field">
				<label><?php el('Owner id value'); ?></label>
				<input type="text" value="" name="Connection[functions][<?php echo $n; ?>][owner_id]">
			</div>
		</div>
		
		<?php $this->view('views.permissions_manager', ['model' => 'Connection[functions]['.$n.']', 'perms' => ['access' => rl('Access')], 'groups' => $this->get('groups')]); ?>
	</div>
	
</div>