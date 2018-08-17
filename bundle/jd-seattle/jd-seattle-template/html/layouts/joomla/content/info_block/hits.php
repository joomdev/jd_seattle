<?php
/**
 * @package     Joomla.Site
 * @subpackage  Layout
 *
 * @copyright   Copyright (C) 2005 - 2018 Open Source Matters, Inc. All rights reserved.
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 */

defined('JPATH_BASE') or die;

?>
<span class="date d-inline-block mb-2 mr-3">
	<i class="fas fa-gavel mr-1"></i>
	<meta itemprop="interactionCount" content="UserPageVisits:<?php echo $displayData['item']->hits; ?>" />
	<?php echo JText::sprintf('COM_CONTENT_ARTICLE_HITS', $displayData['item']->hits); ?>
</span>