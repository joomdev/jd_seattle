<?php

defined('_JEXEC') or die;

// Licensed under the GPL v3

//echo "<pre>";

//print_r($profiles);

?>

<div class="jd-profiler-wrapper jd-list-layout-view jd-list-simple-layout">

	<div class="jd-row <?php echo ($params->get('gutter_space')=='nomargin') ? 'jd-nogutters' : '' ?>">

		<?php foreach($profiles as $profile) { ?>

		<!-- Team Item wrapper start -->

		<div class="jd-team-columns jd-col-12">

			<div class="card-team jd-team-items">

				<div class="team-mamber-image-wrapper">

					<img src="<?php echo $profile->image; ?>" alt="<?php  echo $profile->name; ?>" class="team-mamber-image">

				</div>

				<?php if($params->get('display_name',1) or $params->get('display_designation',1) ) { ?>

				<div class="card-team-body">

					<div class="team-member-content-wrapper">

						<?php if($params->get('display_name',1)) { ?>

						<h5 class="team-member-name">

							<?php if($params->get('enable_link')){ ?>

							<a href="<?php echo JRoute::_('index.php?option=com_jdprofiler&view=profile&id='.(int) $profile->id); ?>">
								<?php  echo $profile->name; ?></a>

							<?php }else {?>

							<?php  echo $profile->name; ?>

							<?php  } ?>

						</h5>

						<?php } ?>

						<?php if($params->get('display_designation',1)) { ?>

						<?php if(!empty($profile->designation)) { ?>

						<p class="team-member-designation">

							<small>
								<?php  echo $profile->designation; ?></small>

						</p>

						<?php } ?>

						<?php } ?>

						<?php if(!empty($profile->sbio)) { ?>

						<p class="team-member-bio">
							<?php echo $profile->sbio;  ?>
						</p>

						<?php } ?>
					<?php if(!empty($profile->phone) and !empty($profile->email)  and  !empty($profile->location)) {?>			
						<ul class="list-unstyled contact-info">

							<?php if($params->get('show_Contact',1)) { ?>

							<?php if(!empty($profile->phone)) { ?>

							<li>

								<i class="fas fa-phone fa-rotate-90"></i>
								<?php  echo $profile->phone; ?>
							</li>

							<li>

								<?php } ?>

								<?php } ?>

								<?php if(!empty($profile->email)) { ?>

								<i class="fas fa-envelope"></i>
								<?php  echo $profile->email; ?>
							</li>

							<?php } ?>

							<?php if(!empty($profile->location)) { ?>

							<li>

								<i class="fas fa-map-marker-alt"></i>

								<?php  echo $profile->location; ?>
							</li>

							<?php } ?>

						</ul>
							<?php } ?>

					</div>

					<?php } ?>

					<?php if($params->get('show_socialsIcon',1)) { ?>

					<?php if(!empty($profile->social)) { ?>

					<div class="social-profile-wrapper">

						<ul class="social-profile <?php echo $params->get('IconStyle','none'); ?>">

							<?php  $socials=  json_decode($profile->social);?>

							<?php  foreach($socials as $social){?>

							<li>

								<a href="<?php echo $social->link?>" target="<?php if($params->get('new_tab')){echo '1'; } ?>">

									<i class="<?php echo $social->icon?>"></i>

								</a>

							</li>

							<?php } ?>

						</ul>

					</div>

					<?php } ?>

					<?php } ?>

					<!-- Social Profile Wrapper End -->

				</div>

			</div>

		</div>

		<?php } ?>

		<!-- Team Item wrapper end -->

	</div>

</div>
