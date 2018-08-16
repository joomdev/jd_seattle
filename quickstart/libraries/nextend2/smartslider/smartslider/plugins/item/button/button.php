<?php

N2Loader::import('libraries.renderable.layers.item.itemFactoryAbstract', 'smartslider');

class N2SSPluginItemFactoryButton extends N2SSPluginItemFactoryAbstract {

    public $type = 'button';

    protected $priority = 3;

    private $font = 1103;
    private $style = 1101;

    protected $class = 'N2SSItemButton';

    public function __construct() {
        $this->title = n2_x('Button', 'Slide item');
        $this->group = n2_('Basic');
    }

    private function initDefaultFont() {
        static $inited = false;
        if (!$inited) {
            $res = N2StorageSectionAdmin::get('smartslider', 'default', 'item-button-font');
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
            $res = N2StorageSectionAdmin::get('smartslider', 'default', 'item-button-style');
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
        $this->initDefaultFont();
        new N2ElementFont($fontTab, 'item-button-font', n2_('Item') . ' - ' . n2_('Button'), $this->font, array(
            'set'         => 1100,
            'previewMode' => 'link'
        ));

        $this->initDefaultStyle();
        new N2ElementStyle($styleTab, 'item-button-style', n2_('Item') . ' - ' . n2_('Button'), $this->style, array(
            'set'         => 1100,
            'previewMode' => 'button'
        ));
    }

    function getValues() {
        $this->initDefaultFont();
        $this->initDefaultStyle();

        return array(
            'content'       => n2_('MORE'),
            'nowrap'        => 1,
            'fullwidth'     => 0,
            'link'          => '#|*|_self',
            'font'          => $this->font,
            'style'         => $this->style,
            'class'         => '',
            'icon'          => '',
            'iconsize'      => '100',
            'iconspacing'   => '30',
            'iconplacement' => 'left',
        );
    }

    function getPath() {
        return dirname(__FILE__) . DIRECTORY_SEPARATOR . $this->type . DIRECTORY_SEPARATOR;
    }

    public static function getFilled($slide, $data) {
        $data->set('content', $slide->fill($data->get('content', '')));
        $data->set('link', $slide->fill($data->get('link', '#|*|')));

        return $data;
    }

    public function prepareExport($export, $data) {
        $export->addVisual($data->get('font'));
        $export->addVisual($data->get('style'));
        $export->addLightbox($data->get('link'));
    }

    public function prepareImport($import, $data) {
        $data->set('font', $import->fixSection($data->get('font')));
        $data->set('style', $import->fixSection($data->get('style')));
        $data->set('link', $import->fixLightbox($data->get('link')));

        return $data;
    }

    public function loadResources($renderable) {
        parent::loadResources($renderable);

        $renderable->addLess($this->getPath() . "/button.n2less", array(
            "sliderid" => $renderable->elementId
        ));
    }

    public function renderFields($form) {
        $settings = new N2Tab($form, 'item-button');

        new N2ElementText($settings, 'content', n2_('Label'), n2_('Button'), array(
            'style' => 'width:280px;'
        ));
        new N2ElementFont($settings, 'font', n2_('Font') . ' - ' . n2_('Button'), '', array(
            'rowClass'    => 'n2-hidden',
            'previewMode' => 'link',
            'set'         => 1100,
            'style'       => 'item_buttonstyle',
            'preview'     => '<div class="{fontClassName}" style="width:{nextend.activeLayer.prop(\'style\').width};"><a style="display:{$(\'#item_buttonfullwidth\').val() == 1 ? \'block\' : \'inline-block\'};" href="#" class="{styleClassName}" onclick="return false;">{$(\'#item_buttoncontent\').val();}</a></div>'
        ));
        new N2ElementStyle($settings, 'style', n2_('Style') . ' - ' . n2_('Button'), '', array(
            'rowClass'    => 'n2-hidden',
            'previewMode' => 'button',
            'set'         => 1100,
            'font'        => 'item_buttonfont',
            'preview'     => '<div class="{fontClassName}" style="width:{nextend.activeLayer.prop(\'style\').width};"><a style="display:{$(\'#item_buttonfullwidth\').val() == 1 ? \'block\' : \'inline-block\'};" href="#" class="{styleClassName}" onclick="return false;">{$(\'#item_buttoncontent\').val();}</a></div>'
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
        new N2ElementList($link, 'link-3', n2_('Rel'), '', array(
            'options' => array(
                ''           => '',
                'nofollow'   => 'nofollow',
                'noreferrer' => 'noreferrer',
                'author'     => 'author',
                'external'   => 'external',
                'help'       => 'help'
            )
        ));

        $ui = new N2ElementGroup($settings, 'item-button-ui');
        new N2ElementOnOff($ui, 'fullwidth', n2_('Full width'), 1);
        new N2ElementOnOff($ui, 'nowrap', n2_('No wrap'), 1);
    }
}

N2SmartSliderItemsFactory::addItem(new N2SSPluginItemFactoryButton);