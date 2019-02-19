<?php
defined('_JEXEC') or die;
$profiles = $params->get('profiles', []);
?>
<div class="row team-wrapper" id="team-wrapper-<?php echo $module->id; ?>">
    <?php foreach ($profiles as $profile) { ?>
        <div class="col pb-5">
            <div class="card card-team shadow-sm">
                <img class="card-img-top" src="<?php echo JURI::root() . $profile->thumbnail; ?>" alt="<?php echo $profile->name; ?>">
                <div class="card-body">
                    <h6 class="card-text"><?php echo $profile->designation; ?></h6>
                    <p class="card-text">
                        <small class="text-muted"><?php echo $profile->description; ?></small>
                    </p>
                </div>
                <div class="card-footer">
                <h6 class="card-title text-center">
                        <?php echo $profile->name; ?>
                    </h6>
                    <div class="social-icon">
                        <ul class="nav justify-content-center">
                        <?php
                        for ($i = 1; $i <= 5; $i++) {
                           $icon = $profile->{'social_profile_' . $i . '_icon'};
                           $link = $profile->{'social_profile_' . $i . '_link'};
                           if (!empty($link)) {
                              ?>
                              <li>
                                 <a href="<?php echo $link; ?>">
                                    <i class="<?php echo $icon; ?>" aria-hidden="true"></i>
                                 </a>
                              </li>
                           <?php } ?>
                        <?php } ?>
                        </ul>
                    </div>
                </div>
            </div>
        </div>
    <?php } ?>    
</div>
<script src="<?php echo JURI::root(); ?>templates/<?php echo JFactory::getApplication()->getTemplate(true)->template; ?>/js/slick.min.js"></script>
<script>
(function($){
    $(function(){
        $('#team-wrapper-<?php echo $module->id; ?>').slick({
                  arrows: false,
                  dots: true,
                  infinite: true,
                  speed: 300,
                  slidesToShow: 4,
                  adaptiveHeight: true,
                  responsive: [{
                              breakpoint: 1200,
                              settings: {
                                    slidesToShow: 3,
                                    slidesToScroll: 3,
                              }
                        },
                        {
                              breakpoint: 991,
                              settings: {
                                    slidesToShow: 2,
                                    slidesToScroll: 2
                              }
                        },
                        {
                              breakpoint: 480,
                              settings: {
                                    slidesToShow: 1,
                                    slidesToScroll: 1
                              }
                        }
                  ]
            });
    });
})(jQuery);
</script>