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
defined('_JEXEC') or die;

jimport('sellacious.loader');

if (class_exists('SellaciousHelper'))
{
	$helper = SellaciousHelper::getInstance();
}

$app      = JFactory::getApplication();
$doc      = JFactory::getDocument();
$sitename = $app->get('sitename');

$doc->addStyleSheet('http://fonts.googleapis.com/css?family=Open+Sans:400italic,700italic,300,400,700');

$doc->addStyleSheet('templates/sellacious/css/bootstrap.min.css', 'text/css', 'screen');
$doc->addStyleSheet('templates/sellacious/css/font-awesome.min.css', 'text/css', 'screen');

// SmartAdmin Styles - Please note (smartadmin-production.css) was created using LESS variables
$doc->addStyleSheet('templates/sellacious/css/smartadmin-production.css', 'text/css', 'screen');
$doc->addStyleSheet('templates/sellacious/css/smartadmin-skins.css', 'text/css', 'screen');
$doc->addStyleSheet('templates/sellacious/css/login.css', 'text/css', 'screen');

// SmartAdmin RTL Support is under construction
// $doc->addStyleSheet('templates/sellacious/css/smartadmin-rtl.css', 'text/css', 'screen');

// Demo purpose only: goes with demo.js, you can delete this css when designing your own WebApp
// $doc->addStyleSheet('templates/sellacious/css/demo.css', 'text/css', 'screen');

// PACE LOADER - turn this on if you want ajax loading to show (caution: uses lots of memory on iDevices)
$doc->addScript('templates/sellacious/js/plugin/pace/pace.min.js');

// Google CDN's jQuery + jQueryUI - fall back to local
// $doc->addScript('//ajax.googleapis.com/ajax/libs/jquery/2.0.2/jquery.min.js');
// $doc->addScript('//ajax.googleapis.com/ajax/libs/jqueryui/1.10.3/jquery-ui.min.js');

$doc->addScript('templates/sellacious/js/libs/jquery-2.0.2.min.js');
$doc->addScript('templates/sellacious/js/libs/jquery-ui-1.10.3.min.js');

$doc->addScript('templates/sellacious/js/plugin/jquery-touch/jquery.ui.touch-punch.min.js');    // JS TOUCH plugin for mobile drag-drop touch events
$doc->addScript('templates/sellacious/js/bootstrap/bootstrap.min.js');                            // BOOTSTRAP JS
$doc->addScript('templates/sellacious/js/notification/SmartNotification.min.js');                // CUSTOM NOTIFICATION
$doc->addScript('templates/sellacious/js/smartwidgets/jarvis.widget.min.js');                    // JARVIS WIDGETS
$doc->addScript('templates/sellacious/js/plugin/easy-pie-chart/jquery.easy-pie-chart.min.js');    // EASY PIE CHARTS
$doc->addScript('templates/sellacious/js/plugin/sparkline/jquery.sparkline.min.js');            // SPARKLINES
$doc->addScript('templates/sellacious/js/plugin/jquery-validate/jquery.validate.min.js');        // JQUERY VALIDATE
$doc->addScript('templates/sellacious/js/plugin/masked-input/jquery.maskedinput.min.js');        // JQUERY MASKED INPUT
$doc->addScript('templates/sellacious/js/plugin/select2/select2.min.js');                        // JQUERY SELECT2 INPUT
$doc->addScript('templates/sellacious/js/plugin/bootstrap-slider/bootstrap-slider.min.js');        // JQUERY UI + Bootstrap Slider
$doc->addScript('templates/sellacious/js/plugin/msie-fix/jquery.mb.browser.min.js');            // browser msie issue fix
$doc->addScript('templates/sellacious/js/plugin/fastclick/fastclick.js');                        // FastClick: For mobile devices
$doc->addScript('templates/sellacious/js/demo.js');                                                // MAIN APP JS FILE
$doc->addScript('templates/sellacious/js/app.js');                                                // MAIN APP JS FILE
?>
<!DOCTYPE html>
<html xmlns="http://www.w3.org/1999/xhtml" xml:lang="<?php echo $this->language; ?>" lang="<?php echo $this->language; ?>" dir="<?php echo $this->direction; ?>">

<head>

	<meta charset="utf-8">

	<meta http-equiv="X-UA-Compatible" content="IE=edge,chrome=1">
	<meta name="viewport" content="width=device-width, initial-scale=1.0">

	<!-- favicons -->
	<?php
	$favicon = 'templates/sellacious/images/favicon/favicon.ico';

	if (isset($helper) && $helper->access->isSubscribed()):
		$altFavicon = $helper->config->getFaviconPremium();
		$favicon    = $altFavicon ? JUri::root() . $altFavicon : $favicon;
	endif;
	?>
	<link rel="shortcut icon" href="<?php echo $favicon ?>" type="image/x-icon" />
	<link rel="icon" href="<?php echo $favicon ?>" type="image/x-icon" />

	<jdoc:include type="head" />

	<!--[if IE 7]>
	<h1>Your browser is out of date, please update your browser by going to www.microsoft.com/download</h1>
	<![endif]-->

</head>

<body id="login" class="animated fadeInDown">

<header id="header">
	<div id="logo-group">
		<?php
		$logo = 'templates/sellacious/images/logo.png';

		if (isset($helper) && $helper->access->isSubscribed()):
			$altLogo = $helper->media->getImage('config.backoffice_logo', 1, false);
			$logo    = $altLogo ?: $logo;
		endif;
		?>
		<span id="logo"> <img src="<?php echo $logo ?>" alt="<?php echo htmlspecialchars($sitename) ?>"> </span>
	</div>
<!--<span id="login-header-space">
		<span class="hidden-mobile"><?php /*echo JText::_('COM_LOGIN_DONT_HAVE_AN_ACCOUNT') */?></span>
		<a href="register.html" class="btn btn-danger"><?php /*echo JText::_('COM_LOGIN_CREATE_ACCOUNT') */?></a>
	</span>-->
</header>

<div id="main" role="main">

	<noscript>
		<?php echo JText::_('JGLOBAL_WARNJAVASCRIPT') ?>
	</noscript>

	<!-- MAIN CONTENT -->
	<div id="content" class="container">
		<!-- Begin Content -->

		<div class="row">
			<?php
			if ($this->countModules('login-left'))
			{
				if ($this->countModules('login-right'))
				{
					?><div class="col-xs-12 col-sm-12 col-md-4 col-lg-4">
						<jdoc:include type="modules" name="login-left" style="none" />
					</div><?php
				}
				else
				{
					?><div class="col-xs-12 col-sm-12 col-md-8 col-lg-8">
						<jdoc:include type="modules" name="login-left" style="none" />
					</div><?php
				}
			}
			elseif (!$this->countModules('login-right'))
			{
				?><div class="col-xs-12 col-sm-12 col-md-4 col-lg-4"></div><?php
			}
			?>
			<div class="col-xs-12 col-sm-12 col-md-4 col-lg-4">
				<jdoc:include type="message" />
				<div class="clearfix"></div>
				<jdoc:include type="component" />
			</div>
			<?php
			if ($this->countModules('login-right'))
			{
				if ($this->countModules('login-left'))
				{
					?><div class="col-xs-12 col-sm-12 col-md-4 col-lg-4">
						<jdoc:include type="modules" name="login-right" style="none" />
					</div><?php
				}
				else
				{
					?><div class="col-xs-12 col-sm-12 col-md-8 col-lg-8">
						<jdoc:include type="modules" name="login-right" style="none" />
					</div><?php
				}
				?>
				<?php
			}
			elseif (!$this->countModules('login-left'))
			{
				?><div class="col-xs-12 col-sm-12 col-md-4 col-lg-4"></div><?php
			}
			?>
		</div>

		<!-- End Content -->
	</div>

</div>

<div class="navbar navbar-fixed-bottom">
	<div class="row text-center">
		<div class="pull-left col-xs-12 col-sm-12 col-md-4 col-lg-2">&nbsp;&nbsp;&nbsp;&nbsp;<a href="<?php echo JUri::root(); ?>" target="_blank"><i class="icon-share icon-white"></i><?php echo JText::_('COM_LOGIN_RETURN_TO_SITE_HOME_PAGE') ?>
			</a></div>
		<div class="pull-right col-xs-12 col-sm-12 col-md-4 col-lg-3">&copy; 2011-<?php echo date('Y'); ?> <?php echo $sitename; ?>&nbsp;&nbsp;&nbsp;&nbsp;</div>
	</div>
</div>

<jdoc:include type="modules" name="debug" style="none" />

</body>

</html>
