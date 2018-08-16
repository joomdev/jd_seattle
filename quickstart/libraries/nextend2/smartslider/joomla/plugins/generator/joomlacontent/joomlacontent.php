<?php
N2Loader::import('libraries.plugins.N2SliderGeneratorPluginAbstract', 'smartslider');

class N2SSPluginGeneratorJoomlaContent extends N2SliderGeneratorPluginAbstract {

    protected $name = 'joomlacontent';

    public function getLabel() {
        return 'Joomla articles';
    }

    protected function loadSources() {
        new N2GeneratorJoomlaContentArticle($this, 'article', n2_('Article'));
        new N2GeneratorJoomlaContentCategory($this, 'category', n2_('Category'));
    }

    public function getPath() {
        return dirname(__FILE__) . DIRECTORY_SEPARATOR;
    }

}

N2SSGeneratorFactory::addGenerator(new N2SSPluginGeneratorJoomlaContent);