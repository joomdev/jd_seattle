<?php

/**
 * Class N2Html
 */
class N2Html {

    public static $closeSingleTags = true;
    /**
     * @var boolean whether to render special attributes value. Defaults to true. Can be set to false for HTML5.
     * @since 1.1.13
     */
    public static $renderSpecialAttributesValue = true;


    /**
     * Decodes special HTML entities back to the corresponding characters.
     * This is the opposite of {@link encode()}.
     *
     * @param string $text data to be decoded
     *
     * @return string the decoded data
     * @see   http://www.php.net/manual/en/function.htmlspecialchars-decode.php
     * @since 1.1.8
     */
    public static function decode($text) {
        return htmlspecialchars_decode($text, ENT_QUOTES);
    }

    /**
     * Generates an HTML element.
     *
     * @param string  $tag         the tag name
     * @param array   $htmlOptions the element attributes. The values will be HTML-encoded using {@link encode()}.
     *                             If an 'encode' attribute is given and its value is false,
     *                             the rest of the attribute values will NOT be HTML-encoded.
     *                             Since version 1.1.5, attributes whose value is null will not be rendered.
     * @param mixed   $content     the content to be enclosed between open and close element tags. It will not be HTML-encoded.
     *                             If false, it means there is no body content.
     * @param boolean $closeTag    whether to generate the close tag.
     *
     * @return string the generated HTML element tag
     */
    public static function tag($tag, $htmlOptions = array(), $content = "", $closeTag = true) {
        $html = '<' . $tag . self::renderAttributes($htmlOptions);
        if ($content === false) return $closeTag && self::$closeSingleTags ? $html . ' />' : $html . '>'; else
            return $closeTag ? $html . '>' . $content . '</' . $tag . '>' : $html . '>' . $content;
    }

    /**
     * Generates an open HTML element.
     *
     * @param string $tag         the tag name
     * @param array  $htmlOptions the element attributes. The values will be HTML-encoded using {@link encode()}.
     *                            If an 'encode' attribute is given and its value is false,
     *                            the rest of the attribute values will NOT be HTML-encoded.
     *                            Since version 1.1.5, attributes whose value is null will not be rendered.
     *
     * @return string the generated HTML element tag
     */
    public static function openTag($tag, $htmlOptions = array()) {
        return '<' . $tag . self::renderAttributes($htmlOptions) . '>';
    }

    /**
     * Generates a close HTML element.
     *
     * @param string $tag the tag name
     *
     * @return string the generated HTML element tag
     */
    public static function closeTag($tag) {
        return '</' . $tag . '>';
    }

    /**
     * Generates an image tag.
     *
     * @param string $src         the image URL
     * @param string $alt         the alternative text display
     * @param array  $htmlOptions additional HTML attributes (see {@link tag}).
     *
     * @return string the generated image tag
     */
    public static function image($src, $alt = '', $htmlOptions = array()) {
        $htmlOptions['src'] = $src;
        $htmlOptions['alt'] = $alt;

        return self::tag('img', $htmlOptions, false);
    }

    /**
     * Renders the HTML tag attributes.
     * Since version 1.1.5, attributes whose value is null will not be rendered.
     * Special attributes, such as 'checked', 'disabled', 'readonly', will be rendered
     * properly based on their corresponding boolean value.
     *
     * @param array $htmlOptions attributes to be rendered
     *
     * @return string the rendering result
     */
    public static function renderAttributes($htmlOptions = array()) {
        static $specialAttributes = array(
            'autofocus'          => 1,
            'autoplay'           => 1,
            'controls'           => 1,
            'declare'            => 1,
            'default'            => 1,
            'disabled'           => 1,
            'ismap'              => 1,
            'loop'               => 1,
            'muted'              => 1,
            'playsinline'        => 1,
            'webkit-playsinline' => 1,
            'nohref'             => 1,
            'noresize'           => 1,
            'novalidate'         => 1,
            'open'               => 1,
            'reversed'           => 1,
            'scoped'             => 1,
            'seamless'           => 1,
            'selected'           => 1,
            'typemustmatch'      => 1,
            'lazyload'           => 1,
        ), $specialAttributesNoValue = array(
            'defer' => 1,
            'async' => 1
        );

        if ($htmlOptions === array()) return '';

        $html = '';
        if (isset($htmlOptions['encode'])) {
            $raw = !$htmlOptions['encode'];
            unset($htmlOptions['encode']);
        } else
            $raw = false;

        foreach ($htmlOptions as $name => $value) {
            if (isset($specialAttributes[$name])) {
                if ($value) {
                    $html .= ' ' . $name;
                    if (self::$renderSpecialAttributesValue) $html .= '="' . $name . '"';
                }
            } else if (isset($specialAttributesNoValue[$name])) {
                $html .= ' ' . $name;
            } else if ($value !== null) $html .= ' ' . $name . '="' . ($raw ? $value : self::encode($value)) . '"';
        }

        return $html;
    }

    /**
     * @param $text
     *
     * @return string
     */
    public static function encode($text) {
        return htmlspecialchars($text, ENT_QUOTES, 'UTF-8');
    }

    public static function link($name, $url, $htmlOptions = array()) {
        $htmlOptions["href"] = $url;
        //$htmlOptions["encode"] = false;

        $url = self::openTag("a", $htmlOptions);
        if (isset($htmlOptions["encode"]) && $htmlOptions["encode"]) {
            $url .= self::encode($name);
        } else {
            $url .= $name;
        }

        $url .= self::closeTag("a");

        return $url;
    }

    /**
     * Insert stylesheet
     *
     * @param string $script
     * @param bool   $file
     * @param array  $scriptOptions
     *
     * @return string
     */
    public static function style($script, $file = false, $scriptOptions = array()) {
        if ($file) {
            $options = array(
                "rel"  => "stylesheet",
                "type" => "text/css",
                "href" => $script
            );
            $options = array_merge($options, $scriptOptions);

            return N2Html::tag('link', $options, false);
        }

        return N2Html::tag("style", $scriptOptions + array(
                "type" => "text/css"
            ), $script);
    }

    /**
     * Insert script
     *
     * @param string $script
     * @param bool   $file
     *
     * @return string
     */
    public static function script($script, $file = false) {
        if ($file) {
            return N2Html::tag('script', array(
                    'type' => 'text/javascript',
                    'src'  => $script
                ) + self::getScriptAttributes(), '');
        }

        return self::tag('script', array(
            'type'   => 'text/javascript',
            'encode' => false
        ), $script);
    }

    public static function scriptFile($script, $attributes = array()) {
        return N2Html::tag('script', array(
                'type' => 'text/javascript',
                'src'  => $script
            ) + self::getScriptAttributes() + $attributes, '');
    }

    public static function clear() {
        return self::tag("div", array("class" => "n2-clear"), "");
    }

    private static function getScriptAttributes() {
        static $attributes = null;
        if ($attributes === null) {
            if (class_exists('N2Settings', false)) {
                $value       = trim(html_entity_decode(strip_tags(N2Settings::get('scriptattributes', ''))));
                $_attributes = explode(' ', str_replace('\'', "", str_replace("\"", "", $value)));
                if (!empty($value) && !empty($_attributes)) {
                    foreach ($_attributes AS $attr) {
                        if (strpos($attr, '=') !== false) {
                            $atts = explode("=", $attr);
                            if (count($atts) <= 2) {
                                $attributes[$atts[0]] = $atts[1];
                            } else {
                                $attributes[$attr] = $attr;
                            }
                        } else {
                            $attributes[$attr] = $attr;
                        }
                    }
                } else {
                    $attributes = array();
                }
            } else {
                return array();
            }
        }

        return $attributes;
    }

    public static function topBar($options = array()) {
        static $params = array(
            'menu'         => array(),
            'actions'      => array(),
            'snapClass'    => 'n2-main-top-bar',
            'fixTo'        => true,
            'expert'       => true,
            'notification' => true,
            'hideSidebar'  => false,
            'back'         => false,
            'middle'       => ''
        );

        $options = array_merge($params, $options);

        if (!$options['fixTo']) {
            $options['snapClass'] = '';
        }

        if (!is_array($options['actions'])) {
            $options['actions'] = array();
        }
        if (!N2Base::$currentApplicationType->app->hasExpertMode()) {
            $options['expert'] = false;
        }

        extract($options);
        include(dirname(__FILE__) . '/fragments/topbar.phtml');

    }

    public static function definitionList($options = array()) {
        static $params = array(
            'class' => 'n2-definition-list',
        );
        $options = array_merge($params, $options);
        extract($options);

        include(dirname(__FILE__) . '/fragments/definitionlist.phtml');
    }

    public static function buttonMenu($options = array()) {
        static $params = array(
            'content' => '',
        ), $init;

        $options = array_merge($params, $options);
        extract($options);

        include(dirname(__FILE__) . '/fragments/buttonmenu.phtml');

        if (!$init) {
            N2JS::addInline('$(".n2-button-menu-open").n2opener();');
            $init = true;
        }
    }

    public static function ulList($options = array()) {
        static $params = array(
            'ul' => array(
                'htmlOptions' => '',
                'orderable'   => false,
                'link'        => '',
                'iconclass'   => '',
                'title'       => '',
                'actions'     => array(),
                'id'          => false
            )
        );

        $options = array_merge($params, $options);
        extract($options);

        include(dirname(__FILE__) . '/fragments/list.phtml');
    }

    public static function nav($options = array()) {
        static $params = array(
            'logoUrl'      => false,
            'logoImageUrl' => false,
            'views'        => array(),
            'actions'      => array()
        );

        $options = array_merge($params, $options);
        extract($options);

        include(dirname(__FILE__) . '/fragments/nav.phtml');
    }

    private static function addClass(&$a, $class) {
        if (empty($a['class'])) {
            $a['class'] = '';
        }
        $a['class'] .= ' ' . $class;
    }

    public static function box($options = array()) {
        static $params = array(
            'attributes'         => array(),
            'center'             => null,
            'centerAttributes'   => array(),
            'lt'                 => null,
            'rt'                 => null,
            'lb'                 => null,
            'rb'                 => null,
            'ltAttributes'       => array(),
            'rtAttributes'       => array(),
            'lbAttributes'       => array(),
            'rbAttributes'       => array(),
            'overlay'            => false,
            'placeholderAlign'   => '',
            'placeholderContent' => ''
        );

        $options = array_merge($params, $options);

        self::addClass($options['attributes'], 'n2-box');
        self::addClass($options['centerAttributes'], 'n2-box-center');

        self::addClass($options['ltAttributes'], 'n2-box-lt');
        self::addClass($options['rtAttributes'], 'n2-box-rt');
        self::addClass($options['lbAttributes'], 'n2-box-lb');
        self::addClass($options['rbAttributes'], 'n2-box-rb');

        extract($options);

        include(dirname(__FILE__) . '/fragments/box.phtml');
    }

    public static function heading($options = array()) {
        static $params = array(
            'title'     => '',
            'menu'      => array(),
            'actions'   => array(),
            'snap'      => false,
            'snapClass' => ''
        );

        $options = array_merge($params, $options);

        extract($options);

        include(dirname(__FILE__) . '/fragments/heading.phtml');
    }

    public static function banner($options = array()) {
        static $params = array(
            'id'          => '',
            'image'       => '',
            'imageLink'   => '',
            'title'       => '',
            'description' => '',
            'buttons'     => array()
        );

        $options = array_merge($params, $options);

        extract($options);

        include(dirname(__FILE__) . '/fragments/banner.phtml');
    }

    /**
     * @param array $array1
     * @param array $array2 [optional]
     * @param array $_      [optional]
     *
     * @return array the resulting array.
     * @since 4.0
     * @since 5.0
     */
    public static function mergeAttributes($array1, $array2 = null, $_ = null) {
        $arguments = func_get_args();
        $target    = array_shift($arguments);
        foreach ($arguments AS $array) {
            if (isset($array['style'])) {
                if (!isset($target['style'])) $target['style'] = '';
                $target['style'] .= $array['style'];
                unset($array['style']);
            }
            if (isset($array['class'])) {
                if (!isset($target['class'])) $target['class'] = '';
                $target['class'] .= ' ' . $array['class'];
                unset($array['class']);
            }

            $target = array_merge($target, $array);
        }

        return $target;
    }

    public static function addExcludeLazyLoadAttributes($target) {

        return self::mergeAttributes($target, self::getExcludeLazyLoadAttributes());
    }

    public static function getExcludeLazyLoadAttributes() {
        static $attrs;
        if ($attrs === null) {
            $attrs = array(
                'data-no-lazy' => 1,
                'data-hack'    => 'data-lazy-src'
            );

            if (class_exists('\FlorianBrinkmann\LazyLoadResponsiveImages\Plugin', false)) {
                $attrs['data-no-lazyload'] = 1;
            }

            if (function_exists('thb_lazy_images_filter') || defined('WP_SMUSH_VERSION')) {
                $attrs['class'] = 'no-lazyload';
            }
        }

        return $attrs;
    }
}