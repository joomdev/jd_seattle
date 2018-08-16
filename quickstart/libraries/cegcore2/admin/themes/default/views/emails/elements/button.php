<?php
/* @copyright:ChronoEngine.com @license:GPLv2 */defined('_JEXEC') or die('Restricted access');
defined("GCORE_SITE") or die;
?>
<?php
	$td_css = [
		'font-family' => "'Arial',Helvetica Neue,Helvetica,sans-serif",
		'font-size' => '16px',
		'vertical-align' => 'top',
	];
	$td_css = $this->Fields->styles($td_css);
	
	$link_css = [
		'background-color' => $bgcolor,
		'border-radius' => '3px',
		'color' => $color,
		'display' => 'inline-block',
		'font-family' => "'Arial',Helvetica Neue,Helvetica,sans-serif",
		'font-size' => '16px',
		'line-height' => '19px',
		'padding' => $padding,
		'text-align' => 'center',
		'text-decoration' => 'none',
		'white-space' => 'nowrap',
	];
	$link_css = $this->Fields->styles($link_css);
?>
<center>
	<table border="0" cellspacing="0" cellpadding="0">
		<tbody>
			<tr>
				<td style="<?php echo $td_css; ?>" valign="top">
					<a href="<?php echo $element['params']['href']; ?>" style="<?php echo $link_css; ?>" bgcolor="<?php echo $bgcolor; ?>" align="center" target="_blank"><?php echo $element['params']['text']; ?></a>
				</td>
			</tr>
		</tbody>
	</table>
</center>