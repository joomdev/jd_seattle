<?php

namespace Nextend\SmartSlider3\Generator\Joomla\JoomlaContent;

use Nextend\SmartSlider3\Generator\AbstractGeneratorGroup;
use Nextend\SmartSlider3\Generator\Joomla\JoomlaContent\Sources\JoomlaContentArticle;
use Nextend\SmartSlider3\Generator\Joomla\JoomlaContent\Sources\JoomlaContentCategory;

class GeneratorGroupJoomlaContent extends AbstractGeneratorGroup {

    protected $name = 'joomlacontent';

    public function getLabel() {
        return n2_('Joomla articles');
    }

    public function getDescription() {
        return n2_('Creates slides from your Joomla articles or categories.');
    }

    protected function loadSources() {
        new JoomlaContentArticle($this, 'article', n2_('Article'));
        new JoomlaContentCategory($this, 'category', n2_('Category'));
    }

}