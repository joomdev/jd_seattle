<?php
defined('_JEXEC') or die;
$services = $params->get('services', []);
$LinkOn = $params->get('LinkOn');
$showReadMore = $params->get('showReadMore');
$showReadMoreText = $params->get('showReadMoreText');
?>
<div class="jdservices row">
    <?php foreach ($services as $service) { ?>
        <div class="col-12 col-md-6 col-lg-4 d-flex mb-5">
            <div class="features-box-icon-wrapper shadow-lg d-flex flex-row icon-left p-4">
                <?php if(!empty($service->serveices_icon_upload) or !empty($service->serveices_icon_class) ) {?>
                <div class="features-box-icon pr-4">
                    <?php if($service->serveices_iconOption == "icon") { ?>
                        <?php if($LinkOn =="titleAndMedia") { ?> <a href="<?php echo $url = JRoute::_('index.php?Itemid=' . $service->link); ?>"><?php } ?>
                            <i class="service-icon <?php echo $service->serveices_icon_class; ?>"></i>
                        <?php if($LinkOn =="titleAndMedia") { ?> </a> <?php } ?> 
                    <?php } ?>
                    <?php if($service->serveices_iconOption == "upload") { ?>
                        <?php if($LinkOn =="titleAndMedia") { ?> <a href="<?php echo $url = JRoute::_('index.php?Itemid=' . $service->link); ?>"><?php } ?>
                            <?php if(!empty($service->serveices_icon_upload)) {?>
                                <img src="<?php echo $service->serveices_icon_upload; ?>" alt="<?php echo $service->title; ?>">
                            <?php } ?>
                        <?php if($LinkOn =="titleAndMedia") { ?> </a> <?php } ?> 
                    <?php } ?>
                </div>
                <?php } ?>
                <?php if(!empty ($service->title) or !empty($service->description)){ ?>
                    <div class="features-box-content">
                        <?php if(!empty($service->title)){ ?>
                            <h4 class="services-title">
                                <?php if($LinkOn =="title" or $LinkOn =="titleAndMedia") { ?>
                                    <a href="<?php echo $url = JRoute::_('index.php?Itemid=' . $service->link); ?>">
                                <?php } ?>
                                    <?php echo $service->title; ?>
                                <?php if($LinkOn =="title" or $LinkOn =="titleAndMedia") { ?>
                                    </a>
                                <?php } ?> 
                            </h4>
                        <?php } ?>
                        <?php if(!empty($service->description)){ ?>
                            <p class="services-description"><?php echo $service->description; ?></p>
                        <?php } ?>
                        <?php if(($showReadMore)){ ?>
                            <?php if(!empty($showReadMoreText)){ ?>
                                <a class="services-btn" href="<?php echo $url = JRoute::_('index.php?Itemid=' . $service->link); ?>"><?php echo $showReadMoreText; ?></a>
                            <?php } ?>
                        <?php } ?>
                    </div>
                <?php } ?>
                <?php if($LinkOn =="fullBox") { ?> <a class="fullwidthbox" href="<?php echo $url = JRoute::_('index.php?Itemid=' . $service->link); ?>"><?php } ?> <?php if($LinkOn =="fullBox") { ?></a> <?php } ?> 
            </div>
        </div>
    <?php } ?>
</div>