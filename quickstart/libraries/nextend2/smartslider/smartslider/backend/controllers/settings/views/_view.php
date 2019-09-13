<?php

class N2SmartsliderBackendSettingsView extends N2ViewBase {

    public $xml;
    public $viewName = 'default';

    public function __set($name, $value) {
        if (!is_null($name)) {
            $this->$name = $value;
        }
    }

    public function _renderDefaultForm() {
        $this->viewName = 'default';
        $settingsModel  = new N2SmartsliderSettingsModel();
        $settingsModel->form();
        echo '<input name="namespace" value="default" type="hidden" />';
    }

    public function renderDefaultsForm() {

        $form = new N2Form();

        $fontTab = new N2Tab($form, 'font', n2_('Font'));
        new N2ElementToken($fontTab);
        $styleTab = new N2Tab($form, 'style', n2_('Style'));


        foreach (N2SmartSliderItemsFactory::getItems() as $item) {
            $item->globalDefaultItemFontAndStyle($fontTab, $styleTab);
        }

        $form->render('defaults');
    }

    public function renderFrameworkConfigurationForm() {


        $values = N2Settings::getAll();

        $form = new N2Form($this->appType);
        $form->loadArray($values);

        $options = new N2Tab($form, 'options', n2_('Options'));
        new N2ElementToken($options);

        new N2ElementOnOff($options, 'api-secondary', n2_('Secondary server'), 0);
        new N2ElementOnOff($options, 'protocol-relative', n2_('Use protocol-relative URL'), 1);
        new N2ElementOnOff($options, 'force-english-backend', n2_('Force english backend'), 0);
        new N2ElementOnOff($options, 'frontend-accessibility', n2_('Improved frontend accessibility'), 1);
        new N2ElementOnOff($options, 'show-joomla-admin-footer', n2_('Show Joomla admin footer'), 0);
    


        $javascript = new N2Tab($form, 'javascript', n2_('JavaScript'));
        new N2ElementOnOff($javascript, 'jquery', n2_('Load jQuery on frontend'), 1);
        new N2ElementOnOff($javascript, 'gsap', n2_('Load GSAP on frontend'), 1);
    
        new N2ElementOnOff($javascript, 'async', n2_('Async'), 0);
        new N2ElementOnOff($javascript, 'combine-js', n2_('Combine'), 0);
        new N2ElementText($javascript, 'scriptattributes', n2_('Script attributes'), '');

        new N2ElementRadio($javascript, 'javascript-inline', n2_('Slider\'s inline JavaScript'), 'head', array(
            'options' => array(
                'head' => n2_('Head'),
                'body' => n2_('Into the slider')
            )
        ));


        $css = new N2Tab($form, 'css', n2_('CSS'));
        new N2ElementRadio($css, 'css-mode', n2_('CSS mode'), 'normal', array(
            'options' => array(
                'normal' => n2_('Inline'),
                'inline' => n2_('Inline at head'),
                'async'  => n2_('Async'),
            )
        ));

        $request = new N2Tab($form, 'requests', n2_('API requests'));
        new N2ElementOnOff($request, 'curl', 'Curl', 1);
        new N2ElementOnOff($request, 'curl-clean-proxy', n2_('Clean Curl proxy'), 0);


        $form->render('global');

        N2JS::addFirstCode("
            new N2Classes.Form(
                'nextend-config',
                '" . $this->appType->router->createAjaxUrl(array(
                'settings/index'
            )) . "',
                " . json_encode($values) . "
            );
        ");
    }

    public function renderFontsConfigurationForm() {
        $values = N2Fonts::loadSettings();

        $form = new N2Form($this->appType);
        $form->loadArray($values);
        $form->loadArray($values['plugins']->toArray());


        $fonts = new N2Tab($form, 'fonts', n2_('Configuration'));
        new N2ElementToken($fonts);
        new N2ElementText($fonts, 'default-family', n2_('Default family'), '');
        new N2ElementTextarea($fonts, 'preset-families', n2_('Preset font families'), '', array(
            'fieldStyle' => 'height: 300px; width: 500px;'
        ));


        $fontServices = new N2TabRaw($form, 'font-services', false);
        new N2ElementFontServices($fontServices, 'font-services', '', 'google');

        $form->render('fonts');
    }

}