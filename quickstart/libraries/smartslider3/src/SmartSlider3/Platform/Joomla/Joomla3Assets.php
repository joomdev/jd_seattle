<?php


namespace Nextend\SmartSlider3\Platform\Joomla;


use JDocumentHTML;
use JFactory;

class Joomla3Assets {

    /**
     * @var JDocumentHTML
     */
    private $document;

    private $original = array();
    private $updates = array();

    public function __construct() {

        $this->document = JFactory::getDocument();

        $this->original['_styleSheets'] = $this->document->_styleSheets;
        $this->original['_style']       = $this->document->_style;

        $this->original['_scripts'] = $this->document->_scripts;
        $this->original['_script']  = $this->document->_script;

        $this->document->_style  = array();
        $this->document->_script = array();
    }

    public function process() {

        $this->updates['_styleSheets'] = array_diff_key($this->document->_styleSheets, $this->original['_styleSheets']);
        $this->updates['_style']       = $this->document->_style;

        $this->updates['_scripts'] = array_diff_key($this->document->_scripts, $this->original['_scripts']);
        $this->updates['_script']  = $this->document->_script;


        $this->document->_style  = $this->original['_style'];
        $this->document->_script = $this->original['_script'];
    }

    /**
     * Based on JDocumentRendererHead
     *
     * @return string
     */
    public function renderHead() {

        if (get_class($this->document) === 'Joomla\CMS\Document\HtmlDocument') {
            $lnEnd        = $this->document->_getLineEnd();
            $tab          = $this->document->_getTab();
            $tagEnd       = ' />';
            $buffer       = '';
            $mediaVersion = $this->document->getMediaVersion();

            $defaultCssMimes = array('text/css');

            // Generate stylesheet links
            foreach ($this->updates['_styleSheets'] as $src => $attribs) {
                // Check if stylesheet uses IE conditional statements.
                $conditional = isset($attribs['options']) && isset($attribs['options']['conditional']) ? $attribs['options']['conditional'] : null;

                // Check if script uses media version.
                if (isset($attribs['options']['version']) && $attribs['options']['version'] && strpos($src, '?') === false && ($mediaVersion || $attribs['options']['version'] !== 'auto')) {
                    $src .= '?' . ($attribs['options']['version'] === 'auto' ? $mediaVersion : $attribs['options']['version']);
                }

                $buffer .= $tab;

                // This is for IE conditional statements support.
                if (!is_null($conditional)) {
                    $buffer .= '<!--[if ' . $conditional . ']>';
                }

                $buffer .= '<link href="' . $src . '" rel="stylesheet"';

                // Add script tag attributes.
                foreach ($attribs as $attrib => $value) {
                    // Don't add the 'options' attribute. This attribute is for internal use (version, conditional, etc).
                    if ($attrib === 'options') {
                        continue;
                    }

                    // Don't add type attribute if document is HTML5 and it's a default mime type. 'mime' is for B/C.
                    if (in_array($attrib, array(
                            'type',
                            'mime'
                        )) && $this->document->isHtml5() && in_array($value, $defaultCssMimes)) {
                        continue;
                    }

                    // Don't add type attribute if document is HTML5 and it's a default mime type. 'mime' is for B/C.
                    if ($attrib === 'mime') {
                        $attrib = 'type';
                    }

                    // Add attribute to script tag output.
                    $buffer .= ' ' . htmlspecialchars($attrib, ENT_COMPAT, 'UTF-8');

                    // Json encode value if it's an array.
                    $value = !is_scalar($value) ? json_encode($value) : $value;

                    $buffer .= '="' . htmlspecialchars($value, ENT_COMPAT, 'UTF-8') . '"';
                }

                $buffer .= $tagEnd;

                // This is for IE conditional statements support.
                if (!is_null($conditional)) {
                    $buffer .= '<![endif]-->';
                }

                $buffer .= $lnEnd;
            }

            // Generate stylesheet declarations
            foreach ($this->updates['_style'] as $type => $content) {
                $buffer .= $tab . '<style';

                if (!is_null($type) && (!$this->document->isHtml5() || !in_array($type, $defaultCssMimes))) {
                    $buffer .= ' type="' . $type . '"';
                }

                $buffer .= '>' . $lnEnd;

                // This is for full XHTML support.
                if ($this->document->_mime != 'text/html') {
                    $buffer .= $tab . $tab . '/*<![CDATA[*/' . $lnEnd;
                }

                $buffer .= $content . $lnEnd;

                // See above note
                if ($this->document->_mime != 'text/html') {
                    $buffer .= $tab . $tab . '/*]]>*/' . $lnEnd;
                }

                $buffer .= $tab . '</style>' . $lnEnd;
            }

            // Generate scripts options
            $scriptOptions = $this->document->getScriptOptions();

            if (!empty($scriptOptions)) {
                $buffer .= $tab . '<script type="application/json" class="joomla-script-options new">';

                $prettyPrint = (JDEBUG && defined('JSON_PRETTY_PRINT') ? JSON_PRETTY_PRINT : false);
                $jsonOptions = json_encode($scriptOptions, $prettyPrint);
                $jsonOptions = $jsonOptions ? $jsonOptions : '{}';

                $buffer .= $jsonOptions;
                $buffer .= '</script>' . $lnEnd;
            }

            $defaultJsMimes         = array(
                'text/javascript',
                'application/javascript',
                'text/x-javascript',
                'application/x-javascript'
            );
            $html5NoValueAttributes = array(
                'defer',
                'async'
            );

            foreach ($this->updates['_scripts'] as $src => $attribs) {
                // Check if script uses IE conditional statements.
                $conditional = isset($attribs['options']) && isset($attribs['options']['conditional']) ? $attribs['options']['conditional'] : null;

                // Check if script uses media version.
                if (isset($attribs['options']['version']) && $attribs['options']['version'] && strpos($src, '?') === false && ($mediaVersion || $attribs['options']['version'] !== 'auto')) {
                    $src .= '?' . ($attribs['options']['version'] === 'auto' ? $mediaVersion : $attribs['options']['version']);
                }

                $buffer .= $tab;

                // This is for IE conditional statements support.
                if (!is_null($conditional)) {
                    $buffer .= '<!--[if ' . $conditional . ']>';
                }

                $buffer .= '<script src="' . $src . '"';

                // Add script tag attributes.
                foreach ($attribs as $attrib => $value) {
                    // Don't add the 'options' attribute. This attribute is for internal use (version, conditional, etc).
                    if ($attrib === 'options') {
                        continue;
                    }

                    // Don't add type attribute if document is HTML5 and it's a default mime type. 'mime' is for B/C.
                    if (in_array($attrib, array(
                            'type',
                            'mime'
                        )) && $this->document->isHtml5() && in_array($value, $defaultJsMimes)) {
                        continue;
                    }

                    // B/C: If defer and async is false or empty don't render the attribute.
                    if (in_array($attrib, array(
                            'defer',
                            'async'
                        )) && !$value) {
                        continue;
                    }

                    // Don't add type attribute if document is HTML5 and it's a default mime type. 'mime' is for B/C.
                    if ($attrib === 'mime') {
                        $attrib = 'type';
                    } // B/C defer and async can be set to yes when using the old method.
                    else if (in_array($attrib, array(
                            'defer',
                            'async'
                        )) && $value === true) {
                        $value = $attrib;
                    }

                    // Add attribute to script tag output.
                    $buffer .= ' ' . htmlspecialchars($attrib, ENT_COMPAT, 'UTF-8');

                    if (!($this->document->isHtml5() && in_array($attrib, $html5NoValueAttributes))) {
                        // Json encode value if it's an array.
                        $value = !is_scalar($value) ? json_encode($value) : $value;

                        $buffer .= '="' . htmlspecialchars($value, ENT_COMPAT, 'UTF-8') . '"';
                    }
                }

                $buffer .= '></script>';

                // This is for IE conditional statements support.
                if (!is_null($conditional)) {
                    $buffer .= '<![endif]-->';
                }

                $buffer .= $lnEnd;
            }

            foreach ($this->updates['_script'] as $type => $content) {
                $buffer .= $tab . '<script';

                if (!is_null($type) && (!$this->document->isHtml5() || !in_array($type, $defaultJsMimes))) {
                    $buffer .= ' type="' . $type . '"';
                }

                $buffer .= '>' . $lnEnd;

                // This is for full XHTML support.
                if ($this->document->_mime != 'text/html') {
                    $buffer .= $tab . $tab . '//<![CDATA[' . $lnEnd;
                }

                $buffer .= $content . $lnEnd;

                // See above note
                if ($this->document->_mime != 'text/html') {
                    $buffer .= $tab . $tab . '//]]>' . $lnEnd;
                }

                $buffer .= $tab . '</script>' . $lnEnd;
            }

            return $buffer;
        } else {
            return null;
        }
    }
}