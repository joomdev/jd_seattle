<?php
namespace Nextend\SmartSlider3\Application\Admin\Layout\Block\Core\AdminEmpty;

use Nextend\SmartSlider3\Settings;

/**
 * @var $this BlockAdminEmpty
 */
if (intval(Settings::get('force-rtl-backend', 0))) {
    ?>
    <script>
        jQuery(document).ready(function () {
            jQuery("html").attr("dir", "rtl");
        });
    </script>
    <?php
}

?>
<div <?php $this->renderAttributes(); ?>>
    <?php $this->displayContent(); ?>
</div>