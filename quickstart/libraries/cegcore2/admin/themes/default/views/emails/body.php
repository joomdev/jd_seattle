<?php
/* @copyright:ChronoEngine.com @license:GPLv2 */defined('_JEXEC') or die('Restricted access');
defined("GCORE_SITE") or die;
?>
<?php if(0): ?>
<div bgcolor="#f5f5f5" style="Margin:0;padding-top:0;padding-bottom:0;padding-right:0;padding-left:0;width:100%;direction:ltr;background-color:#f5f5f5" dir="ltr">
	<center style="width:100%;table-layout:fixed;background-color:#f5f5f5">
		<div style="max-width:600px;margin:0 auto;padding:0">
			<section>
				<table cellspacing="0" cellpadding="0" border="0" width="600" align="center" style="border-spacing:0;border:0;border-collapse:collapse;font-family:Roboto,Arial,sans-serif;color:#444444;Margin:0 auto;width:600px;max-width:600px">
					<tbody>
						<tr>
							<td align="center" width="600" style="padding-top:0;padding-bottom:0;text-align:left;font-size:0;max-width:600px">
<?php endif; ?>							
							<?php foreach($elements as $element): ?>
								<?php if(!empty($element['type']) AND empty($element['parent_id'])): ?>
									<?php echo $this->Parser2->parse($this->view('views.emails.element', ['element' => $element, 'elements' => $elements], true)); ?>
								<?php endif; ?>
							<?php endforeach; ?>
<?php if(0): ?>
							</td>
						</tr>
					</tbody>
				</table>
			</section>
		</div>
	</center>
</div>
<?php endif; ?>	