<?php
/**
 * @package	AcyMailing for Joomla!
 * @version	5.10.3
 * @author	acyba.com
 * @copyright	(C) 2009-2018 ACYBA S.A.R.L. All rights reserved.
 * @license	GNU/GPLv3 http://www.gnu.org/licenses/gpl-3.0.html
 */

defined('_JEXEC') or die('Restricted access');
?><?php if($this->mail->html){ ?>
	<style type="text/css">
		.previewsize{
			background-image: url('<?php echo ACYMAILING_IMAGES?>preview_icons.png');
			background-repeat: no-repeat;
			cursor: pointer;
			height: 25px;
			display: block;
			float: left;
			margin-right: 5px;
		}

		.previewpict:hover, .previewpictenabled{
			background-position: -284px 0px;
		}

		.previewpict, .previewpictenabled:hover{
			background-position: -284px -33px;
		}

		.preview320{
			background-position: 0px 0px;
		}

		.preview320:hover, .preview320enabled{
			background-position: 0px -33px;
		}

		.preview480{
			background-position: -65px 0px;
		}

		.preview480:hover, .preview480enabled{
			background-position: -65px -33px;
		}

		.preview768{
			background-position: -136px 0px;
		}

		.preview768:hover, .preview768enabled{
			background-position: -136px -33px;
		}

		.previewmax{
			background-position: -211px 0px;
		}

		.previewmax:hover, .previewmaxenabled{
			background-position: -211px -33px;
		}
	</style>

	<div class="<?php echo acymailing_isAdmin() ? 'acyblockoptions' : 'onelineblockoptions'; ?> acyblock_newsletter" width="100%" id="htmlfieldset" style="clear:both;">
		<span class="acyblocktitle donotprint"> <?php echo acymailing_translation('HTML_VERSION'); ?></span>

		<div style="float:right;width:340px;clear:both" id="acypreview_resize">
			<span class="previewsize preview320" id="preview320" style="width:55px;" onclick="previewResize('342px','480px');previewSizeClick(this);"></span>
			<span class="previewsize preview480" id="preview480" style="width:61px;" onclick="previewResize('502px','320px');previewSizeClick(this);"></span>
			<span class="previewsize preview768" id="preview768" style="width:65px" onclick="previewResize('790px','1024px');previewSizeClick(this);"></span>
			<span class="previewsize previewmaxenabled" id="previewmax" style="width:63px;" onclick="previewResize('100%','100%');previewSizeClick(this);"></span>
			<span class="previewsize previewpictenabled" id="previewpict" style="width:46px;margin-left:20px;" onclick="switchPict();"></span>
		</div>

		<div class="newsletter_body" id="newsletter_preview_area"><?php echo $this->mail->body; ?></div>

	</div>
<?php
} ?>

<div class="<?php echo (acymailing_isAdmin() ? 'acyblockoptions' : 'onelineblockoptions'); ?> acyblock_newsletter donotprint" id="textfieldset">
	<span class="acyblocktitle donotprint"><?php echo acymailing_translation('TEXT_VERSION'); ?></span>
	<?php echo nl2br($this->escape($this->mail->altbody)); ?>
</div>
<?php
if(!empty($this->mail->attachments)){
	echo '<div class="'.(acymailing_isAdmin() ? 'acyblockoptions' : 'onelineblockoptions').' newsletter_attachments donotprint adminform">
		<span class="acyblocktitle">'.acymailing_translation('ATTACHMENTS').'</span>
		<table>';
	foreach($this->mail->attachments as $attachment){
		echo '<tr><td><a href="'.$attachment->url.'" target="_blank">'.$attachment->name.'</a></td></tr>';
	}
	echo '</table></div>';
}
?>

<div class="clr"></div>
