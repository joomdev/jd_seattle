<?php

N2Loader::import(array(
    'libraries.stylemanager.storage'
));

class N2StyleRenderer {

    public static $pre = '';
    public static $sets = array();
    public static $styles = array();

    /**
     * @var N2Style
     */
    public static $style;

    public static $mode;

    public static function preLoad($styleId) {
        if (intval($styleId) > 0) {
            $style = N2StorageSectionAdmin::getById($styleId, 'style');
            if ($style) {
                self::$sets[] = $style['referencekey'];
            }
        }
    }

    public static function render($style, $mode, $group, $pre = '') {

        $cssData = self::_render($style, $mode, $pre);
        if ($cssData) {
            N2CSS::addCode($cssData[1], $group);

            return $cssData[0];
        }

        return '';
    }

    public static function _render($style, $mode, $pre = '') {
        self::$pre = $pre;
        if (intval($style) > 0) {
            // Linked
            $style = N2StorageSectionAdmin::getById($style, 'style');
            if ($style) {
                if (is_string($style['value'])) {

                    $decoded = $style['value'];
                    if ($decoded[0] != '{') {
                        $decoded = n2_base64_decode($decoded);
                    }

                    $value = json_decode($decoded, true);
                } else {
                    $value = $style['value'];
                }
                $selector = 'n2-style-' . $style['id'] . '-' . $mode;

                self::$sets[] = $style['referencekey'];

                if (!isset(self::$styles[$style['id']])) {
                    self::$styles[$style['id']] = array(
                        $mode
                    );
                } else if (!in_array($mode, self::$styles[$style['id']])) {
                    self::$styles[$style['id']][] = $mode;
                }

                return array(
                    $selector . ' ',
                    self::renderStyle($mode, $pre, $selector, $value['data'])
                );
            }
        } else if ($style != '') {
            $decoded = $style;
            if ($decoded[0] != '{') {
                $decoded = n2_base64_decode($decoded);
            } else {
                $style = n2_base64_encode($decoded);
            }

            $value = json_decode($decoded, true);
            if ($value) {
                $selector = 'n2-style-' . md5($style) . '-' . $mode;

                return array(
                    $selector . ' ',
                    self::renderStyle($mode, $pre, $selector, $value['data'])
                );
            }
        }

        return false;
    }

    private static function renderStyle($mode, $pre, $selector, $tabs) {
        $search  = array(
            '@pre',
            '@selector'
        );
        $replace = array(
            $pre,
            '.' . $selector
        );
        $tabs[0] = array_merge(array(
            'backgroundcolor' => 'ffffff00',
            'opacity'         => 100,
            'padding'         => '0|*|0|*|0|*|0|*|px',
            'boxshadow'       => '0|*|0|*|0|*|0|*|000000ff',
            'border'          => '0|*|solid|*|000000ff',
            'borderradius'    => '0',
            'extra'           => '',
        ), $tabs[0]);

        for ($i = 0; $i < count(self::$mode[$mode]['tabs']); $i++) {
            if (!isset($tabs[$i])) {
                $tabs[$i] = array();
            }
        }

        foreach ($tabs AS $k => $tab) {
            $search[]  = '@tab' . $k;
            $replace[] = self::$style->style($tab);
        }

        $template = '';
        foreach (self::$mode[$mode]['selectors'] AS $s => $style) {
            if (!in_array($style, $search) || !empty($replace[array_search($style, $search)])) {
                $template .= $s . "{" . $style . "}";
            }
        }

        return str_replace($search, $replace, $template);
    }
}

$frontendAccessibility = intval(N2Settings::get('frontend-accessibility', 1));

N2StyleRenderer::$mode = array(
    '0'              => array(
        'id'            => '0',
        'label'         => n2_('Single'),
        'tabs'          => array(
            n2_('Text')
        ),
        'renderOptions' => array(
            'combined' => false
        ),
        'preview'       => '<div class="{styleClassName}">Lorem ipsum dolor sit amet, consectetur adipiscing elit, sed do eiusmod tempor incididunt ut labore et dolore magna aliqua. Ut enim ad minim veniam, quis nostrud exercitation ullamco laboris nisi ut aliquip ex ea commodo consequat. Duis aute irure dolor in reprehenderit in voluptate velit esse cillum dolore eu fugiat nulla pariatur. Excepteur sint occaecat cupidatat non proident, sunt in culpa qui officia deserunt mollit anim id est laborum.</div>',
        'selectors'     => array(
            '@pre@selector' => '@tab'
        )
    ),
    'simple'         => array(
        'id'            => 'simple',
        'label'         => n2_('Simple'),
        'tabs'          => array(
            n2_('Normal')
        ),
        'renderOptions' => array(
            'combined' => true
        ),
        'preview'       => '<div class="{styleClassName}" style="width: 200px; height:100px;"></div>',
        'selectors'     => array(
            '@pre@selector' => '@tab0'
        )
    ),
    'box'            => array(
        'id'            => 'box',
        'label'         => n2_('Box'),
        'tabs'          => array(
            n2_('Normal'),
            n2_('Hover')
        ),
        'renderOptions' => array(
            'combined' => true
        ),
        'preview'       => '<div class="{styleClassName}" style="width: 200px; height:100px;"></div>',
        'selectors'     => array(
            '@pre@selector'       => '@tab0',
            '@pre@selector:HOVER' => '@tab1'
        )
    ),
    'button'         => array(
        'id'            => 'button',
        'label'         => n2_('Button'),
        'tabs'          => array(
            n2_('Normal'),
            n2_('Hover')
        ),
        'renderOptions' => array(
            'combined' => true
        ),
        'preview'       => '<div><a style="display:inline-block; margin:20px;" class="{styleClassName}" href="#" onclick="return false;">Button</a></div>',
        'selectors'     => $frontendAccessibility ? array(
            '@pre@selector'                                                  => '@tab0',
            '@pre@selector:Hover, @pre@selector:ACTIVE, @pre@selector:FOCUS' => '@tab1'
        ) : array(
            '@pre@selector, @pre@selector:FOCUS'        => '@tab0',
            '@pre@selector:Hover, @pre@selector:ACTIVE' => '@tab1'
        )
    ),
    'heading'        => array(
        'id'            => 'heading',
        'label'         => n2_('Heading'),
        'tabs'          => array(
            n2_('Normal'),
            n2_('Hover')
        ),
        'renderOptions' => array(
            'combined' => true
        ),
        'preview'       => '<div class="{styleClassName}">Heading</div>',
        'selectors'     => $frontendAccessibility ? array(
            '@pre@selector'                                                  => '@tab0',
            '@pre@selector:Hover, @pre@selector:ACTIVE, @pre@selector:FOCUS' => '@tab1'
        ) : array(
            '@pre@selector, @pre@selector:FOCUS'        => '@tab0',
            '@pre@selector:Hover, @pre@selector:ACTIVE' => '@tab1'
        )
    ),
    'heading-active' => array(
        'id'            => 'heading-active',
        'label'         => n2_('Heading active'),
        'tabs'          => array(
            n2_('Normal'),
            n2_('Active')
        ),
        'renderOptions' => array(
            'combined' => true
        ),
        'preview'       => '<div class="{styleClassName}">Heading</div>',
        'selectors'     => array(
            '@pre@selector'           => '@tab0',
            '@pre@selector.n2-active' => '@tab1'
        )
    ),
    'dot'            => array(
        'id'            => 'dot',
        'label'         => n2_('Dot'),
        'tabs'          => array(
            n2_('Normal'),
            n2_('Active')
        ),
        'renderOptions' => array(
            'combined' => true
        ),
        'preview'       => '<div><div class="{styleClassName}" style="display: inline-block; margin: 3px;"></div><div class="{styleClassName} n2-active" style="display: inline-block; margin: 3px;"></div><div class="{styleClassName}" style="display: inline-block; margin: 3px;"></div></div>',
        'selectors'     => array(
            '@pre@selector'                                                     => '@tab0',
            '@pre@selector.n2-active, @pre@selector:HOVER, @pre@selector:FOCUS' => '@tab1'
        )
    ),
    'highlight'      => array(
        'id'            => 'highlight',
        'label'         => n2_('Highlight'),
        'tabs'          => array(
            n2_('Normal'),
            n2_('Highlight'),
            n2_('Hover')
        ),
        'renderOptions' => array(
            'combined' => true
        ),
        'preview'       => '<div class="{fontClassName}">' . n2_('Button') . '</div>',
        'selectors'     => $frontendAccessibility ? array(
            '@pre@selector'                                                                                                  => '@tab0',
            '@pre@selector .n2-highlighted'                                                                                  => '@tab1',
            '@pre@selector .n2-highlighted:HOVER, @pre@selector .n2-highlighted:ACTIVE, @pre@selector .n2-highlighted:FOCUS' => '@tab2'
        ) : array(
            '@pre@selector'                                                             => '@tab0',
            '@pre@selector .n2-highlighted, @pre@selector .n2-highlighted:FOCUS'        => '@tab1',
            '@pre@selector .n2-highlighted:HOVER, @pre@selector .n2-highlighted:ACTIVE' => '@tab2'
        )
    ),
);

N2Loader::import('libraries.image.color');

class N2Style {

    /**
     * @param string $tab
     *
     * @return string
     */
    public function style($tab) {
        $style = '';
        $extra = '';
        if (isset($tab['extra'])) {
            $extra = $tab['extra'];
            unset($tab['extra']);
        }
        foreach ($tab AS $k => $v) {
            $style .= $this->parse($k, $v);
        }
        $style .= $this->parse('extra', $extra);

        return $style;
    }

    /**
     * @param $property
     * @param $value
     *
     * @return mixed
     */
    public function parse($property, $value) {
        $fn = 'parse' . $property;

        return $this->$fn($value);
    }

    public function parseBackgroundColor($v) {
        $hex   = N2Color::hex82hex($v);
        $style = 'background: #' . $hex[0] . ';';
        if ($hex[1] != 'ff') {
            $rgba  = N2Color::hex2rgba($v);
            $style .= 'background: RGBA(' . $rgba[0] . ',' . $rgba[1] . ',' . $rgba[2] . ',' . round($rgba[3] / 127, 2) . ');';
        }

        return $style;
    }

    public function parseOpacity($v) {
        return 'opacity:' . (intval($v) / 100) . ';';
    }

    public function parsePadding($v) {
        $padding   = explode('|*|', $v);
        $unit      = array_pop($padding);
        $padding[] = '';

        return 'padding:' . implode($unit . ' ', $padding) . ';';
    }

    public function parseBoxShadow($v) {
        $boxShadow = explode('|*|', $v);

        if ($boxShadow[0] == '0' && $boxShadow[1] == '0' && $boxShadow[2] == '0' && $boxShadow[3] == '0') {
            return 'box-shadow: none;';
        } else {
            $rgba = N2Color::hex2rgba($boxShadow[4]);

            return 'box-shadow: ' . $boxShadow[0] . 'px ' . $boxShadow[1] . 'px ' . $boxShadow[2] . 'px ' . $boxShadow[3] . 'px RGBA(' . $rgba[0] . ',' . $rgba[1] . ',' . $rgba[2] . ',' . round($rgba[3] / 127, 2) . ');';
        }
    }

    public function parseBorder($v) {
        $border = explode('|*|', $v);
        $style  = 'border-width: ' . $border[0] . 'px;';
        $style  .= 'border-style: ' . $border[1] . ';';
        $rgba   = N2Color::hex2rgba($border[2]);
        $style  .= 'border-color: #' . substr($border[2], 0, 6) . "; border-color: RGBA(" . $rgba[0] . ',' . $rgba[1] . ',' . $rgba[2] . ',' . round($rgba[3] / 127, 2) . ');';

        return $style;
    }

    public function parseBorderRadius($v) {
        return 'border-radius:' . $v . 'px;';
    }

    public function parseExtra($v) {
        return $v;
    }
}

N2StyleRenderer::$style = new N2Style();