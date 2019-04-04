<?php
/**
 * @package	AcyMailing for Joomla
 * @version	6.1.2
 * @author	acyba.com
 * @copyright	(C) 2009-2019 ACYBA S.A.R.L. All rights reserved.
 * @license	GNU/GPLv3 http://www.gnu.org/licenses/gpl-3.0.html
 */

defined('_JEXEC') or die('Restricted access');
?><div id="acyarchiveview">
	<h1 class="contentheading"><?php echo $data['mail']->subject; ?></h1>

	<div class="newsletter_body" style="min-width:80%" id="newsletter_preview_area"><?php echo $data['mail']->body; ?></div>

    <?php
    $attachments = json_decode($data['mail']->attachments);

    if (!empty($attachments)) {
        ?>
		<fieldset class="newsletter_attachments">
			<legend><?php echo acym_translation('ACYM_ATTACHMENTS'); ?></legend>
			<table>
                <?php
                foreach ($attachments as $attachment) {
                    $onlyFilename = explode("/", $attachment->filename);

                    $onlyFilename = end($onlyFilename);

                    echo '<tr><td><a href="'.acym_rootURI().$attachment->filename.'" target="_blank">'.$onlyFilename.'</a></td></tr>';
                }
                ?>
			</table>
		</fieldset>
    <?php } ?>
</div>
