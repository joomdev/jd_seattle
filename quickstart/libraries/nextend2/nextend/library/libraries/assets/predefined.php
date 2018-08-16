<?php

class N2AssetsPredefined {

    public static function backend($force = false) {
        static $once;
        if ($once != null && !$force) {
            return;
        }
        $once   = true;
        $family = n2_x('Montserrat', 'Default Google font family for admin');
        foreach (explode(',', n2_x('latin', 'Default Google font charset for admin')) AS $subset) {
            N2GoogleFonts::addSubset($subset);
        }
        N2GoogleFonts::addFont($family);

        N2CSS::addInline('.n2,html[dir="rtl"] .n2,.n2 td,.n2 th,.n2 select, .n2 textarea, .n2 input{font-family: "' . $family . '", Arial, sans-serif;}');
        N2CSS::addStaticGroup(N2LIBRARYASSETS . '/dist/nextend-backend.min.css', 'nextend-backend');
    

        N2Localization::addJS(array(
            'Cancel',
            'Delete',
            'Delete and never ask for confirmation again',
            'Are you sure you want to delete?',
            'Documentation'
        ));
        N2JS::addStaticGroup(N2LIBRARYASSETS . '/dist/nextend-backend.min.js', 'nextend-backend');
    


        N2Base::getApplication('system')->info->assetsBackend();
        N2JS::addFirstCode("N2R(['AjaxHelper'],function(){N2Classes.AjaxHelper.addAjaxArray(" . json_encode(N2Form::tokenizeUrl()) . ");});");

        N2Fonts::onFontManagerLoadBackend();
    }

    public static function frontend($force = false) {
        static $once;
        if ($once != null && !$force) {
            return;
        }
        $once = true;
        N2AssetsManager::getInstance();
        if (N2Platform::$isAdmin) {
            N2JS::addGlobalInline('window.N2PRO=' . N2PRO . ';');
            N2JS::addGlobalInline('window.N2GSAP=' . N2GSAP . ';');
            N2JS::addGlobalInline('window.N2PLATFORM="' . N2Platform::getPlatform() . '";');
        }
    

        N2JS::addGlobalInline('(function(){var N=this;N.N2_=N.N2_||{r:[],d:[]},N.N2R=N.N2R||function(){N.N2_.r.push(arguments)},N.N2D=N.N2D||function(){N.N2_.d.push(arguments)}}).call(window);');
        N2JS::addGlobalInline('if(!window.n2jQuery){window.n2jQuery={ready:function(cb){console.error(\'n2jQuery will be deprecated!\');N2R([\'$\'],cb);}}}');

        N2JS::addGlobalInline('window.nextend={localization: {}, ready: function(cb){console.error(\'nextend.ready will be deprecated!\');N2R(\'documentReady\', function($){cb.call(window,$)})}};');

        N2JS::jQuery($force);

        self::animation($force);
        N2JS::addStaticGroup(N2LIBRARYASSETS . "/dist/nextend-frontend.min.js", 'nextend-frontend');
    

        N2Loader::import('libraries.fonts.fonts');
        N2Fonts::onFontManagerLoad($force);
    }

    private static function form($force = false) {
        static $once;
        if ($once != null && !$force) {
            return;
        }
        $once = true;

        N2JS::addFiles(N2LIBRARYASSETS . "/admin/js", array(
            'form.js',
            'element.js'
        ), 'nextend-backend');

        N2Localization::addJS('The changes you made will be lost if you navigate away from this page.');


        N2JS::addFiles(N2LIBRARYASSETS . "/admin/js/element", array(
            'text.js'
        ), 'nextend-backend');

        foreach (glob(N2LIBRARYASSETS . "/admin/js/element/*.js") AS $file) {
            N2JS::addFile($file, 'nextend-backend');
        }
    }

    private static function animation($force = false) {
        static $once;
        if ($once != null && !$force) {
            return;
        }
        $once = true;

        if (N2Pluggable::hasAction('animationFramework')) {
            N2Pluggable::doAction('animationFramework');
        } else {
            if (N2Settings::get('gsap') || N2Platform::$isAdmin) {
                N2JS::addStaticGroup(N2LIBRARYASSETS . "/dist/nextend-gsap.min.js", 'nextend-gsap');
            
            } else {
                N2JS::addGlobalInline('window.NextendGSAPFallback=' . json_encode(N2Uri::pathToUri(N2LIBRARYASSETS . "/dist/nextend-gsap.min.js")) . ';');
                N2JS::addInline(N2Filesystem::readFile(N2LIBRARYASSETS . "/dist/nextend-gsap-external.min.js"));
            
            }
        }
    }

    public static function custom_animation_framework() {
    }

    public static function loadLiteBox() {
    }
}