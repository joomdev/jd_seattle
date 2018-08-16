<?php
/**
 * @package	AcyMailing for Joomla!
 * @version	5.10.3
 * @author	acyba.com
 * @copyright	(C) 2009-2018 ACYBA S.A.R.L. All rights reserved.
 * @license	GNU/GPLv3 http://www.gnu.org/licenses/gpl-3.0.html
 */

defined('_JEXEC') or die('Restricted access');
?><div class="acymailing_module<?php echo $params->get('moduleclass_sfx') ?>" id="acymailing_module_<?php echo $formName; ?>">
	<?php
	if(!empty($mootoolsIntro)) echo '<p class="acymailing_mootoolsintro">'.$mootoolsIntro.'</p>'; ?>
	<div class="acymailing_mootoolsbutton">
		<?php
		$acypop = acymailing_get('helper.acypopup');
		$href = acymailing_completeLink('sub&task=display&autofocus=1&formid='.$module->id, true);

		$link = $acypop->display($mootoolsButton, '', $href, 'acymailing_togglemodule_'.$formName, $params->get('boxwidth', 250), $params->get('boxheight', 200), 'class="acymailing_togglemodule"', '', 'link');

		?>
		<p><?php echo $link; ?></p>
	</div>
</div>
