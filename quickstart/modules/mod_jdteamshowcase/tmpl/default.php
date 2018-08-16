<?php
defined('_JEXEC') or die;
$profiles = $params->get('profiles', []);
?>
<div class="row">
   <?php foreach ($profiles as $profile) { ?>
      <div class="col-12 col-lg-4 mb-5">
         <div class="trainer-member-wrapper">
            <div class="trainer-member-image text-center">
               <img class="img-fluid" src="<?php echo JURI::root() . $profile->thumbnail; ?>" alt="<?php echo $profile->name; ?>">
               <div class="team-overly">
                  <div class="col team-member-info text-center py-3">
                     <h5 class="trainer-member-name m-0"><?php echo $profile->name; ?></h5>
                     <p class="trainer-member-designation mb-3"><?php echo $profile->designation; ?></p>
                     <!-- Social links -->
                     <ul class="social list-inline m-0 p-0">
                        <?php
                        for ($i = 1; $i <= 5; $i++) {
                           $icon = $profile->{'social_profile_' . $i . '_icon'};
                           $link = $profile->{'social_profile_' . $i . '_link'};
                           if (!empty($link)) {
                              ?>
                              <li class="list-inline-item">
                                 <a href="<?php echo $link; ?>">
                                    <i class="<?php echo $icon; ?>" aria-hidden="true"></i>
                                 </a>
                              </li>
                           <?php } ?>
                        <?php } ?>
                     </ul>
                  </div>
                  <!-- End Trainer Member Info -->
               </div>
            </div>
            <!-- End Trainer Member image -->
         </div>
         <!-- Trainer Member wrapper -->
      </div>
   <?php } ?>
</div>