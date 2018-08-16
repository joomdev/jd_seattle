<?php

class N2AssetsCss extends N2AssetsAbstract {

    public function __construct() {
        $this->cache = new N2AssetsCacheCSS();
    }

    public function getOutput() {

        N2GoogleFonts::build();
        N2LESS::build();

        $output = "";

        $this->urls = array_unique($this->urls);

        foreach ($this->urls AS $url) {
            $output .= N2Html::style($url, true, array(
                    'media' => 'all'
                )) . "\n";
        }

        $needProtocol = !N2Settings::get('protocol-relative', '1');

        $mode = N2Settings::get('css-mode', 'normal');
        if (N2Platform::$isAdmin || $mode != 'inline') {

            foreach ($this->getFiles() AS $file) {
                if (substr($file, 0, 2) == '//') {
                    $output .= N2Html::style($file, true, array(
                            'media' => 'all'
                        )) . "\n";
                } else {
                    $output .= N2Html::style(N2Uri::pathToUri($file, $needProtocol) . '?' . filemtime($file), true, array(
                            'media' => 'all'
                        )) . "\n";
                }
            }

            $inline = implode("\n", $this->inline);
            if (!empty($inline)) {
                $output .= N2Html::style($inline);
            }
        } else if ($mode == 'inline') {
            $inline = '';

            foreach ($this->getFiles() AS $file) {
                if (substr($file, 0, 2) == '//') {
                    $output .= N2Html::style($file, true, array(
                            'media' => 'screen, print'
                        )) . "\n";
                } else {
                    $inline .= N2Filesystem::readFile($file);
                }
            }

            $inline .= implode("\n", $this->inline);

            if (!empty($inline)) {
                $output .= N2Html::style($inline);
            }
        }

        return $output;
    }

    public function get() {
        N2GoogleFonts::build();
        N2LESS::build();

        return array(
            'url'    => $this->urls,
            'files'  => $this->getFiles(),
            'inline' => implode("\n", $this->inline)
        );
    }

    public function getAjaxOutput() {

        $output = implode("\n", $this->inline);

        return $output;
    }
} 