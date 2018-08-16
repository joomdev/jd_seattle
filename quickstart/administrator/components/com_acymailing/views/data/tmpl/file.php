<?php
/**
 * @package	AcyMailing for Joomla!
 * @version	5.10.3
 * @author	acyba.com
 * @copyright	(C) 2009-2018 ACYBA S.A.R.L. All rights reserved.
 * @license	GNU/GPLv3 http://www.gnu.org/licenses/gpl-3.0.html
 */

defined('_JEXEC') or die('Restricted access');
?><table class="acymailing_table">
	<tr id="trfileupload">
		<td class="acykey">
			<?php echo acymailing_translation('UPLOAD_FILE'); ?>
		</td>
		<td>
			<input type="file" style="width:auto;" name="importfile"/>
			<?php echo '<br />'.(acymailing_translation_sprintf('MAX_UPLOAD', (acymailing_bytes(ini_get('upload_max_filesize')) > acymailing_bytes(ini_get('post_max_size'))) ? ini_get('post_max_size') : ini_get('upload_max_filesize'))); ?>
		</td>
	</tr>
</table>

