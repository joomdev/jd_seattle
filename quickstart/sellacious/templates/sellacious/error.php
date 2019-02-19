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
?>
<!DOCTYPE html>
<html xmlns="http://www.w3.org/1999/xhtml" xml:lang="<?php echo $this->language; ?>" lang="<?php echo $this->language; ?>" dir="<?php echo $this->direction; ?>">

<head>
	<?php $sitename = JFactory::getApplication()->get('sitename'); ?>

	<title>Error - <?php echo htmlspecialchars($sitename); ?></title>

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

	<link rel="stylesheet" href="templates/sellacious/css/bootstrap.min.css" type="text/css"/>
	<link rel="stylesheet" href="templates/sellacious/css/font-awesome.min.css" type="text/css"/>
	<link rel="stylesheet" href="templates/sellacious/css/smartadmin-production.css" type="text/css"/>
	<link rel="stylesheet" href="templates/sellacious/css/smartadmin-skins.css" type="text/css"/>
	<link rel="stylesheet" href="templates/sellacious/css/login.css" type="text/css"/>

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
</header>

<div id="main" role="main">
	<noscript>
		<?php echo JText::_('JGLOBAL_WARNJAVASCRIPT') ?>
	</noscript>
	<!-- MAIN CONTENT -->
	<div id="content">
		<!-- row -->
		<div class="row">
			<div class="col-xs-12 col-sm-12 col-md-12 col-lg-12">
				<div class="row">
					<div class="col-sm-12">
						<div class="text-center error-box">
							<h1 class="error-text tada animated"><i class="fa fa-times-circle text-danger error-icon-shadow"></i> Error <?php $this->error->getCode() ?></h1>
							<h2 class="font-xl"><strong>Oooops, Something went wrong!</strong></h2>
							<br />
							<p class="lead semi-bold">
								<strong><?php echo htmlspecialchars($this->error->getMessage(), ENT_QUOTES, 'UTF-8'); ?></strong><br><br>
								<small>
									<?php $user = JFactory::getUser(); ?>
									<?php if (is_file(JPATH_ROOT . '/izdebug.php')): ?>
										If you believe this is a bug, please contact sellacious support team.
									<?php else: ?>
										If you believe this is a bug, please contact the site administrator.
									<?php endif; ?>
								</small>
							</p>

							<?php
							if (is_file(JPATH_ROOT . '/izdebug.php'))
							{
								/** @var  Exception $error */
								$error = $this->error;
								?><pre class="text-left"><code><strong>File: </strong><?php echo $error->getFile(); ?> at line <?php echo $error->getLine(); ?></code></pre><?php
								?><pre class="text-left"><code><?php echo $this->renderBacktrace(); ?></code></pre><?php
							}
							?>

							<div class="clearfix"></div>
							<ul class="error-search text-center font-md" style="list-style: none;">
								<li><a href="index.php"><small>Go to My Dashboard <i class="fa fa-arrow-right"></i></small></a></li>
							</ul>
						</div>
					</div>
				</div>
			</div>
		</div>
		<!-- end row -->
	</div>
</div>

</body>

</html>
