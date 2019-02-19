<?php

N2Loader::import('libraries.renderable.layers.item.itemFactoryAbstract', 'smartslider');

class N2SSPluginItemFactoryText extends N2SSPluginItemFactoryAbstract {

    protected $type = 'text';

    protected $priority = 2;

    private $font = 1304;

    private $style = '';

    protected $layerProperties = array(
        "desktopportraitleft"   => 0,
        "desktopportraittop"    => 0,
        "desktopportraitwidth"  => 400,
        "desktopportraitalign"  => "left",
        "desktopportraitvalign" => "top"
    );

    protected $class = 'N2SSItemText';

    public function __construct() {
        $this->title = n2_x('Text', 'Slide item');
        $this->group = n2_x('Content', 'Layer group');
    }

    private function initDefaultFont() {
        static $inited = false;
        if (!$inited) {
            $res = N2StorageSectionAdmin::get('smartslider', 'default', 'item-text-font');
            if (is_array($res)) {
                $this->font = $res['value'];
            }
            if (is_numeric($this->font)) {
                N2FontRenderer::preLoad($this->font);
            }
            $inited = true;
        }
    }

    private function initDefaultStyle() {
        static $inited = false;
        if (!$inited) {
            $res = N2StorageSectionAdmin::get('smartslider', 'default', 'item-text-style');
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
        self::initDefaultFont();
        new N2ElementFont($fontTab, 'item-text-font', n2_('Item') . ' - ' . n2_('Text'), $this->font, array(
            'previewMode' => 'paragraph'
        ));

        self::initDefaultStyle();
        new N2ElementStyle($styleTab, 'item-text-style', n2_('Item') . ' - ' . n2_('Text'), $this->style, array(
            'previewMode' => 'heading'
        ));
    }

    function getValues() {
        self::initDefaultFont();
        self::initDefaultStyle();

        return array(
            'content'       => 'Lorem ipsum dolor sit amet, <a href="#">consectetur adipiscing</a> elit, sed do eiusmod tempor incididunt ut labore et dolore magna aliqua. Ut enim ad minim veniam, quis nostrud exercitation ullamco laboris nisi ut aliquip ex ea commodo consequat.',
            'contenttablet' => '',
            'contentmobile' => '',
            'font'          => $this->font,
            'style'         => $this->style
        );
    }

    function getPath() {
        return dirname(__FILE__) . DIRECTORY_SEPARATOR . $this->type . DIRECTORY_SEPARATOR;
    }

    public function getFilled($slide, $data) {
        $data = parent::getFilled($slide, $data);

        $data->set('content', $slide->fill($data->get('content', '')));
        $data->set('contenttablet', $slide->fill($data->get('contenttablet', '')));
        $data->set('contentmobile', $slide->fill($data->get('contentmobile', '')));

        return $data;
    }

    public function prepareExport($export, $data) {
        parent::prepareExport($export, $data);

        $export->addVisual($data->get('font'));
        $export->addVisual($data->get('style'));
    }

    public function prepareImport($import, $data) {
        $data = parent::prepareImport($import, $data);

        $data->set('font', $import->fixSection($data->get('font')));
        $data->set('style', $import->fixSection($data->get('style')));

        return $data;
    }

    public function renderFields($form) {
        $settings = new N2Tab($form, 'item-text');

        new N2ElementRichTextarea($settings, 'content', n2_('Text'), '', array(
            'fieldStyle' => 'height: 120px; width: 230px;resize: vertical;'
        ));

        new N2ElementFont($settings, 'font', n2_('Font') . ' - ' . n2_x('Text', 'Slide item'), '', array(
            'previewMode' => 'paragraph',
            'preview'     => '<div style="width:{nextend.activeLayer.width()}px;"><p class="{styleClassName} {fontClassName}">{$(\'#item_textcontent\').val();}</p></div>',
            'set'         => 1000,
            'style'       => 'item_textstyle',
            'rowClass'    => 'n2-hidden'
        ));
        new N2ElementStyle($settings, 'style', n2_('Style') . ' - ' . n2_x('Text', 'Slide item'), '', array(
            'previewMode' => 'heading',
            'preview'     => '<div style="width:{nextend.activeLayer.width()}px;"><p class="{styleClassName} {fontClassName}">{$(\'#item_textcontent\').val();}</p></div>',
            'set'         => 1000,
            'font'        => 'item_textfont',
            'rowClass'    => 'n2-hidden'
        ));

        new N2ElementRichTextarea($settings, 'contenttablet', n2_('Tablet text'), '', array(
            'fieldStyle' => 'height: 120px; width: 230px;resize: vertical;'
        ));

        new N2ElementRichTextarea($settings, 'contentmobile', n2_('Mobile text'), '', array(
            'fieldStyle' => 'height: 120px; width: 230px;resize: vertical;'
        ));
    }
}

N2SmartSliderItemsFactory::addItem(new N2SSPluginItemFactoryText);