<?php
/**
 * @package     Joomla.Site
 * @subpackage  mod_articles_news
 *
 * @copyright   Copyright (C) 2005 - 2018 Open Source Matters, Inc. All rights reserved.
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 */

defined('_JEXEC') or die;
?>
<div class="newsflash">
	<div class="card-deck flex-column flex-lg-row">
		<?php foreach ($list as $item) : ?>
			<?php // echo "<pre>"; print_r($item);  echo "</pre>"; ?>
			<?php require JModuleHelper::getLayoutPath('mod_articles_news', '_item'); ?>
		<?php endforeach; ?>
	</div>
</div>
