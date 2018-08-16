<?php
defined('_JEXEC') or die;
$pricingtables = $params->get('pricingtables', []);
$itemsInRow = $params->get('itemsInRow');
?>

<div class="container py-5">
	<div class="row">
	  <?php foreach ($pricingtables as $pricingtable) { ?>
		  <div class="col-12 col-md-6 col-lg-<?php echo $itemsInRow;  ?> d-flex pricing-card mt-3 <?php if($pricingtable->hightlight) {echo 'hightlight';}?>">
			<div class="card card-normal shadow-lg text-center w-100 mb-5 mb-lg-0">
				<?php if(!empty($pricingtable->title) or !empty($pricingtable->subtitle))  { ?>
				  <div class="card-header <?php if(!$pricingtable->headerBacground=="color") {echo 'bg-primary'; } ?> text-white pt-4" style="<?php if($pricingtable->headerBacground=="color") {echo 'background: ' . $pricingtable->headerBacground_color;} elseif($pricingtable->headerBacground=="media"){ echo 'background: url('.$pricingtable->headerBacground_upload.') no-repeat;
background-size: cover;';}?>">
					<?php if(!empty($pricingtable->title))  { ?>
						<h3 class="text-white"><?php echo $pricingtable->title; ?></h3>
					<?php } ?>
					<?php if(!empty($pricingtable->subtitle))  { ?>
						<p><?php echo $pricingtable->subtitle; ?></p>
					<?php } ?>
				  </div>
				<?php } ?> 
				<?php if(!empty($pricingtable->description)){?>
				  <div class="card-body text-center pt-5 px-4">
						<?php echo $pricingtable->description; ?>
				  </div>
				<?php }	 ?>
			<?php if(!empty($pricingtable->pricing) or  !empty($pricingtable->period) or !empty($pricingtable->button_text) or !empty($pricingtable->bottom_line)){?>
				  <div class="card-footer border-0 pt-4">
					  <?php if(!empty($pricingtable->pricing) and !empty($pricingtable->period) ){?>
						<h3 class="mb-3" style="<?php if($pricingtable->pricingColor) {echo 'color: ' . $pricingtable->pricingColor;}?>"><?php echo $pricingtable->pricing; ?>
						  <small><?php echo $pricingtable->period; ?></small>
						</h3>
					  <?php } ?>
					  
						<?php if(!empty($pricingtable->button_text)) {?>
							<a href="<?php echo ($pricingtable->button_link) ? $pricingtable->button_link : '#'  ?>" class="btn btn-outline-primary btn-block w-75 mx-auto mb-3"><?php echo $pricingtable->button_text; ?></a>
						<?php } ?>
						
						<?php if(!empty($pricingtable->bottom_line)) {?>
							<p>
							  <small class="text-gray"><?php echo $pricingtable->bottom_line; ?></small>
							</p>
						<?php } ?>
				  </div>
			<?php } ?>
			</div>
		  </div>
	  <?php } ?>
	</div>
</div>