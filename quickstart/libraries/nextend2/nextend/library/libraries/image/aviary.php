<?php

class N2ImageAviary {

    private static $config = array(
        'public' => '',
        'secret' => ''
    );

    public static function init() {
        static $once = false;
        if ($once === false) {
            self::loadSettings();

            N2JS::addFirstCode('
            window.nextend.getFeatherEditor = function(){
                if(typeof window.nextend.featherEditor !== "undefined"){
                    return $.when(window.nextend.featherEditor);
                }
                var deferred = $.Deferred();

                $.getScript("https://dme0ih8comzn4.cloudfront.net/imaging/v1/editor.js").done(function(){
                    window.nextend.featherEditorHiRes = -1;
                    window.nextend.featherEditor = new Aviary.Feather({
                        apiKey: "' . self::$config['public'] . '",
                        encryptionMethod: "sha1",
                        maxSize: 1920,
                        displayImageSize: true,
                        onLoad: function(){
                            deferred.resolve();
                        },
                        onReady: function(){
                            if(window.nextend.featherEditorHiRes == -1){
                                AV.controlsWidgetInstance.serverMessaging.sendMessage({
                                    id: "avpw_auth_form",
                                    action: AV.controlsWidgetInstance.assetManager.getManifestURL(),
                                    method: "GET",
                                    dataType: "json",
                                    announcer: AV.build.asyncFeatherTargetAnnounce,
                                    origin: AV.build.asyncImgrecvBase,
                                    callback: function(response){
                                        window.nextend.featherEditorHiRes = false;
                                        for(var i = 0; i < response.permissions.length; i++){
                                            if(response.permissions[i] == "hires"){
                                                window.nextend.featherEditorHiRes = true;
                                                break;
                                            }
                                        }
                                    }
                                });
                            }
                        },
                        onError: function(error){
                            if(error.code == 8){
                                N2Classes.Notification.error("Aviary not set up. <a target=\"_blank\" href=\"' . N2Base::getApplication('system')->router->createUrl('settings/aviary') . '\">Click here to setup!</a>");
                            }else{
                                N2Classes.Notification.error(error.message);
                            }
                            if(typeof error.args !== "undefined" && typeof error.args[1] !== "undefined"){
                                N2Classes.Notification.error(error.args[1].Error);
                            }
                            window.nextend.featherEditor.close();
                            deferred.reject();
                        },
                        onSaveButtonClicked: function(){
                            if(window.nextend.featherEditorHiRes === true){
                                N2Classes.AjaxHelper.ajax({
                                    type: "POST",
                                    url: N2Classes.AjaxHelper.makeAjaxUrl(window.nextend.featherEditor.ajaxUrl, {
                                        nextendaction: "getHighResolutionAuth"
                                    }),
                                    dataType: "json"
                                })
                                    .done(function (response) {
                                        var auth = response.data.highResolutionAuth;
                                        window.nextend.featherEditor.updateConfig({
                                            salt: auth.salt,
                                            timestamp: auth.timestamp,
                                            signature: auth.signature
                                        });

                                        window.nextend.featherEditor.saveHiRes();
                                    });
                                return false;
                            }
                        }
                    });
                    window.nextend.featherEditor.ajaxUrl = "' . N2Base::getApplication('system')
                                                                      ->getApplicationType('backend')->router->createAjaxUrl(array('aviary/index')) . '";
                });
                return deferred;
            };
        ');
            $once = true;
        }


        if (empty(self::$config['public'])) return false;

        return true;
    }

    public static function getHighResolutionAuth() {
        self::loadSettings();
        $timestamp = time();
        $salt      = uniqid(mt_rand(), true);
        $signature = sha1(self::$config['public'] . self::$config['secret'] . $timestamp . $salt);

        return array(
            'timestamp' => $timestamp,
            'salt'      => $salt,
            'signature' => $signature
        );
    }

    public static function loadSettings() {
        static $inited;
        if (!$inited) {
            $inited = true;
            foreach (N2StorageSectionAdmin::getAll('system', 'aviary') AS $data) {
                self::$config[$data['referencekey']] = $data['value'];
            }
        }

        return self::$config;
    }

    public static function storeSettings($data) {
        if (is_array($data)) {
            foreach ($data as $key => $value) {
                if (isset(self::$config[$key])) {
                    self::$config[$data['referencekey']] = $data['value'];
                    N2StorageSectionAdmin::set('system', 'aviary', $key, $value, 1, 1);
                }
            }

            return true;
        }

        return false;
    }
}