<?php
defined('_JEXEC') or die;
$items = $params->get('items', []);
?>
<div class="row">
    <?php $i=1; foreach($items as $item) {?>
        <div class="col-12 col-md-3 position-relative mb-5">
            <div class="process-wrapper text-center text-white">
                <span class="pro-icon d-block rounded-circle mb-4 mx-auto" title="<?php echo  $i; ?>">
                    <i class="<?php echo $item->icon; ?>"></i>
                </span>
                <h6 class="text-white"><?php echo $item->title; ?></h6>
                <i class="lni-arrow-right d-none d-md-block"></i>
            </div>
        </div>
    <?php $i++; } ?>
</div>