<?php
/**
 * @package	AcyMailing for Joomla
 * @version	6.2.2
 * @author	acyba.com
 * @copyright	(C) 2009-2019 ACYBA S.A.R.L. All rights reserved.
 * @license	GNU/GPLv3 http://www.gnu.org/licenses/gpl-3.0.html
 */

defined('_JEXEC') or die('Restricted access');
?><div class="grid-x grid-margin-x acym__template__edit__choose-template__ajax">
	<input type="hidden" name="tag_template_choose" id="acym_tag_template_choose__ajax" value="">
	<input type="hidden" name="search_template_choose" id="acym_search_template_choose__ajax" value="">
	<div class="medium-auto cell">
        <?php echo acym_filterSearch('', 'mailchoose_search__ajax', 'ACYM_SEARCH_TEMPLATE'); ?>
	</div>

	<div class="medium-auto cell">
        <?php
        $allTags = new stdClass();
        $allTags->name = acym_translation('ACYM_ALL_TAGS');
        $allTags->value = '';
        array_unshift($data["allTags"], $allTags);
        echo acym_select($data["allTags"], 'mailchoose_tag__ajax', null, 'class="acym__templates__filter__tags__ajax"', 'value', 'name'); ?>
	</div>
	<div class="grid-x xlarge-auto large-shrink text-center cell acym__templates__choose__type-templates">
		<!-- Todo find a better way to pass the step in url when you choose a template in campaigns -->
        <?php
        $switchfilter = ['custom' => 'ACYM_MY_TEMPLATES', 'library' => 'ACYM_LIBRARY'];
        echo acym_switchFilter($switchfilter, 'custom', 'mailchoose_type');
        ?>
	</div>
	<div class="grid-x cell">
		<div class="cell medium-shrink acym__templates__choose__title hide-for-small-only">
			<b><?php echo acym_translation('ACYM_START_FROM'); ?></b>
		</div>

		<div class="cell medium-auto hide-for-small-only"></div>
	</div>
	<div class="acym__template__choose__ajax cell grid-x ">
	</div>
</div>

