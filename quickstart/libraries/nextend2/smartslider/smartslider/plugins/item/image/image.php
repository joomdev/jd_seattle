<?php

N2Loader::import('libraries.renderable.layers.item.itemFactoryAbstract', 'smartslider');

class N2SSPluginItemFactoryImage extends N2SSPluginItemFactoryAbstract {

    protected $type = 'image';

    protected $priority = 3;

    protected $layerProperties = array("desktopportraitwidth" => "300");

    private $style = '';

    protected $class = 'N2SSItemImage';

    public function __construct() {
        $this->title = n2_x('Image', 'Slide item');
        $this->group = n2_x('Image', 'Layer group');
    }

    private function initDefaultStyle() {
        static $inited = false;
        if (!$inited) {
            $res = N2StorageSectionAdmin::get('smartslider', 'default', 'item-image-style');
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
        self::initDefaultStyle();

        new N2ElementStyle($styleTab, 'item-image-style', n2_('Item') . ' - ' . n2_('Image'), $this->style, array(
            'previewMode' => 'box'
        ));
    }

    function getValues() {
        self::initDefaultStyle();

        return array(
            'image'          => '$system$/images/placeholder/image.png',
            'alt'            => '',
            'title'          => '',
            'link'           => '#|*|_self',
            'size'           => 'auto|*|auto',
            'style'          => $this->style,
            'cssclass'       => '',
            'image-optimize' => 1
        );
    }

    function getPath() {
        return dirname(__FILE__) . DIRECTORY_SEPARATOR . $this->type . DIRECTORY_SEPARATOR;
    }

    public static function getFilled($slide, $data) {
        $data->set('image', $slide->fill($data->get('image', '')));
        $data->set('alt', $slide->fill($data->get('alt', '')));
        $data->set('title', $slide->fill($data->get('title', '')));
        $data->set('link', $slide->fill($data->get('link', '#|*|')));

        return $data;
    }

    public function prepareExport($export, $data) {
        $export->addImage($data->get('image'));
        $export->addVisual($data->get('style'));
        $export->addLightbox($data->get('link'));
    }

    public function prepareImport($import, $data) {
        $data->set('image', $import->fixImage($data->get('image')));
        $data->set('style', $import->fixSection($data->get('style')));
        $data->set('link', $import->fixLightbox($data->get('link')));

        return $data;
    }

    public function prepareSample($data) {
        $data->set('image', N2ImageHelper::fixed($data->get('image')));

        return $data;
    }

    public function renderFields($form) {
        $settings = new N2Tab($form, 'item-image');

        new N2ElementImage($settings, 'image', n2_('Image'), '', array(
            'fixed'      => true,
            'style'      => 'width:236px;',
            'relatedAlt' => 'item_imagealt'
        ));

        $link = new N2ElementMixed($settings, 'link', '', '|*|_self|*|');
        new N2ElementUrl($link, 'link-1', n2_('Link'), '', array(
            'style' => 'width:236px;'
        ));
        new N2ElementList($link, 'link-2', n2_('Target window'), '', array(
            'options' => array(
                '_self'  => n2_('Self'),
                '_blank' => n2_('New')
            )
        ));
        new N2ElementList($link, 'link-3', 'Rel', '', array(
            'options' => array(
                ''           => '',
                'nofollow'   => 'nofollow',
                'noreferrer' => 'noreferrer',
                'author'     => 'author',
                'external'   => 'external',
                'help'       => 'help'
            )
        ));

        $seo = new N2ElementGroup($settings, 'item-image-seo');
        new N2ElementText($seo, 'alt', 'SEO - ' . n2_('Alt tag'), '', array(
            'style' => 'width:125px;'
        ));
        new N2ElementText($seo, 'title', 'SEO - ' . n2_('Title'), '', array(
            'style' => 'width:125px;'
        ));

        $misc = new N2ElementGroup($settings, 'item-image-misc', '', array(
        ));
        $size = new N2ElementMixed($misc, 'size', '', 'auto|*|auto');
        new N2ElementText($size, 'size-1', n2_('Width'), '', array(
            'style' => 'width:60px;'
        ));
        new N2ElementText($size, 'size-2', n2_('Height'), '', array(
            'style' => 'width:60px;'
        ));


    }

}

N2SmartSliderItemsFactory::addItem(new N2SSPluginItemFactoryImage);