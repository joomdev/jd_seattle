<?php
/**
 * @package	AcyMailing for Joomla!
 * @version	5.10.3
 * @author	acyba.com
 * @copyright	(C) 2009-2018 ACYBA S.A.R.L. All rights reserved.
 * @license	GNU/GPLv3 http://www.gnu.org/licenses/gpl-3.0.html
 */

defined('_JEXEC') or die('Restricted access');
?><div id="acy_content">
	<form action="<?php echo acymailing_completeLink('template', true); ?>" method="post" name="adminForm" id="adminForm" autocomplete="off" enctype="multipart/form-data">
		<div id="iframedoc"></div>
		<div style="text-align:center;padding-top:20px;"><input type="file" style="width:auto" name="uploadedfile"/>
			<?php echo '<br />'.(acymailing_translation_sprintf('MAX_UPLOAD', (acymailing_bytes(ini_get('upload_max_filesize')) > acymailing_bytes(ini_get('post_max_size'))) ? ini_get('post_max_size') : ini_get('upload_max_filesize'))); ?></div>
		<br/><br/><a class="downloadmore" href="https://www.acyba.com/acymailing/templates-pack.html" target="_blank"><?php echo acymailing_translation('MORE_TEMPLATES'); ?></a>
		<?php acymailing_formOptions(); ?>
	</form>
</div>
