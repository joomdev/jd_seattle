<?php
defined('_JEXEC') or die;
// Licensed under the GPL v3
//echo "<pre>";
//print_r($profiles);
?>
<div class="jd-profiler-wrapper jd-grid-layout-view jd-grid-simple-layout">
  <div class="jd-row <?php echo ($params->get('gutter_space')=='nomargin') ? 'jd-nogutters' : '' ?>">
	<!-- Team Item wrapper start -->
	<?php foreach($profiles as $profile) { ?>
	<div class="jd-team-columns jd-col-12 jd-col-md-6 jd-col-lg-<?php echo  $params->get('grid_coloumns'); ?>" <?php if($params->get('gutter_space')=='custom') { ?> style="padding-right:<?php echo $params->get('margin');?>px; padding-left:<?php echo $params->get('margin');?>px;" <?php } ?>>
		<div class="card-team jd-team-items">
		  <?php if(!empty($profile->image)) { ?>
			<img src="<?php echo $profile->image;  ?>" alt="<?php echo $profile->name;  ?>" class="card-img-top team-mamber-image">
		  <?php }?>  
		  <?php if($params->get('display_name',1) or $params->get('display_designation',1) ) { ?>
			<div class="card-team-body">
			  <div class="team-member-content-wrapper">
				  <?php if($params->get('display_name',1)) { ?>
					<h5 class="card-img-overlayteam-member-name">
					<?php if($params->get('enable_link')){ ?>
					  <a href="<?php echo JRoute::_('index.php?option=com_jdprofiler&view=profile&id='.(int) $profile->id); ?>"><?php  echo $profile->name; ?></a>
					<?php }else {?>
					  <?php  echo $profile->name; ?>
					<?php  } ?>
					  </h5>
					<?php } ?>
				  <?php if($params->get('display_designation',1)) { ?>
					<p class="team-member-designation">
					  <small><?php echo $profile->designation;  ?></small>
					</p>
				  <?php } ?>
				  <?php if(!empty($profile->sbio)) { ?>
						<?php if($params->get('display_sbio',1)) { ?>
					  	<p class="card-img-overlayteam-member-bio"><?php echo $profile->sbio;  ?></p>
						<?php } ?>
				  <?php }?>
			  </div>
			</div>
		  <?php }?>
		  <?php if($params->get('show_socialsIcon',1)) { ?>
			  <?php if(!empty($profile->social)) { ?>
				  <div class="card-team-footer social-profile-wrapper">
					<ul class="social-profile <?php  echo $params->get('IconStyle','none'); ?>">
					  <?php  $socials=  json_decode($profile->social);?>
						<?php  foreach($socials as $social){?>
							<li>
								<a href="<?php echo $social->link?>" target="<?php if($params->get('new_tab')){echo '1'; } ?>" >
								  <i class="<?php echo $social->icon?>"></i>
								</a>
							</li>
						<?php } ?>
					</ul>
				  </div>
				<?php } ?> 
			  <?php } ?>
		  </div>
	  </div>
	<?php  } ?>
	<!-- Team Item wrapper end -->
  </div>
</div>
</div>