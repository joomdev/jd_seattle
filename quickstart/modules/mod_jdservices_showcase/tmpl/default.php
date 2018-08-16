<?php
defined('_JEXEC') or die;
$services = $params->get('services', []);
$showcase_height = $params->get('showcase_height', 'auto');
$LinkOn = $params->get('LinkOn');
$showReadMore = $params->get('showReadMore');
$showReadMoreText = $params->get('showReadMoreText');
?>
<div class="row">
    <?php foreach ($services as $service) { ?>
        <div class="col-12 col-md-6 col-lg-4 d-flex mb-5">
			<?php if($LinkOn =="fullBox") { ?> <a href="<?php echo $url = JRoute::_('index.php?Itemid=' . $service->link); ?>"><?php } ?>
                <div class="features-box-icon-wrapper w-100 shadow-lg d-flex flex-row icon-left p-4">
                <?php if(!empty($service->serveices_icon_upload) or !empty($service->serveices_icon_class) ) {?>
                        <div class="features-box-icon pr-4">
                            <?php if($service->serveices_iconOption ==  "icon") { ?>
                                <i class="<?php echo $service->serveices_icon_class; ?>"></i>
                            <?php }  ?>
                            <?php if($service->serveices_iconOption ==  "upload") { ?>
                                <?php if($LinkOn  =="titleAndMedia") { ?> <a href="<?php echo $url = JRoute::_('index.php?Itemid=' . $service->link); ?>"><?php } ?>
                                    <?php if(!empty($service->serveices_icon_upload)) {?>
                                        <img src="<?php echo $service->serveices_icon_upload; ?>">
                                    <?php } ?>
                                <?php if($LinkOn  =="titleAndMedia") { ?> </a> <?php } ?> 
                            <?php }  ?>
                        </div>
                    <?php }  ?>
                    <?php if(!empty ($service->title) or !empty($service->description)){ ?>
                        <div class="features-box-content">
                            <?php if(!empty($service->title)){ ?>
                                <h4>
                                    <?php if($LinkOn    =="title" or $LinkOn  =="titleAndMedia") { ?>
                                        <a href="<?php echo $url = JRoute::_('index.php?Itemid=' . $service->link); ?>">
                                    <?php } ?>
                                            <?php echo $service->title; ?>
                                    <?php if($LinkOn    =="title" or $LinkOn  =="titleAndMedia") { ?>
                                        </a>
                                    <?php } ?> 
                                </h4>
                            <?php } ?>
                            <?php if(!empty($service->description)){ ?>
                                <p><?php echo $service->description; ?></p>
                            <?php } ?>
                            <?php if(($showReadMore)){ ?>
                                <?php if(!empty($showReadMoreText)){ ?>
                                    <p><a href="<?php echo $url = JRoute::_('index.php?Itemid=' . $service->link); ?>"><?php echo $showReadMoreText; ?></a></p>
                                <?php } ?>
                            <?php } ?>
                        </div>
                    <?php } ?>
                </div>
            <?php if($LinkOn  =="fullBox") { ?></a> <?php } ?> 
        </div>
    <?php } ?>
</div>