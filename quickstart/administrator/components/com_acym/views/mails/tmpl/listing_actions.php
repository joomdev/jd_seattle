<?php
defined('_JEXEC') or die('Restricted access');
?><?php
$postMaxSize = ini_get('post_max_size');
$uploadMaxSize = ini_get('upload_max_filesize');
$maxSize = acym_translation_sprintf(
    'ACYM_MAX_UPLOAD',
    acym_bytes($uploadMaxSize) > acym_bytes($postMaxSize) ? $postMaxSize : $uploadMaxSize
);
$templateTips = '<div class="text-center padding-0 cell grid-x text-center align-center">
							<input type="file" style="width:auto" name="uploadedfile" class="cell"/>
							<div class="cell">'.$maxSize.'</div>
						</div>
						<div class="cell margin-top-2 margin-bottom-2">
							'.acym_translation('ACYM_IMPORT_INFO').'
							<ul>
								<li>'.acym_translation('ACYM_TEMLPATE_ZIP_IMPORT').'</li>
								<ul>
									<li>/template.html -> '.acym_translation('ACYM_TEMPLATE_HTML_IMPORT').'</li>
									<li>/css -> '.acym_translation('ACYM_TEMPLATE_CSS_IMPORT').'</li>
									<li>/images -> '.acym_translation('ACYM_TEMPLATE_IMAGES_IMPORT').'</li>
									<li>/thumbnail.png -> '.acym_translation('ACYM_TEMPLATE_THUMBNAIL_IMPORT').'</li>
								</ul>
							</ul>
						</div>
					   <div class="cell grid-x align-center">
							<button type="button" data-task="doUploadTemplate" class="acy_button_submit button cell shrink margin-1">'.acym_translation('ACYM_IMPORT').'</button>
					   </div>';
?>
<div class="xlarge-4 medium-auto cell text-center cell grid-x grid-margin-x text-right">
    <?php
    echo acym_modal(
        acym_translation('ACYM_CREATE_TEMPLATE'),
        '<div class="cell grid-x grid-margin-x">
								<button type="button" data-task="edit" data-editor="acyEditor" class="acym__create__template button cell medium-auto margin-top-1">'.acym_translation('ACYM_DD_EDITOR').'</button>
								<button type="button" data-task="edit" data-editor="html" class="acym__create__template button cell large-auto small-6 margin-top-1 button-secondary">'.acym_translation('ACYM_HTML_EDITOR').'</button>
							</div>',
        '',
        '',
        'class="button cell auto"',
        true,
        false
    );
    ?>
	<button type="button" id="acym__mail__install-default" class="button cell auto button-secondary acy_button_submit" data-task="installDefaultTmpl">
        <?php echo acym_translation('ACYM_ADD_DEFAULT_TMPL'); ?>
	</button>
    <?php
    echo acym_modal(
        acym_translation('ACYM_IMPORT'),
        $templateTips,
        null,
        '',
        'class="button cell medium-auto button-secondary" data-reload="true" data-ajax="false"'
    );
    ?>
</div>

