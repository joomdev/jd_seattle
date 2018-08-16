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
	<div id="iframedoc"></div>
	<form action="<?php echo acymailing_completeLink('file', true); ?>" method="post" name="adminForm" id="adminForm" autocomplete="off">
		<textarea style="width:98%;height:350px;" rows="20" name="csscontent"><?php echo $this->content; ?></textarea>

		<input type="hidden" name="file" value="<?php echo $this->type.'_'.$this->fileName; ?>"/>
		<input type="hidden" name="var" value="<?php echo acymailing_getVar('cmd', 'var'); ?>"/>
		<?php acymailing_formOptions(); ?>
	</form>
</div>
