<?php

N2Loader::import('libraries.renderable.layers.item.itemFactoryAbstract', 'smartslider');

class N2SSPluginItemFactoryHeading extends N2SSPluginItemFactoryAbstract {

    protected $type = 'heading';

    protected $priority = 1;

    private $font = 1009;
    private $style = '';

    protected $class = 'N2SSItemHeading';

    public function __construct() {
        $this->title = n2_x('Heading', 'Slide item');
        $this->group = n2_x('Content', 'Layer group');
    }

    function getValues() {
        self::initDefault();

        return array(
            'priority'    => 'div',
            'fullwidth'   => 1,
            'nowrap'      => 0,
            'heading'     => n2_('Heading layer'),
            'title'       => '',
            'href'        => '#',
            'href-target' => '_self',
            'href-rel'    => '',
            'font'        => $this->font,
            'style'       => $this->style,

            'split-text-transform-origin'    => '50|*|50|*|0',
            'split-text-backface-visibility' => 1,

            'split-text-animation-in' => '',
            'split-text-delay-in'     => 0,

            'split-text-animation-out' => '',
            'split-text-delay-out'     => 0,

            'class' => ''
        );
    }

    function getPath() {
        return dirname(__FILE__) . DIRECTORY_SEPARATOR . $this->type . DIRECTORY_SEPARATOR;
    }

    public function upgradeData($data) {
        $linkV1 = $data->get('link', '');
        if (!empty($linkV1)) {
            list($link, $target, $rel) = array_pad((array)N2Parse::parse($linkV1), 3, '');
            $data->un_set('link');
            if (is_array($link)) {
                $data->set('href', implode('', $link));
            } else {
                $data->set('href', $link);
            }
            $data->set('href-target', $target);
            $data->set('href-rel', $rel);
        }
    }

    public function getFilled($slide, $data) {
        $data = parent::getFilled($slide, $data);

        $data->set('heading', $slide->fill($data->get('heading', '')));
        $data->set('href', $slide->fill($data->get('href', '#|*|')));

        return $data;
    }

    public function prepareExport($export, $data) {
        parent::prepareExport($export, $data);

        $export->addVisual($data->get('font'));
        $export->addVisual($data->get('style'));
        $export->addLightbox($data->get('href'));
    }

    public function prepareImport($import, $data) {
        $data = parent::prepareImport($import, $data);

        $data->set('font', $import->fixSection($data->get('font')));
        $data->set('style', $import->fixSection($data->get('style')));
        $data->set('href', $import->fixLightbox($data->get('href')));

        return $data;
    }

    private function initDefault() {
        static $inited = false;
        if (!$inited) {
            $res = N2StorageSectionAdmin::get('smartslider', 'default', 'item-heading-font');
            if (is_array($res)) {
                $this->font = $res['value'];
            }
            if (is_numeric($this->font)) {
                N2FontRenderer::preLoad($this->font);
            }

            $res = N2StorageSectionAdmin::get('smartslider', 'default', 'item-heading-style');
            if (is_array($res)) {
                $this->style = $res['value'];
            }
            if (is_numeric($this->style)) {
                N2StyleRenderer::preLoad($this->style);
            }
            $inited = true;
        }
    }

    public function globalDefaultItemFontAndStyle($fontTab, $styleTab) {
        self::initDefault();

        new N2ElementFont($fontTab, 'item-heading-font', n2_('Item') . ' - ' . n2_('Heading'), $this->font, array(
            'previewMode' => 'hover'
        ));

        new N2ElementStyle($styleTab, 'item-heading-style', n2_('Item') . ' - ' . n2_('Heading'), $this->style, array(
            'previewMode' => 'heading'
        ));
    }

    public function renderFields($form) {
        $settings = new N2Tab($form, 'item-heading');

        new N2ElementTextarea($settings, 'heading', n2_('Text'), n2_('Heading'), array(
            'fieldStyle' => 'width: 230px;resize: vertical;'
        ));

        $link = new N2ElementGroup($settings, 'link', '');
        new N2ElementUrl($link, 'href', n2_('Link'), '', array(
            'style' => 'width:236px;'
        ));
        new N2ElementLinkTarget($link, 'href-target', n2_('Target window'));
        new N2ElementLinkRel($link, 'href-rel', n2_('Rel'));

        $other = new N2ElementGroup($settings, 'item-heading-other');
        new N2ElementList($other, 'priority', 'Tag', 'div', array(
            'options' => array(
                'div' => 'div',
                '1'   => 'H1',
                '2'   => 'H2',
                '3'   => 'H3',
                '4'   => 'H4',
                '5'   => 'H5',
                '6'   => 'H6'
            )
        ));
        new N2ElementOnOff($other, 'fullwidth', n2_('Full width'), 1);
        new N2ElementOnOff($other, 'nowrap', n2_('No wrap'), 0);

        new N2ElementFont($settings, 'font', n2_('Font') . ' - ' . n2_('Heading'), '', array(
            'previewMode' => 'hover',
            'preview'     => '<div style="width:{nextend.activeLayer.prop(\'style\').width};"><div class="{styleClassName} {fontClassName}">{$(\'#item_headingheading\').val().replace(/\\n/g, \'<br />\');}</div></div>',
            'set'         => 1000,
            'style'       => 'item_headingstyle',
            'rowClass'    => 'n2-hidden'
        ));
        new N2ElementStyle($settings, 'style', n2_('Style') . ' - ' . n2_('Heading'), '', array(
            'previewMode' => 'heading',
            'preview'     => '<div style="width:{nextend.activeLayer.prop(\'style\').width};"><div class="{styleClassName} {fontClassName}">{$(\'#item_headingheading\').val().replace(/\\n/g, \'<br />\');}</div></div>',
            'set'         => 1000,
            'font'        => 'item_headingfont',
            'rowClass'    => 'n2-hidden'
        ));

    }

}

N2SmartSliderItemsFactory::addItem(new N2SSPluginItemFactoryHeading);
