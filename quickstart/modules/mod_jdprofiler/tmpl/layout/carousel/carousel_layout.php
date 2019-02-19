<?php
defined('_JEXEC') or die;
// Licensed under the GPL v3
//echo "<pre>";
//print_r($profiles);
?>
<div class="jd-profiler-wrapper jd-carousel-layout-view jd-carousel-simple-layout">
  <div class="row <?php echo ($params->get('gutter_space')=='nomargin') ? 'jd-nogutters' : '' ?>">
    <div class="col-12">
      <div class="jd-row jd-team-carousel">
        <!-- Team Item wrapper start -->
        <?php foreach($profiles as $profile) { ?>
            <div class="jd-team-columns <?php echo ($params->get('gutter_space')=='') ? 'col' : '' ?>" <?php if($params->get('gutter_space')=='custom') { ?> style="padding-right:<?php echo $params->get('margin');?>px; padding-left:<?php echo $params->get('margin');?>px;" <?php } ?>>
              <div class="card-team jd-team-items">
                <img src="<?php echo $profile->image;  ?>" alt="" class="card-img-top team-mamber-image">
                <?php if($params->get('display_name',1) or $params->get('display_designation',1) ) { ?>
                  <div class="card-team-body">
                    <div class="team-member-content-wrapper">
                      <?php if($params->get('display_name',1)) { ?>
                        <h5 class="card-img-overlayteam-member-name">
                          <?php  echo $profile->name; ?>
                        </h5>
                      <?php } ?>
                      <?php if($params->get('display_designation',1)) { ?>
                        <?php if(!empty($profile->designation)) { ?>  
                          <p class="team-member-designation">
                            <small><?php echo $profile->designation;  ?></small>
                          </p>
                        <?php } ?>
                      <?php } ?>
                      <?php if($params->get('display_sbio',1)) { ?>
                        <?php if(!empty($profile->sbio)) { ?>    
                          <p class="card-img-overlayteam-member-bio"><?php echo $profile->sbio;  ?></p>
                        <?php } ?>
                      <?php } ?>
                    </div>
                  </div>
                <?php } ?>
                <?php if($params->get('show_socialsIcon',1)) { ?>
                  <?php if(!empty($profile->social)) { ?>
                  <div class="card-team-footer social-profile-wrapper">
                    <ul class="social-profile <?php echo $params->get('IconStyle'); ?>">
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
</div>
<!-- End Jd Team Showcase wrapper -->

<link rel="stylesheet" type="text/css" href="//cdn.jsdelivr.net/npm/slick-carousel@1.8.1/slick/slick.css"/>
<link rel="stylesheet" type="text/css" href="//cdnjs.cloudflare.com/ajax/libs/slick-carousel/1.9.0/slick-theme.css"/>
<script type="text/javascript" src="//cdn.jsdelivr.net/npm/slick-carousel@1.8.1/slick/slick.min.js"></script>
<script>
(function ($) {
    // Slick Js start
    var intislickSlider = function () {
        $('.jd-team-carousel').slick({
            arrows: <?php if($params->get('DisplayArrow')){ echo "true"; }else{ echo 'false'; } ?>,
            dots: <?php if($params->get('DisplayBullit')){ echo "true"; }else{ echo 'false'; } ?>,
            infinite: true,
            speed: 300,
            slidesToShow: <?php echo  $params->get('grid_coloumns_carosuel',3); ?>,
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
                        slidesToScroll: 2,
                        arrows: false
                    }
                },
                {
                    breakpoint: 480,
                    settings: {
                        slidesToShow: 1,
                        slidesToScroll: 1,
                        arrows: false
                    }
                }
            ]
        });
    }
    // end slick slider
    // Events
    var docReady = function () {
        intislickSlider();
    };
    $(docReady);
})(jQuery);
  </script>