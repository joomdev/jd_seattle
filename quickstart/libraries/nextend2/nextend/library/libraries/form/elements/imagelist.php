<?php
N2Loader::import('libraries.form.elements.radio');

abstract class N2ElementImageList extends N2ElementRadio {

    protected $folder = '';

    protected $isRequired = false;

    protected function fetchElement() {


        $files = N2Filesystem::files($this->folder);
        if (!$this->isRequired) {
            $this->options['-1'] = array(
                'path'  => false,
                'label' => n2_('No image')
            );
        }
        for ($i = 0; $i < count($files); $i++) {
            $ext        = pathinfo($files[$i], PATHINFO_EXTENSION);
            $extensions = array( 'jpg', 'jpeg', 'png', 'svg', 'gif', 'webp' );
            if (in_array($ext, $extensions)) {

                $path  = $this->folder . $files[$i];
                $value = $this->parseValue(N2Uri::pathToUri($path));

                $this->options[$value] = array(
                    'path'  => $path,
                    'label' => htmlspecialchars(ucfirst(pathinfo($files[$i], PATHINFO_FILENAME)))
                );
            }
        }

        $html = N2Html::openTag("div", array(
            'class' => 'n2-imagelist',
            'style' => $this->style
        ));

        $html .= parent::fetchElement();
        $html .= N2Html::closeTag('div');

        return $html;
    }

    protected function renderOptions() {
        $html = '';
        foreach ($this->options AS $value => $option) {
            $selected = $this->isSelected($value);
            if ($value != -1) {

                $html .= N2Html::openTag("div", array( "class" => "n2-radio-option n2-imagelist-option" . ($selected ? ' n2-active' : '') ));

                $ext = pathinfo($option['path'], PATHINFO_EXTENSION);
                if ($ext == 'svg') {
                    $image = 'data:image/svg+xml;base64,' . n2_base64_encode(N2Filesystem::readFile($option['path']));
                } else {
                    $image = N2Uri::pathToUri($option['path']);
                }

                $html .= N2Html::image($image, $option['label'], array( 'data-image' => $value ));
                $html .= N2Html::closeTag("div");
            } else {
                $html .= N2Html::tag("div", array( "class" => "n2-radio-option" . ($selected ? ' n2-active' : '') ), $option['label']);
            }
        }

        return $html;
    }

    function parseValue($image) {
        return N2ImageHelper::dynamic($image);
    }

    public function setFolder($folder) {
        $this->folder = $folder;
    }

    function isSelected($value) {
        if (basename($value) == basename($this->getValue())) {
            return true;
        }

        return false;
    }

    /**
     * @param bool $isRequired
     */
    public function setIsRequired($isRequired) {
        $this->isRequired = $isRequired;
    }
}
