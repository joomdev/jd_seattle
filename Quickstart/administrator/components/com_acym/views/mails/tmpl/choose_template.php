<?php
/**
 * @package	AcyMailing for Joomla
 * @version	6.1.2
 * @author	acyba.com
 * @copyright	(C) 2009-2019 ACYBA S.A.R.L. All rights reserved.
 * @license	GNU/GPLv3 http://www.gnu.org/licenses/gpl-3.0.html
 */

defined('_JEXEC') or die('Restricted access');
?><div class="grid-x grid-margin-x">
    <?php if (empty($data['allMails']) && empty($data['search']) && empty($data['tag']) && empty($data['status']) && $data['type'] == 'standard') { ?>
		<div class="grid-x cell text-center">
			<h1 class="acym__listing__empty__title cell"><?php echo acym_translation('ACYM_YOU_DONT_HAVE_ANY_TEMPLATE'); ?></h1>
			<h1 class="acym__listing__empty__subtitle cell"><?php echo acym_translation('ACYM_CREATE_CAMPAIGN_EMPTY_TEMPLATE'); ?></h1>
			<div class="medium-4"></div>
			<div class="medium-4 small-12 cell">
				<a href="<?php echo acym_completeLink(acym_getVar('cmd', 'ctrl').'&task=edit&step=editEmail&from=-1&id='.htmlspecialchars($data['campaignID'])); ?>" class="button expanded" id="acym__templates__choose__create__empty">
                    <?php echo acym_translation('ACYM_CREATE_EMPTY_TEMPLATE'); ?>
				</a>
			</div>
			<div class="medium-4"></div>
		</div>
    <?php } else { ?>
	<div class="medium-auto cell">
        <?php echo acym_filterSearch(htmlspecialchars($data["search"]), 'mailchoose_search', 'ACYM_SEARCH_TEMPLATE'); ?>
	</div>

	<div class="medium-auto cell">
        <?php
        $allTags = new stdClass();
        $allTags->name = acym_translation('ACYM_ALL_TAGS');
        $allTags->value = '';
        array_unshift($data["allTags"], $allTags);

        echo acym_select($data["allTags"], 'mailchoose_tag', htmlspecialchars($data["tag"]), 'class="acym__templates__filter__tags"', 'value', 'name'); ?>
	</div>
	<div class="xxlarge-3 xlarge-2 large-1 hide-for-medium-only hide-for-small-only cell"></div>
	<div class="grid-x xlarge-auto large-shrink text-center cell acym__templates__choose__type-templates">
		<!-- Todo find a better way to pass the step in url when you choose a template in campaigns -->
        <?php
        $switchfilter = array('custom' => 'ACYM_MY_TEMPLATES', 'library' => 'ACYM_LIBRARY');
        echo acym_switchFilter($switchfilter, htmlspecialchars($data['type']), 'mailchoose_type');
        ?>
	</div>
	<div class="grid-x cell">
		<div class="cell medium-shrink acym__templates__choose__title hide-for-small-only">
			<b><?php echo acym_translation('ACYM_START_FROM'); ?></b>
		</div>

		<div class="cell medium-auto hide-for-small-only"></div>
		<div class="cell medium-shrink acym__templates__newTpl">
			<a href="<?php echo acym_completeLink(acym_getVar('cmd', 'ctrl').'&task=edit&step=editEmail&from=-1&type_editor=acyEditor&id='.htmlspecialchars($data['campaignID'])); ?>" class="button expanded" id="acym__templates__choose__create__empty">
                <?php echo acym_translation('ACYM_CREATE_EMPTY_TEMPLATE'); ?>
			</a>
		</div>
	</div>
    <?php if (empty($data['allMails'])) { ?>
		<h1 class="cell acym__listing__empty__search__title text-center"><?php echo acym_translation('ACYM_NO_RESULTS_FOUND'); ?></h1>
    <?php } else { ?>
	<div class="grid-x grid-padding-x grid-padding-y grid-margin-x grid-margin-y xxlarge-up-6 large-up-4 medium-up-3 small-up-1 cell">
        <?php
        foreach ($data['allMails'] as $oneTemplate) {
            ?>
			<div class="cell grid-x acym__templates__oneTpl acym__listing__block">
				<div class="cell acym__templates__pic text-center">
					<!-- Todo find a better way to pass the step in url when you choose a template in campaigns -->
					<a href="<?php echo acym_completeLink(acym_getVar('cmd', 'ctrl').'&task=edit&step=editEmail&from='.$oneTemplate->id.'&id='.htmlspecialchars($data['campaignID'])); ?>">
						<img src="<?php echo htmlspecialchars(((strpos($oneTemplate->thumbnail, 'default_template_thumbnail') === false && strpos($oneTemplate->thumbnail, 'default_template') === false) ? ACYM_TEMPLATE_THUMBNAILS.$oneTemplate->thumbnail : $oneTemplate->thumbnail)); ?>" alt="<?php echo htmlspecialchars($oneTemplate->name); ?>"/>
					</a>
                    <?php
                    if ($oneTemplate->drag_editor) {
                        echo '<div class="acym__templates__choose__ribbon ribbon">
                                    <div class="acym__templates__choose__ribbon__label acym__color__white acym__background-color__blue">AcyEditor</div>
                                </div>';
                    }
                    ?>
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
			</div>
        <?php } ?>
	</div>
</div>
<?php
echo $data['pagination']->display('mailchoose');
}
}

