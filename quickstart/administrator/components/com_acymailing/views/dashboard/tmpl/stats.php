<?php
/**
 * @package	AcyMailing for Joomla!
 * @version	5.10.3
 * @author	acyba.com
 * @copyright	(C) 2009-2018 ACYBA S.A.R.L. All rights reserved.
 * @license	GNU/GPLv3 http://www.gnu.org/licenses/gpl-3.0.html
 */

defined('_JEXEC') or die('Restricted access');
?><div id="dashboard_mainstat">
	<div class="acydashboard_content">
		<div class="acycircles">
			<div class="circle stat_subscribers" onclick="displayDetails('userStatisticDetails');">

				<!-- circle animation 1 -->
				<div class="progressdiv" data-percent="<?php echo $this->userStats->confirmedPercent; ?>" data-title="<?php echo $this->userStats->total; ?>">
					<svg class="acyprogress" width="178" height="178" viewport="0 0 100 100" version="1.1" xmlns="http://www.w3.org/2000/svg">
						<circle r="80" cx="89" cy="89" fill="#fff" stroke-dasharray="502.4" stroke-dashoffset="0" stroke="#93bfeb"></circle>
						<circle class="bar" r="80" cx="89" cy="89" fill="transparent" stroke-dasharray="502.4" stroke-dashoffset="0"></circle>
					</svg>
				</div>
				<span class="circle_title"><?php echo acymailing_translation('ACY_DASHBOARD_USERS'); ?></span>
				<span class="circle_informations">
					<span class="stats_blue_point"></span> <?php echo acymailing_translation('ENABLED'); ?>
					<span class="stats_grey_point"></span> <?php echo acymailing_translation('DISABLED'); ?>
				</span>
				<br/>
				<button class="acymailing_button"><?php echo acymailing_translation_sprintf("ACY_MORE_USER_STATISTICS", acymailing_translation('USERS')) ?></button>
			</div>

			<div class="circle stat_lists" onclick="displayDetails('listStatisticDetails');">

				<!-- circle animation 2 -->
				<div class="progressdiv" data-percent="<?php echo $this->listStats->subscribedPercent; ?>" data-title="<?php echo $this->listStats->total; ?>">
					<svg class="acyprogress" width="178" height="178" viewport="0 0 100 100" version="1.1" xmlns="http://www.w3.org/2000/svg">
						<circle r="80" cx="89" cy="89" fill="#fff" stroke-dasharray="502.4" stroke-dashoffset="0" stroke="#c9c472"></circle>
						<circle class="bar" r="80" cx="89" cy="89" fill="transparent" stroke-dasharray="502.4" stroke-dashoffset="0"></circle>
					</svg>
				</div>
				<span class="circle_title"><?php echo acymailing_translation('ACY_DASHBOARD_LISTS'); ?></span>
				<span class="circle_informations">
					<span class="stats_green_point"></span> <?php echo acymailing_translation('ACY_ATLEASTONE'); ?>
					<span class="stats_grey_point"></span> <?php echo acymailing_translation('ACY_NOSUB'); ?>
				</span>
				<br/>
				<button class="acymailing_button"><?php echo acymailing_translation_sprintf("ACY_MORE_LIST_STATISTICS", acymailing_translation('LISTS')) ?></button>

			</div>
			<div class="circle stat_newsletters" onclick="displayDetails('newsletterStatisticDetails');">
				<!-- circle animation 3 -->
				<div class="progressdiv" data-percent="<?php echo $this->nlStats->publishedPercent; ?>" data-title="<?php echo $this->nlStats->total; ?>">
					<svg class="acyprogress" width="178" height="178" viewport="0 0 100 100" version="1.1" xmlns="http://www.w3.org/2000/svg">
						<circle r="80" cx="89" cy="89" fill="#fff" stroke-dasharray="502.4" stroke-dashoffset="0" stroke="#7c95ad"></circle>
						<circle class="bar" r="80" cx="89" cy="89" fill="transparent" stroke-dasharray="502.4" stroke-dashoffset="0"></circle>
					</svg>
				</div>
				<span class="circle_title"><?php echo acymailing_translation('ACY_DASHBOARD_NEWSLETTERS'); ?></span>
				<span class="circle_informations">
					<span class="stats_darkblue_point"></span> <?php echo acymailing_translation('ACY_PUBLISHED'); ?>
					<span class="stats_grey_point"></span> <?php echo acymailing_translation('ACY_UNPUBLISHED'); ?>
				</span>
				<br/>
				<button class="acymailing_button"><?php echo acymailing_translation_sprintf("ACY_MORE_NEWSLETTER_STATISTICS", acymailing_translation('NEWSLETTER')) ?></button>
			</div>
		</div>
		<div class="acygraph">
			<div id="userStatisticDetails" style="display: none;">
				<?php
				if(acymailing_isAllowed($this->config->get('acl_subscriber_manage', 'all'))){
					echo '<div id="userLocations">';
					include(dirname(__FILE__).DS.'userlocations.php');
					echo '</div>';
				}
				?>
				<?php
				if(acymailing_isAllowed($this->config->get('acl_subscriber_manage', 'all'))){
					echo '<div id="userStatsDiagram">';
					include(dirname(__FILE__).DS.'userstats.php');
					echo '</div>';
				}

				if(acymailing_isAllowed($this->config->get('acl_subscriber_manage', 'all'))){
					echo '<div id="recentUserListing">';
					include(dirname(__FILE__).DS.'users.php');
					echo '</div>';
				}
				?>
			</div>
			<div id="listStatisticDetails" style="display: none;">
				<?php
				if(acymailing_isAllowed($this->config->get('acl_lists_manage', 'all'))){
					echo '<div id="listStatsDiagram">';
					include(dirname(__FILE__).DS.'liststats.php');
					echo '</div>';
				}
				?>

			</div>
			<div id="newsletterStatisticDetails" style="display: none;">
				<?php
				if(acymailing_isAllowed($this->config->get('acl_queue_manage', 'all'))){
					echo '<div id="queueStatsDiagram">';
					include(dirname(__FILE__).DS.'queuestats.php');
					echo '</div>';
				}
				?>
			</div>
		</div>
	</div>
</div>


