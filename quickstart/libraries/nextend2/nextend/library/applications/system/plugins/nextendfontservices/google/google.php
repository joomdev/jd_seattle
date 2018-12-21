<?php

class N2SystemPluginFontServiceGoogle extends N2FontServiceAbstract {

    protected $name = 'google';

    /*
jQuery.getJSON('https://www.googleapis.com/webfonts/v1/webfonts?sort=alpha&key=AIzaSyBIzBtder0-ef5a6kX-Ri9IfzVwFu21PGw').done(function(data){
var f = [];
for(var i = 0; i < data.items.length; i++){
f.push(data.items[i].family);
}
console.log(JSON.stringify(f));
});
     */
    private static $fonts = array();

    private static $styles = array();
    private static $subsets = array();

    public function __construct() {
        $lines = file(dirname(__FILE__) . '/families.csv', FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES);
        for ($i = 0; $i < count($lines); $i++) {
            self::$fonts[strtolower($lines[$i])] = $lines[$i];
        }
        self::$fonts['droid sans']  = 'Noto Sans';
        self::$fonts['droid serif'] = 'Noto Serif';
    }

    public function getLabel() {
        return 'Google';
    }

    /**
     * @param N2Form $form
     */
    public function renderFields($form) {

        $googleFonts = new N2Tab($form, 'google-fonts', false);

        $enabledGroup = new N2ElementGroup($googleFonts, 'google-font-enabled', n2_('Enabled'));
        new N2ElementOnOff($enabledGroup, 'google-enabled', n2_('Frontend'), 1);
        new N2ElementOnOff($enabledGroup, 'google-enabled-backend', n2_('Backend'), 1);

        $styleGroup = new N2ElementGroup($googleFonts, 'google-font-style', n2_('Style'));
        new N2ElementOnOff($styleGroup, 'google-style-100', '100', 0);
        new N2ElementOnOff($styleGroup, 'google-style-100italic', '100 '.n2_x('Italic', "Font style"), 0);
        new N2ElementOnOff($styleGroup, 'google-style-200', '200', 0);
        new N2ElementOnOff($styleGroup, 'google-style-200italic', '200 '.n2_x('Italic', "Font style"), 0);
        new N2ElementOnOff($styleGroup, 'google-style-300', '300', 1);
        new N2ElementOnOff($styleGroup, 'google-style-300italic', '300 '.n2_x('Italic', "Font style"), 0);
        new N2ElementOnOff($styleGroup, 'google-style-400', n2_('Normal'), 1);
        new N2ElementOnOff($styleGroup, 'google-style-400italic', n2_('Normal').' '.n2_x('Italic', "Font style"), 0);
        new N2ElementOnOff($styleGroup, 'google-style-500', '500', 0);
        new N2ElementOnOff($styleGroup, 'google-style-500italic', '500 '.n2_x('Italic', "Font style"), 0);
        new N2ElementOnOff($styleGroup, 'google-style-600', '600', 0);
        new N2ElementOnOff($styleGroup, 'google-style-600italic', '600 '.n2_x('Italic', "Font style"), 0);
        new N2ElementOnOff($styleGroup, 'google-style-700', '700', 0);
        new N2ElementOnOff($styleGroup, 'google-style-700italic', '700 '.n2_x('Italic', "Font style"), 0);
        new N2ElementOnOff($styleGroup, 'google-style-800', '800', 0);
        new N2ElementOnOff($styleGroup, 'google-style-800italic', '800 '.n2_x('Italic', "Font style"), 0);
        new N2ElementOnOff($styleGroup, 'google-style-900', '900', 0);
        new N2ElementOnOff($styleGroup, 'google-style-900italic', '900 '.n2_x('Italic', "Font style"), 0);


        $characterSet = new N2ElementGroup($googleFonts, 'google-font-character-set', n2_('Character set'));
        new N2ElementOnOff($characterSet, 'google-set-latin', n2_x('Latin', "Character set"), 1);
        new N2ElementOnOff($characterSet, 'google-set-latin-ext', n2_x('Latin Extended', "Character set"), 0);
        new N2ElementOnOff($characterSet, 'google-set-greek', n2_x('Greek', "Character set"), 0);
        new N2ElementOnOff($characterSet, 'google-set-greek-ext', n2_x('Greek Extended', "Character set"), 0);
        new N2ElementOnOff($characterSet, 'google-set-cyrillic', n2_x('Cyrillic', "Character set"), 0);
        new N2ElementOnOff($characterSet, 'google-set-devanagari', n2_x('Devanagari', "Character set"), 0);
        new N2ElementOnOff($characterSet, 'google-set-arabic', n2_x('Arabic', "Character set"), 0);
        new N2ElementOnOff($characterSet, 'google-set-khmer', n2_x('Khmer', "Character set"), 0);
        new N2ElementOnOff($characterSet, 'google-set-telugu', n2_x('Telugu', "Character set"), 0);
        new N2ElementOnOff($characterSet, 'google-set-vietnamese', n2_x('Vietnamese', "Character set"), 0);
    }

    public static function getDefaults() {
        $defaults  = array();
        $fontsSets = explode(',', n2_x('latin', 'Default font sets'));
        for ($i = 0; $i < count($fontsSets); $i++) {
            $fontsSets[$i] = 'google-set-' . $fontsSets[$i];
        }
        $defaults += array_fill_keys($fontsSets, 1);

        return $defaults;
    }

    public function getPath() {
        return dirname(__FILE__) . DIRECTORY_SEPARATOR . 'google' . DIRECTORY_SEPARATOR;
    }

    public function onFontManagerLoad($force = false) {
        static $loaded;
        if (!$loaded || $force) {
            $loaded     = true;
            $settings   = N2Fonts::loadSettings();
            $parameters = $settings['plugins'];

            $parameters->fillDefault(self::getDefaults());

            if ((!N2Platform::$isAdmin && $parameters->get('google-enabled', 1)) || (N2Platform::$isAdmin && $parameters->get('google-enabled-backend', 1))) {
                N2GoogleFonts::$enabled = 1;

                for ($i = 100; $i < 1000; $i += 100) {
                    $this->addStyle($parameters, $i);
                    $this->addStyle($parameters, $i . 'italic');
                }
                if (empty(self::$styles)) {
                    self::$styles[] = '300';
                    self::$styles[] = '400';
                }

                $this->addSubset($parameters, 'latin');
                $this->addSubset($parameters, 'latin-ext');
                $this->addSubset($parameters, 'greek');
                $this->addSubset($parameters, 'greek-ext');
                $this->addSubset($parameters, 'cyrillic');
                $this->addSubset($parameters, 'devanagari');
                $this->addSubset($parameters, 'arabic');
                $this->addSubset($parameters, 'khmer');
                $this->addSubset($parameters, 'telugu');
                $this->addSubset($parameters, 'vietnamese');
                if (empty(self::$subsets)) {
                    self::$subsets[] = 'latin';
                }
                foreach (self::$subsets as $subset) {
                    N2GoogleFonts::addSubset($subset);
                }
                N2Pluggable::addAction('fontFamily', array(
                    $this,
                    'onFontFamily'
                ));
            }
        }
    }

    public function onFontManagerLoadBackend() {
        $settings   = N2Fonts::loadSettings();
        $parameters = $settings['plugins'];

        $parameters->fillDefault(self::getDefaults());

        if ($parameters->get('google-enabled-backend', 1)) {
            N2JS::addInline('new N2Classes.NextendFontServiceGoogle("' . implode(',', self::$styles) . '","' . implode(',', self::$subsets) . '", ' . json_encode(self::$fonts) . ');');
        }
    }

    function addStyle($parameters, $weight) {
        if ($parameters->get('google-style-' . $weight, 0)) {
            self::$styles[] = $weight;
        }
    }

    function addSubset($parameters, $subset) {
        if ($parameters->get('google-set-' . $subset, 0)) {
            self::$subsets[] = $subset;
        }
    }

    function onFontFamily($family) {
        $familyLower = strtolower($family);
        if (isset(self::$fonts[$familyLower])) {
            foreach (self::$styles AS $style) {
                N2GoogleFonts::addFont(self::$fonts[$familyLower], $style);
            }

            return self::$fonts[$familyLower];
        }

        return $family;
    }


}

N2Fonts::addFontService(new N2SystemPluginFontServiceGoogle());