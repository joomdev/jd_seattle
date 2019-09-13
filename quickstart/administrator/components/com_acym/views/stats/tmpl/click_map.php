<?php
/**
 * @package	AcyMailing for Joomla
 * @version	6.2.2
 * @author	acyba.com
 * @copyright	(C) 2009-2019 ACYBA S.A.R.L. All rights reserved.
 * @license	GNU/GPLv3 http://www.gnu.org/licenses/gpl-3.0.html
 */

defined('_JEXEC') or die('Restricted access');
?><div id="acym__stats__click-map" class="acym__content">
	<input type="hidden" id="acym__stats_click__map__all-links__click" value=" <?php echo empty($data['url_click']) ? '' : $data['url_click']; ?>">
	<input type="hidden" id="acym__stats__css__file" value="<?php echo ACYM_CSS.'click_map.min.css?v='.filemtime(ACYM_MEDIA.'css'.DS.'click_map.min.css'); ?>">
	<div style="display: none" class="acym__hidden__mail__content"><?php echo acym_absoluteURL($data['mailInformation']->body); ?></div>
	<div style="display: none" class="acym__hidden__mail__stylesheet"><?php echo $data['mailInformation']->stylesheet; ?></div>
	<div class="cell grid-x">
		<div id="acym__wysid__email__preview" class="acym__email__preview grid-x cell margin-top-1"></div>
	</div>
</div>

