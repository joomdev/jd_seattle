<?php
// No direct access
defined('_JEXEC') or die;
$skillsets = $params->get('skillsets', []);
$numberPosition = $params->get('numberPosition','above');
$symbolPosition = $params->get('symbolPosition','default');
$numberColor = $params->get('numberColor','');

$titleColor = $params->get('titleColor','');
$numberColor = $params->get('numberColor','');
$symbolColor = $params->get('symbolColor','');
$iconColor = $params->get('iconColor',''); 

$titleSize = $params->get('titleSize',20);
$numberSize = $params->get('numberSize',40);
$symbolSize = $params->get('symbolSize',40);
$iconSize = $params->get('iconSize',52);

$customsColor = $params->get('customsColor');
$customsSize = $params->get('customsSize');
$i=0; foreach($skillsets as $skillset){$i++;}
if($i==1){$count=12;}elseif($i==2){$count=6;}elseif($i==3){$count=4;}elseif($i==4){$count=3;}
?>
<style>
/* Skill Counter
=========================== */
.counter-sub-container .counter-wrapper .counter-icon {
	font-size: 52px;
	line-height: 1;
}
.counter-sub-container .counter-wrapper .counter-text-container .counter-title {
	margin: 0 0 5px;
}
.counter-sub-container .counter-wrapper .counter-text-container .counter-number {
	font-size: 40px;
	line-height: 1;
	margin: 0;
}
  <?php if($customsSize=="true") {?>
	 #skillset-<?php echo $module->id; ?> .counter-title{
		font-size:<?php echo $titleSize; ?>px;
	 } 
	  #skillset-<?php echo $module->id; ?> .counter-number .count{
		font-size:<?php echo $numberSize; ?>px;
	 }
	 #skillset-<?php echo $module->id; ?> .counter-number .symbol{
		font-size:<?php echo $symbolSize; ?>px;
	 }
	 #skillset-<?php echo $module->id; ?> .icon{
		font-size:<?php echo $iconSize; ?>px;
	 }
  <?php } ?>
  
  <?php if($customsColor=="true") {?>
	 #skillset-<?php echo $module->id; ?> .counter-title{
		color:<?php echo $titleColor; ?>;
	 } 
	  #skillset-<?php echo $module->id; ?> .counter-number .count{
		color:<?php echo $numberColor; ?>;
	 }   
	 #skillset-<?php echo $module->id; ?> .counter-number .symbol{
		color:<?php echo $symbolColor; ?>;
	 }
	 #skillset-<?php echo $module->id; ?> .icon{
		color:<?php echo $iconColor; ?>;
	 } 
  <?php } ?>
  
/*# sourceMappingURL=style.css.map */

</style>
<div class="row counter-sub-container py-3">
    <?php foreach($skillsets as $skillset) : ?>
        <div class="col-12 col-lg-<?php echo $count;?> mb-3 mb-lg-0" id="skillset-<?php echo $module->id; ?>">
            <div class="counter-wrapper d-lg-flex justify-content-lg-center align-items-lg-center p-3 text-center">
				<?php if(!empty($skillset->skillset_icon_upload) or !empty($skillset->skillset_icon_icon) ) {?>
                <div class="counter-icon d-lg-flex align-items-lg-center text-primary pt-lg-2 pr-lg-3 mb-2 mb-lg-0">
                    <?php  if($skillset->skillset_icon_option == 'upload') { ?>
						<?php if(!empty($skillset->skillset_icon_upload)) {?>
							<img src="<?php  echo $skillset->skillset_icon_upload; ?>" class="mx-auto d-block"></img>
						<?php } ?>
					 <?php }elseif($skillset->skillset_icon_option == 'icon'){ ?>
						<?php if(!empty($skillset->skillset_icon_icon)) {?>
							<i class="<?php  echo $skillset->skillset_icon_icon; ?> icon" class="mx-auto d-block" alt="icon"></i>
						<?php }?>
					 <?php }?>
                </div>
				 <?php }?>
				<?php if(!empty($skillset->skillset_title) or !empty($skillset->skillset_number)) { ?>
					<div class="counter-text-container text-center text-lg-left">
						<?php if($numberPosition=='above'){ ?>
							<?php if(!empty($skillset->skillset_number)) { ?>
								<p class="counter-number text-primary d-flex justify-content-center justify-content-lg-start">
									<span class="count"><?php echo $skillset->skillset_number; ?></span>
									<?php 
										if(($skillset->skillset_enable_symbol)) { ?>
											<span><<?php if($symbolPosition == 'sub') { echo 'sub';} elseif($symbolPosition == 'sup') { echo "sup"; } else { echo 'span';}   ?> class="symbol"><?php echo $skillset->skillset_symbol;  ?><?php if($symbolPosition == 'sub') { echo '</sub>';} elseif($symbolPosition == 'sup') { echo "</sup>"; } else { '</span>'; } ?>
											</span>
									<?php } ?>
								</p>
							<?php } ?>
						<?php } ?>
						<?php if(!empty($skillset->skillset_title)) { ?>
							<p class="counter-title"><?php echo $skillset->skillset_title; ?></p>
						<?php }?>
						
						<?php if($numberPosition=='below'){ ?>
							<?php if(!empty($skillset->skillset_number)) { ?>
								<p class="counter-number text-primary d-flex justify-content-center justify-content-lg-start">
									<span class="count"><?php echo $skillset->skillset_number; ?></span>
									<?php 
										if(($skillset->skillset_enable_symbol)) { ?>
											<span><<?php if($symbolPosition == 'sub') { echo 'sub';} elseif($symbolPosition == 'sup') { echo "sup"; } else { echo 'span';}   ?> class="symbol"><?php echo $skillset->skillset_symbol;  ?><?php if($symbolPosition == 'sub') { echo '</sub>';} elseif($symbolPosition == 'sup') { echo "</sup>"; } else { '</span>'; } ?>
											</span>
									<?php } ?>
								</p>
							<?php } ?>
						<?php } ?>
					</div>
				<?php } ?>
            </div>
        </div>
    <?php endforeach; ?> 
</div>

<script>
	$('.count').each(function() {
		$(this).prop('Counter', 0).animate({
			Counter: $(this).text()
		}, {
			duration: 4000,
			easing: 'swing',
			step: function(now) {
				$(this).text(Math.ceil(now));
			}
		});
	});
</script>