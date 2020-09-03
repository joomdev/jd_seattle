<?php

namespace Nextend\Framework\Image\Joomla;

use JHtml;
use Nextend\Framework\Image\AbstractPlatformImage;

class JoomlaImage extends AbstractPlatformImage {

    public function initLightbox() {

        JHtml::_('behavior.modal');
    }
}