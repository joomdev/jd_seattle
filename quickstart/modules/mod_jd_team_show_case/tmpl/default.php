<?php
// No direct access
defined('_JEXEC') or die;
$teams = $params->get('teams', []);
?>
<div class="row">
      <div class="col-12">
      <div class="row justify-content-center mb-5">
            <div class="col-12 col-md-8 section-heading text-center">
                <p class="title-subheading">Our Team</p>
                <h2 class="title-heading">Lorem ipsum dolor sit amet, consectetur adipiscing elit.
                </h2>
            </div>
        </div>
        <div class="row team-wrapper">
        <?php foreach($teams as $team) :?>
                <div class="col pb-5">
                    <div class="card card-team shadow-sm">
                        <img class="card-img-top" src="<?php echo $team->team_image; ?>" alt="<?php echo $team->team_name; ?>">
                        <div class="card-body">
                            <h5 class="card-title">
                                <a href="#"><?php echo $team->team_name; ?></a>
                            </h5>
                            <p class="card-text"><?php echo $team->team_designation; ?></p>
                            <p class="card-text">
                                <small class="text-muted"><?php echo $team->team_description; ?></small>
                            </p>
                        </div>
                        <div class="card-footer">
                            <div class="social-icon">
                                <ul class="nav justify-content-center">
                                    <li>
                                        <a href="#">
                                            <i class="fab fa-facebook-f"></i>
                                        </a>
                                    </li>
                                    <li>
                                        <a href="#">
                                            <i class="fab fa-twitter"></i>
                                        </a>
                                    </li>
                                    <li>
                                        <a href="#">
                                            <i class="fab fa-linkedin-in"></i>
                                        </a>
                                    </li>
                                    <li>
                                        <a href="#">
                                            <i class="fab fa-google-plus-g"></i>
                                        </a>
                                    </li>
                                </ul>
                            </div>
                        </div>
                    </div>
                </div>
            <?php endforeach;?>
        </div>
    </div>
</div>
