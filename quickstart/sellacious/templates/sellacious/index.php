<?php
/**
 * @version     1.6.1
 * @package     sellacious
 *
 * @copyright   Copyright (C) 2012-2018 Bhartiy Web Technologies. All rights reserved.
 * @license     SPL Sellacious Private License; see http://www.sellacious.com/spl.html
 * @author      Izhar Aazmi <info@bhartiy.com> - http://www.bhartiy.com
 */
// no direct access.
use Sellacious\Cache\CacheHelper;
use Sellacious\Language\LanguageHelper;

defined('_JEXEC') or die;

jimport('sellacious.loader');

if (class_exists('SellaciousHelper'))
{
	$helper = SellaciousHelper::getInstance();
}

$me       = JFactory::getUser();
$doc      = JFactory::getDocument();
$app      = JFactory::getApplication();
$sitename = $app->get('sitename');
$version  = md5(S_VERSION_CORE);

JFactory::getDocument()->addScriptOptions('sellacious.jarvis_site', $helper->core->getJarvisSite());
?>
<!DOCTYPE html>
<html xmlns="http://www.w3.org/1999/xhtml" xml:lang="<?php echo $this->language; ?>" lang="<?php echo $this->language; ?>" dir="<?php echo $this->direction; ?>">

	<head>
		<meta charset="utf-8">
		<meta http-equiv="X-UA-Compatible" content="IE=edge,chrome=1">
		<meta name="viewport" content="width=device-width, initial-scale=1.0, maximum-scale=1.0, user-scalable=no">

		<!-- favicon -->
		<?php
			$favicon = 'templates/sellacious/images/favicon/favicon.ico';

			if (isset($helper) && $helper->access->isSubscribed()):
				$altFavicon = $helper->config->getFaviconPremium();
				$favicon    = $altFavicon ? JUri::root() . $altFavicon : $favicon;
			endif;
		?>
		<link rel="shortcut icon" href="<?php echo $favicon ?>" type="image/x-icon" />
		<link rel="icon" href="<?php echo $favicon ?>" type="image/x-icon" />

		<?php
		JHtml::_('script', 'media/com_sellacious/js/plugin/messagebox/jquery.messagebox.min.js', array('version' => S_VERSION_CORE));
		JHtml::_('stylesheet', 'media/com_sellacious/js/plugin/messagebox/jquery.messagebox.css', null, false);

		$doc->addStyleSheet('//fonts.googleapis.com/css?family=Open+Sans:400italic,700italic,300,400,700');
		$doc->addStyleSheet('templates/sellacious/css/bootstrap.min.css', array('version' => $version));
		$doc->addStyleSheet('templates/sellacious/css/font-awesome.min.css', array('version' => $version));
		$doc->addStyleSheet('templates/sellacious/css/joomla-icons.css', array('version' => $version));

		$doc->addStyleSheet('templates/sellacious/css/jquery.mCustomScrollbar.min.css', array('version' => $version));
		$doc->addStyleSheet('templates/sellacious/css/smartadmin-production.css', array('version' => $version));
		$doc->addStyleSheet('templates/sellacious/css/smartadmin-skins.css', array('version' => $version));
		$doc->addStyleSheet('templates/sellacious/css/custom-style.css', array('version' => $version));

		if ($this->direction == 'rtl')
		{
			$doc->addStyleSheet('templates/sellacious/css/smartadmin-rtl.css', array('version' => $version));
		}

		JHtml::_('jquery.framework');
		JHtml::_('jquery.ui');
		JHtml::_('bootstrap.tooltip');

		$doc->addScript('templates/sellacious/js/jquery.mCustomScrollbar.js', array('version' => $version));                       // mCustomScrollbar
		$doc->addScript('templates/sellacious/js/plugin/fastclick/fastclick.js', array('version' => $version));                    // FastClick: For mobile devices
		$doc->addScript('templates/sellacious/js/plugin/jquery-touch/jquery.ui.touch-punch.min.js', array('version' => $version)); // JS TOUCH plugin for mobile drag-drop touch events
		$doc->addScript('templates/sellacious/js/plugin/msie-fix/jquery.mb.browser.min.js', array('version' => $version));         // browser msie issue fix
		$doc->addScript('templates/sellacious/js/notification/SmartNotification.min.js', array('version' => $version));            // Custom notification
		$doc->addScript('templates/sellacious/js/plugin/cookie/jquery.cookie.min.js', array('version' => $version));               // cookie
		$doc->addScript('templates/sellacious/js/sellacious-core.js', array('version' => $version));                               // Sellacious core functions to work template wide
		$doc->addScript('templates/sellacious/js/sellacious-notifier.js', array('version' => $version));                           // Sellacious notification per view page
		?>

		<script data-pace-options='{"restartOnRequestAfter": true}' src="templates/sellacious/js/plugin/pace/pace.min.js"></script>

		<jdoc:include type="head"/>

		<!--[if IE 7]>
		<h1>Your browser is out of date, please update your browser by going to www.microsoft.com/download</h1>
		<![endif]-->
	</head>

	<?php $collapse = $app->input->cookie->get('collapsedmenu'); ?>
	<body class="fixed-page-footer <?php echo $app->input->get('hidemainmenu') || $collapse ? 'minified' : '' ?>"><!--
	 The possible classes: smart-style-3, minified, fixed-ribbon, fixed-header, fixed-width -->

		<!-- HEADER -->
		<header class="header btn-group-justified">

		</header>
		<!-- END HEADER -->

		<!-- Left panel : Navigation area -->
		<?php if ($this->countModules('left-panel') || $this->countModules('menu')) { ?>
		<!-- Note: This width of the aside area can be adjusted through LESS variables -->

		<aside id="left-panel">
			<div id="logo-group">
				<?php
				$logo     = 'templates/sellacious/images/logo.png';
				$logoIcon = 'templates/sellacious/images/logo-icon.png';

				if (isset($helper) && $helper->access->isSubscribed()):
					$altLogo = $helper->media->getImage('config.backoffice_logo', 1, false);
					$logo    = $altLogo ?: $logo;

					$altLogoIcon = $helper->media->getImage('config.backoffice_logoicon', 1, false);
					$logoIcon    = $altLogoIcon ?: $logoIcon;
				endif;
				?>
				<span id="logo"><a class="pull-left" href="<?php echo JRoute::_('index.php') ?>">
					<img class="logo1x" src="<?php echo $logo ?>" alt="<?php echo htmlspecialchars($sitename) ?>">
						<img class="logo-icon" src="<?php echo $logoIcon ?>" alt="<?php echo htmlspecialchars($sitename) ?>">
					</a></span>

				<?php if ($this->countModules('logo-group')) { ?>
					<!-- OPTIMAL PLACE FOR NOTIFICATION MODULE -->
					<jdoc:include type="modules" name="logo-group" style="none"/>
				<?php } ?>
			</div>

			<!-- User info -->
			<?php if ($this->countModules('left-panel')) { ?>
				<jdoc:include type="modules" name="left-panel" style="none"/>
			<?php } ?>
			<!-- end user info -->

			<!-- NAVIGATION : This navigation is also responsive
			To make this navigation dynamic please make sure to link the node
			(the reference to the nav > ul) after page load. Or the navigation will not initialize.
			-->

			<!-- User info -->
			<?php if ($this->countModules('menu')) { ?>
				<jdoc:include type="modules" name="menu" style="none"/>
			<?php } ?>
			<!-- end user info -->

			<div class="side-info">

				<!-- Cache & Sync button -->
				<div class="sync-media">
					<?php if (!CacheHelper::isRunning()): ?>
						<a href="javascript:void(0)" class="btn" data-action="rebuild-cache"
							data-token="<?php echo JSession::getFormToken(); ?>">
								<i class="fa fa-refresh"></i> <span class="unmini-text">Cache</span></a>
					<?php else: ?>
						<a href="javascript:void(0)" class="btn btn-disabled bg-color-white txt-color-red" data-action="rebuild-cache" data-state="1"
						   data-token="<?php echo JSession::getFormToken(); ?>">
							<i class="fa fa-refresh fa-spin"></i> <span class="unmini-text">Cache</span></a>
					<?php endif; ?>

					<a href="javascript:void(0)" class="btn" data-action="system-autofix"
					   data-token="<?php echo JSession::getFormToken(); ?>"><i class="fa fa-wrench" aria-hidden="true"></i> <span class="unmini-text">Auto Fix</span></a>
				</div>
				<!-- end Cache & Sync button -->

				<div class="side-items">
					<?php if (isset($helper) && $helper->config->get('show_doc_link', 1) || !$helper->access->isSubscribed()): ?>
						<a href="https://www.sellacious.com/documentation-v2" target="_blank" title=<?php echo JText::_('TPL_SELLACIOUS_DOCUMENT_TITLE'); ?> class="primary">
							<i class="fa fa-book"></i> <span class="unmini-text"><?php echo JText::_('TPL_SELLACIOUS_DOCUMENTATION'); ?></span></a>
					<?php endif; ?>
					<?php if (isset($helper) && $helper->config->get('show_support_link', 1) || !$helper->access->isSubscribed()): ?>
						<?php if ($me->authorise('core.admin')): ?>
						<a data-action="support" data-href="/community-support" style="cursor: pointer;" target="_blank" title="Forum">
							<i class="fa fa-phone"></i> <span class="unmini-text"><?php echo JText::_('TPL_SELLACIOUS_SUPPORT'); ?></span></a>
						<?php else: ?>
						<a href="https://www.sellacious.com/community-support" target="_blank" title="Forum">
							<i class="fa fa-phone"></i> <span class="unmini-text"><?php echo JText::_('TPL_SELLACIOUS_SUPPORT'); ?></span></a>
						<?php endif; ?>
					<?php endif; ?>
				</div>
			</div>

		</aside>
		<!-- END NAVIGATION -->
		<?php } ?>
		<!-- End Left panel : Navigation area -->

		<!-- MAIN PANEL -->
		<div id="main" role="main">

			<!-- RIBBON -->
			<div id="ribbon">
				<span class="minifyme"> <i class="fa fa-arrow-circle-left hit"></i> </span>
				<!-- Hide Menu button -->
				<div id="hide-menu" class="btn-header transparent pull-left cursor-pointer">
					<a href="javascript:void(0)" class="hasTooltip" data-placement="bottom" data-menu="hidemenu"
					   title="Menu"><i class="fa fa-reorder"></i></a>
				</div>
				<!-- end Hide Menu button -->

				<!-- breadcrumb -->
				<?php if ($this->countModules('ribbon-left')) { ?>
					<jdoc:include type="modules" name="ribbon-left" style="none"/>
				<?php } ?>
				<!-- end breadcrumb -->

				<?php if ($this->countModules('ribbon-right')) { ?>
				<span class="ribbon-button-alignment pull-right">
					<jdoc:include type="modules" name="ribbon-right" style="none"/>
				</span>
				<?php } ?>
				<div class="btn-top-actions pull-right">
					<!-- User button -->
					<div class="btn-header transparent pull-right cursor-pointer dropdown">
						<a href="javascript:void(0)" class="dropdown-toggle hasTooltip" data-toggle="dropdown" data-placement="bottom"
							title="User" id="userDropdown" aria-haspopup="true" aria-expanded="false"><i class="fa fa-user"></i></a>
						<div class="dropdown-menu" aria-labelledby="userDropdown">
							<h4>Welcome, <?php echo $me->get('name'); ?></h4>
							<a id="show-shortcut" data-action="toggleShortcut" href="index.php?option=com_sellacious&view=profile">
								<i class="fa fa-pencil" aria-hidden="true"></i> Edit Profile</a>
							<!-- logout button -->
							<?php $logout_url = 'index.php?option=com_login&task=logout&' . JSession::getFormToken() . '=1'; ?>
							<a href="<?php echo $logout_url ?>" data-action="userLogout"
								data-logout-msg="You can improve your security further after logging out by closing this opened browser">
								<i class="fa fa-sign-out"></i> Sign Out</a>
							<!-- end logout button -->
						</div>
					</div>

					<!-- end User button -->

					<!-- fullscreen button -->
					<div id="fullscreen" class="btn-header transparent pull-right">
						<a href="javascript:void(0);" data-action="launchFullscreen" class="hasTooltip" data-placement="bottom"
							title="Full Screen"><i class="fa fa-arrows-alt"></i></a>
					</div>
					<!-- end fullscreen button -->

					<!-- back to Joomla administrator button -->
					<?php if (isset($helper) && $helper->config->get('show_back_to_joomla', 1) || !$helper->access->isSubscribed()) : ?>
					<div id="joomla-admin" class="btn-header transparent pull-right cursor-pointer">
						<a href="../<?php echo basename(JPATH_ADMINISTRATOR); ?>/index.php" class="hasTooltip" data-placement="bottom"
							title="Back to Joomla Administrator"><i class="fa fa-joomla"></i></a>
					</div>
					<?php endif; ?>
					<!-- end back to Joomla administrator button -->

					<!-- Go to Joomla frontend button -->
					<div id="joomla" class="btn-header transparent pull-right cursor-pointer">
						<a href="../index.php" target="_blank" class="hasTooltip" data-placement="bottom"
							title="View Site"><i class="fa fa-external-link"></i></a>
					</div>
					<!-- end Go to Joomla frontend button -->

					<!-- Language Switcher button -->
					<?php $languageC = JFactory::getLanguage(); ?>
					<div id="lang-switch" class="btn-header transparent pull-right cursor-pointer dropdown">
						<a href="javascript:void(0)" class="dropdown-toggle hasTooltip" data-toggle="dropdown" data-placement="left"
						   title="<?php echo $languageC->get('nativeName', $languageC->getName()) ?>"
						   id="langSelect" aria-haspopup="true" aria-expanded="false">
							<?php
							$tag      = $languageC->getTag();
							$flag     = str_replace('-', '_', strtolower($tag));
							$filename = 'media/mod_languages/images/' . $flag . '.gif';

							if (is_file(JPATH_ROOT . '/' . $filename))
							{
								echo '<img src="' . JUri::root() . $filename . '">';
							}
							else
							{
								echo $tag;
							}
							?>
						</a>
						<?php $languages = LanguageHelper::createLanguageList(null, null, JPATH_BASE); ?>

						<?php if (count($languages) > 1): ?>
							<ul class="dropdown-menu lang-dropdown" aria-labelledby="langSelect">
							<?php foreach ($languages as $language): ?>
								<?php
								if ($language['value'] == $tag)
								{
									continue;
								}

								$flag     = str_replace('-', '_', strtolower($language['value']));
								$filename = 'media/mod_languages/images/' . $flag . '.gif';

								if (is_file(JPATH_ROOT . '/' . $filename))
								{
									$langIcon = '<img src="' . JUri::root() . $filename . '">';
								}
								?>
								<li><a class="hasTooltip" data-placement="left" data-action="switchLanguage" href="#" data-lang="<?php
									echo $language['value'] ?>" title="<?php echo $language['text']; ?>"><?php echo $langIcon; ?></a></li>
							<?php endforeach; ?>
							</ul>
						<?php endif; ?>
					</div>
					<!-- end Language Switcher button -->

					<!-- Begin Support PIN button -->
					<?php if ($me->authorise('core.admin')): ?>
					<div class="btn-header transparent pull-right cursor-pointer">
						<?php $sPin = $app->getUserState('application.sellacious.support.pin'); ?>
						<?php if ($sPin): ?>
							<a data-action="support-pin" class="hasTooltip hasPin"
							   data-placement="left" title="<?php echo JText::_('TPL_SELLACIOUS_SUPPORT_PIN', true); ?>"
							   style="padding: 0 10px; width: auto;"><i class="fa fa-support"></i><span><?php echo $sPin ?></span></a>
						<?php else: ?>
							<a data-action="support-pin" class="hasTooltip"
							   data-placement="left" title="<?php echo JText::_('TPL_SELLACIOUS_SUPPORT_PIN', true); ?>"
								style="padding: 0 10px; width: auto;"><i class="fa fa-support"></i><span><?php
									echo JText::_('TPL_SELLACIOUS_SUPPORT_PIN_GENERATE'); ?></span></a>
						<?php endif; ?>
					</div>
					<?php endif; ?>
					<!-- end Support PIN button -->

					<?php if ($this->countModules('header-right')): ?>
						<jdoc:include type="modules" name="header-right" style="none"/>
					<?php endif; ?>
				</div>

			</div>
			<!-- END RIBBON -->

			<?php if ($this->countModules('toolbar') || $this->countModules('title')) : ?>
				<div class="box-toolbar">
					<div class="">
						<!-- col -->
						<div class="pull-left">
							<!-- PAGE HEADER -->
							<jdoc:include type="modules" name="title"/>
						</div>
						<!-- end col -->

						<!-- right side of the page with the sparkline graphs -->
						<!-- col -->
						<div class="pull-right">
							<?php if ($this->countModules('toolbar')) : ?>
								<span class="pull-right">
									<jdoc:include type="modules" name="toolbar" style="none"/>
								</span>
							<?php endif; ?>
						</div>
						<!-- end col -->
					</div>
				</div>
			<?php endif; ?>

			<?php if ($this->countModules('top')) : ?>
				<div class="row">
					<div class="col-sm-12">
						<jdoc:include type="modules" name="content-top" style="xhtml"/>
					</div>
				</div>
			<?php endif; ?>

			<?php if ($this->countModules('submenu')) : ?>
				<div class="row">
					<div class="col-sm-12">
						<jdoc:include type="modules" name="submenu" style="none"/>
					</div>
				</div>
			<?php endif; ?>

			<div class="clearfix"></div>

			<!-- MAIN CONTENT -->
			<div id="content">

				<?php if ($this->countModules('content-top')) { ?>
					<div class="row">
						<jdoc:include type="modules" name="content-top" style="none"/>
					</div>
				<?php } ?>

				<div class="clearfix"></div>

				<div class="component content-wrap">

					<div id="system-message-container"><jdoc:include type="message" style="xhtml"/></div>
					<div class="clearfix"></div>

					<jdoc:include type="component" style="xhtml"/>
					<div class="clearfix"></div>
				</div>

				<?php if ($this->countModules('content-bottom')) { ?>
					<div class="row">
						<jdoc:include type="modules" name="content-bottom" style="none"/>
					</div>
				<?php } ?>

			</div>

			<div class="clearfix"></div>

			<!-- END MAIN CONTENT -->

		</div>
		<!-- END MAIN PANEL -->

	<?php if ($this->countModules('footer')) { ?>
		<jdoc:include type="modules" name="footer" style="none"/>
	<?php } ?>

		<jdoc:include type="modules" name="dynamic" style="xhtml"/>

		<!-- Google Analytics code below -->
		<?php if ($ga_code = $this->params->get('ga_code')) { ?>
			<script type="text/javascript">
				var _gaq = _gaq || [];
				_gaq.push(['_setAccount', '<?php echo htmlspecialchars($ga_code) ?>']);
				_gaq.push(['_trackPageview']);

				(function() {
					var ga = document.createElement('script');
					ga.type = 'text/javascript';
					ga.async = true;
					ga.src = ('https:' === document.location.protocol ? 'https://ssl' : 'http://www') + '.google-analytics.com/ga.js';
					var s = document.getElementsByTagName('script')[0];
					s.parentNode.insertBefore(ga, s);
				})();
			</script>
		<?php } ?>

	</body>

</html>
