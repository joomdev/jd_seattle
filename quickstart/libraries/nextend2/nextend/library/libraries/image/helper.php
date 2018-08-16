<?php


class N2ImageHelperAbstract {

    public static $imagePaths = array();
    public static $imageUrls = array();
    public static $siteKeywords = array();
    public static $protocolRelative = 1;

    public static function init() {
        $parameters = array(
            'siteKeywords'     => self::$siteKeywords,
            'imageUrls'        => self::$imageUrls,
            'protocolRelative' => intval(self::$protocolRelative)
        );

        $parameters['placeholderImage']         = '$system$/images/placeholder/image.png';
        $parameters['placeholderRepeatedImage'] = '$system$/images/placeholder/image.png';

        N2JS::addFirstCode('new N2Classes.ImageHelper(' . json_encode($parameters) . ', ' . N2ImageHelper::getLightboxFunction() . ',' . N2ImageHelper::getLightboxMultipleFunction() . ', ' . N2ImageHelper::getLightboxFoldersFunction() . ');');
    }

    public static function dynamic($image) {
        $_image = self::protocolRelative($image);
        foreach (self::$imageUrls AS $i => $imageUrl) {
            if (strpos($_image, $imageUrl) === 0) {
                $image = self::$siteKeywords[$i] . substr($_image, strlen($imageUrl));
                break;
            }
        }

        return $image;
    }

    public static function fixed($image, $needPath = false) {
        foreach (self::$imageUrls AS $i => $imageUrl) {
            if (strpos($image, self::$siteKeywords[$i]) === 0) {
                $image = ($needPath ? self::$imagePaths[$i] : $imageUrl) . substr($image, strlen(self::$siteKeywords[$i]));
                break;
            }
        }

        return $image;
    }

    public static function addKeyword($keyword, $path, $url) {
        array_unshift(self::$siteKeywords, $keyword . '/');
        array_unshift(self::$imagePaths, rtrim($path, '/') . '/');
        if (N2Settings::get('protocol-relative', '1')) {
            $url = self::protocolRelative($url);
        }
        array_unshift(self::$imageUrls, rtrim($url, '/') . '/');
    }

    public static function protocolRelative($url) {
        if (self::$protocolRelative) {
            return preg_replace('/^http(s)?:\/\//', '//', $url);
        }

        return $url;
    }

    public static function export() {
        $def = array();
        for ($i = 0; $i < count(self::$siteKeywords); $i++) {
            $def[self::$siteKeywords[$i]] = self::$imageUrls[$i];
        }

        return $def;
    }

    public static function getLightboxFoldersFunction() {
        return 'function (callback) {
            this.joomlaModal = new N2Classes.NextendModal({
                zero: {
                    fit: true,
                    size: [
                        980,
                        680
                    ],
                    title: "' . n2_('Images') . '",
                    controlsClass: "n2-modal-controls-side",
                    controls: [\'<a href="#" class="n2-button n2-button-normal n2-button-l n2-radius-s n2-button-green n2-uc n2-h4">' . n2_('Select') . '</a>\'],
                    content: \'\',
                    fn: {
                        show: function () {
                            this.content.append(nextend.browse.getNode("folder"));
                            this.controls.find(".n2-button-green")
                                .on("click", $.proxy(function (e) {
                                    e.preventDefault();
                                    this.hide(e);
                                    callback(nextend.browse.getCurrentFolder());
                                }, this));
                        }
                    }
                }
            }, true);
        }';
    }

    public static function SVGToBase64($image) {
        $ext = pathinfo($image, PATHINFO_EXTENSION);
        if (substr($image, 0, 1) == '$' && $ext == 'svg') {
            return 'data:image/svg+xml;base64,' . n2_base64_encode(N2Filesystem::readFile(N2ImageHelper::fixed($image, true)));
        }

        return N2ImageHelper::fixed($image);
    }

    public static function readSVG($image) {
        return N2Filesystem::readFile(N2ImageHelper::fixed($image, true));
    }

    public static function onImageUploaded($filename) {
    }
}

N2Loader::import('libraries.image.helper', 'platform');
N2ImageHelper::$protocolRelative = intval(N2Settings::get('protocol-relative', '1'));
N2ImageHelper::addKeyword('$', N2Filesystem::getBasePath(), N2Uri::getBaseUri());