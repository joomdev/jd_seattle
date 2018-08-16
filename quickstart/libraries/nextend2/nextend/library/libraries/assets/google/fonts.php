<?php

class N2AssetsGoogleFonts extends N2AssetsAbstract {

    public static $hasWebFontLoader = false;

    function addSubset($subset = 'latin') {
        if (!in_array($subset, $this->inline)) {
            $this->inline[] = $subset;
        }
    }

    function addFont($family, $style = '400') {
        $style = (string)$style;
        if (!isset($this->files[$family])) {
            $this->files[$family] = array();
        }
        if (!in_array($style, $this->files[$family])) {
            $this->files[$family][] = $style;
        }
    }

    public function loadFonts() {
        $familyQuery = array();
        if (count($this->files)) {
            foreach ($this->files AS $family => $styles) {
                if (count($styles)) {
                    $familyQuery[] = $family . ':' . implode(',', $styles);
                }
            }
        }
        if (empty($familyQuery)) {
            return false;
        }
        $subsets                              = array_unique($this->inline);
        $familyQuery[count($familyQuery) - 1] .= ':' . implode(',', $subsets);

        self::$hasWebFontLoader = true;
        N2JS::addStaticGroup(N2LIBRARYASSETS . "/dist/nextend-webfontloader.min.js", 'nextend-webfontloader');
    

        N2JS::addGlobalInline("
        nextend.fontsLoaded = false;
        nextend.fontsLoadedActive = function () {nextend.fontsLoaded = true;};
        var fontData = {
            google: {
                families: " . json_encode($familyQuery) . "
            },
            active: function(){nextend.fontsLoadedActive()},
            inactive: function(){nextend.fontsLoadedActive()}
        };
        if(typeof WebFontConfig !== 'undefined'){
            var _WebFontConfig = WebFontConfig;
            for(var k in WebFontConfig){
                if(k == 'active'){
                  fontData.active = function(){nextend.fontsLoadedActive();_WebFontConfig.active();}
                }else if(k == 'inactive'){
                  fontData.inactive = function(){nextend.fontsLoadedActive();_WebFontConfig.inactive();}
                }else if(k == 'google'){
                    if(typeof WebFontConfig.google.families !== 'undefined'){
                        for(var i = 0; i < WebFontConfig.google.families.length; i++){
                            fontData.google.families.push(WebFontConfig.google.families[i]);
                        }
                    }
                }else{
                    fontData[k] = WebFontConfig[k];
                }
            }
        }
        if(typeof WebFont === 'undefined'){
            window.WebFontConfig = fontData;
        }else{
            WebFont.load(fontData);
        }");

        N2JS::addFirstCode("
        nextend.fontsDeferred = $.Deferred();
        if(nextend.fontsLoaded){
            nextend.fontsDeferred.resolve();
        }else{
            nextend.fontsLoadedActive = function () {
                nextend.fontsLoaded = true;
                nextend.fontsDeferred.resolve();
            };
            var intercalCounter = 0;
            nextend.fontInterval = setInterval(function(){
                if(intercalCounter > 3 || document.documentElement.className.indexOf('wf-active') !== -1){
                    nextend.fontsLoadedActive();
                    clearInterval(nextend.fontInterval);
                }
                intercalCounter++;
            }, 1000);
        }", true);
    }
}