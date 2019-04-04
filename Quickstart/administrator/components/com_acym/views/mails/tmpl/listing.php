<?php
/**
 * @package	AcyMailing for Joomla
 * @version	6.1.2
 * @author	acyba.com
 * @copyright	(C) 2009-2019 ACYBA S.A.R.L. All rights reserved.
 * @license	GNU/GPLv3 http://www.gnu.org/licenses/gpl-3.0.html
 */

defined('_JEXEC') or die('Restricted access');
?><form id="acym_form" action="<?php echo acym_completeLink(acym_getVar('cmd', 'ctrl')); ?>" method="post" name="acyForm" enctype="multipart/form-data">
	<input type="hidden" id="acym_create_template_type_editor" name="type_editor">
	<div id="acym__templates" class="acym__content">
        <?php if (empty($data['allMails']) && empty($data['search']) && empty($data['tag']) && empty($data['status'])) { ?>
			<div class="grid-x text-center">
				<h1 class="acym__listing__empty__title cell"><?php echo acym_translation('ACYM_YOU_DONT_HAVE_ANY_TEMPLATE'); ?></h1>
				<h1 class="acym__listing__empty__subtitle cell"><?php echo acym_translation('ACYM_CREATE_AN_AMAZING_TEMPLATE_WITH_OUR_AMAZING_EDITOR'); ?></h1>
				<div class="medium-3"></div>
				<div class="medium-6 small-12 cell">
					<div class="xlarge-4 medium-auto cell text-center cell grid-x grid-margin-x text-right">
						<button type="button" data-task="edit" data-editor="acyEditor" class="acym__create__template button cell medium-auto margin-top-1">
                            <?php echo acym_translation('ACYM_DD_EDITOR'); ?>
						</button>
						<div class="cell medium-shrink hide-for-medium hide-for-small"></div>
						<button type="button" data-task="edit" data-editor="html" class="acym__create__template button cell medium-auto margin-top-1 button-secondary">
							HTML
						</button>
                        <?php
                        $linkImport = acym_completeLink('mails&task=import', true);
                        echo acym_modal(
                            acym_translation('ACYM_IMPORT'),
                            '',
                            null,
                            '',
                            'class="button cell medium-auto margin-top-1 button-secondary" data-reload="true" data-ajax="false" data-iframe="'.$linkImport.'"'
                        );
                        ?>
					</div>
				</div>
				<div class="medium-3"></div>
			</div>
        <?php } else { ?>
			<div class="grid-x grid-margin-x">
			<div class="large-3 medium-8 cell">
                <?php echo acym_filterSearch(htmlspecialchars($data["search"]), 'mails_search', 'ACYM_SEARCH_TEMPLATE'); ?>
			</div>
			<div class="large-3 medium-4 cell">
                <?php
                $allTags = new stdClass();
                $allTags->name = acym_translation('ACYM_ALL_TAGS');
                $allTags->value = '';
                array_unshift($data["allTags"], $allTags);

                echo acym_select($data["allTags"], 'mails_tag', htmlspecialchars($data["tag"]), 'class="acym__templates__filter__tags"', 'value', 'name'); ?>
			</div>
			<div class="xlarge-1 medium-shrink"></div>
			<div class="xlarge-4 medium-auto cell text-center cell grid-x grid-margin-x text-right">
				<span class="acym__template__listing__create__text cell"><?php echo acym_translation('ACYM_CREATE_TEMPLATE') ?> : </span>
				<button type="button" data-task="edit" data-editor="acyEditor" class="acym__create__template button cell medium-auto margin-top-1">
                    <?php echo acym_translation('ACYM_DD_EDITOR'); ?>
				</button>
				<button type="button" data-task="edit" data-editor="html" class="acym__create__template button cell large-auto small-6 margin-top-1 button-secondary">
					HTML
				</button>
                <?php
                $linkImport = acym_completeLink('mails&task=import', true);
                $linkImport = 'data-iframe="'.$linkImport.'"';
                echo acym_modal(
                    acym_translation('ACYM_IMPORT'),
                    '<div class="text-center padding-0 cell grid-x text-center align-center">
								<input type="file" style="width:auto" name="uploadedfile" class="cell"/>
								<div class="cell">'.(acym_translation_sprintf('ACYM_MAX_UPLOAD', (acym_bytes(ini_get('upload_max_filesize')) > acym_bytes(ini_get('post_max_size'))) ? ini_get('post_max_size') : ini_get('upload_max_filesize'))).'</div>
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
							<a class="downloadmore" href="'.ACYM_ACYWEBSITE.'acymailing/templates-pack.html" target="_blank">'.acym_translation('ACYM_MORE_TEMPLATES').'</a>
						   <div class="cell grid-x align-center">
						   		<button type="button" data-task="doUploadTemplate" class="acy_button_submit button cell shrink margin-1">'.acym_translation('ACYM_IMPORT').'</button>
						   </div>',
                    null,
                    '',
                    'class="button acym__mails__listing__import-button cell medium-auto margin-top-1 button-secondary" data-reload="true" data-ajax="false"'
                );
                ?>
			</div>
            <?php if (empty($data['allMails'])) { ?>
				<h1 class="cell acym__listing__empty__search__title text-center"><?php echo acym_translation('ACYM_NO_RESULTS_FOUND'); ?></h1>
            <?php } else { ?>
				<div class="grid-x cell margin-top-1">
					<h1 class="shrink acym__title__listing margin-right-1"><?php echo acym_translation('ACYM_TEMPLATES') ?></h1>
					<div class="cell shrink acym_listing_sorty-by">
                        <?php echo acym_sortBy(
                            array(
                                'id' => strtolower(acym_translation('ACYM_ID')),
                                'creation_date' => acym_translation('ACYM_DATE_CREATED'),
                                'name' => acym_translation('ACYM_NAME'),
                                'type' => acym_translation('ACYM_TYPE'),
                            ),
                            'mails'
                        ) ?>
					</div>
				</div>
				<div class="grid-x cell">
					<div class="small-11 cell">
                        <?php
                        $options = array(
                            '' => ['ACYM_ALL', $data['mailNumberPerStatus']['all']],
                            'standard' => ['ACYM_STANDARD', $data['mailNumberPerStatus']['standard']],
                            'welcome' => ['ACYM_WELCOME_MAIL', $data['mailNumberPerStatus']['welcome']],
                            'unsubscribe' => ['ACYM_UNSUBSCRIBE_MAIL', $data['mailNumberPerStatus']['unsubscribe']],
                        );
                        echo acym_filterStatus($options, $data["status"], 'mails_status');
                        ?>
					</div>
				</div>
				<div class="grid-x grid-padding-x grid-padding-y grid-margin-x grid-margin-y xxlarge-up-6 large-up-4 medium-up-3 small-up-1 cell">
                    <?php
                    foreach ($data['allMails'] as $oneTemplate) {
                        ?>
						<div class="cell grid-x acym__templates__oneTpl acym__listing__block text-center" elementid="<?php echo htmlspecialchars($oneTemplate->id); ?>">
							<a href="<?php echo acym_completeLink('mails&task=edit&id='.htmlspecialchars($oneTemplate->id)); ?>" class="cell grid-x text-center">
								<div class="cell acym__templates__pic">
                                    <?php echo '<img src="'.htmlspecialchars(((strpos($oneTemplate->thumbnail, 'default_template_thumbnail') === false && strpos($oneTemplate->thumbnail, 'default_template') === false) ? ACYM_TEMPLATE_THUMBNAILS.$oneTemplate->thumbnail : $oneTemplate->thumbnail)).'" alt="'.htmlspecialchars($oneTemplate->name).'"/>'; ?>
								</div>
								<div class="cell grid-x acym__templates__footer text-center">
									<div class="cell acym__templates__footer__title" title="<?php echo htmlspecialchars($oneTemplate->name); ?>">
                                        <?php
                                        if (strlen($oneTemplate->name) > 55) {
                                            $oneTemplate->name = substr($oneTemplate->name, 0, 50).'...';
                                        }
                                        echo htmlspecialchars($oneTemplate->name);
                                        ?>
									</div>
									<div class="cell"><?php echo acym_date(htmlspecialchars($oneTemplate->creation_date), 'M. j, Y'); ?></div>
								</div>
							</a>
							<div class="text-center cell acym__listing__block__delete acym__background-color__red">
								<div>
									<i class='fa fa-trash-o acym__listing__block__delete__trash acym__color__white'></i>
									<p class="acym__listing__block__delete__cancel acym__background-color__very-dark-gray acym__color__white">
                                        <?php echo acym_translation("ACYM_CANCEL") ?>
									</p>
									<p class="acym__listing__block__delete__submit acym_toggle_delete acym__color__white" table="mail" elementid="<?php echo htmlspecialchars($oneTemplate->id); ?>"><?php echo acym_translation("ACYM_DELETE") ?></p>
								</div>
							</div>
						</div>
                    <?php } ?>
				</div>
				</div>
                <?php echo $data['pagination']->display('mails'); ?>
            <?php } ?>
        <?php } ?>
	</div>
    <?php echo acym_formOptions(false) ?>
</form>
