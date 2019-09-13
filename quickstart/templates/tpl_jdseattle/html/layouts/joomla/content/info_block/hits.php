<?php
/**
 * @package   Astroid Framework
 * @author    JoomDev https://www.joomdev.com
 * @copyright Copyright (C) 2009 - 2019 JoomDev.
 * @license https://www.gnu.org/licenses/gpl-2.0.html GNU/GPLv2 or Later
 */

defined('JPATH_BASE') or die;

?>
	<dd class="hits">
		<i class="far fa-eye"></i>
		<meta itemprop="interactionCount" content="UserPageVisits:<?php echo $displayData['item']->hits; ?>" />
		<?php echo JText::sprintf('COM_CONTENT_ARTICLE_HITS', $displayData['item']->hits); ?>
	</dd>