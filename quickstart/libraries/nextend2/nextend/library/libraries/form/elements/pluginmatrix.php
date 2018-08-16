<?php
/**
 * @todo: Refactor with fragments
 */
N2Loader::import('libraries.form.elements.hidden');

abstract class N2ElementPluginMatrix extends N2ElementHidden {

    protected $plugins;

    protected function fetchElement() {
        $plugins = $this->getPlugins();

        $id = 'n2-form-matrix-' . $this->fieldID;

        $html = N2Html::openTag("div", array(
            'id'    => $id,
            "class" => "n2-form-matrix"
        ));

        $value = $this->getValue();
        if (!isset($plugins[$value])) {
            reset($plugins);
            $value = key($plugins);
        }

        $html .= N2Html::openTag('div', array('class' => 'n2-h2 n2-content-box-title-bg n2-form-matrix-views'));

        $class = 'n2-underline n2-h4 n2-uc n2-has-underline n2-form-matrix-menu';
        foreach ($plugins AS $type => $plugin) {

            $html .= N2Html::tag("div", array(
                "onclick" => "N2Classes.$('#{$this->fieldID}').val('{$type}');",
                "class"   => $class . ($value == $type ? ' n2-active' : '') . ' n2-fm-' . $type
            ), N2Html::tag("span", array("class" => "n2-underline"), $plugin->getLabel()));

        }
        $html .= N2Html::closeTag("div");


        $html .= N2Html::openTag("div", array(
            "class" => "n2-tabs"
        ));


        foreach ($plugins AS $type => $plugin) {


            $html .= N2Html::openTag('div', array(
                'class' => 'n2-form-matrix-pane' . ($value == $type ? ' n2-active' : '')
            ));

            $GLOBALS['nextendbuffer'] = '';
            $form                     = new N2Form($this->getForm()->appType);

            $form->loadArray($this->getForm()
                                  ->toArray());

            $plugin->renderFields($form);

            ob_start();
            $form->render($this->control_name);
            $html .= ob_get_clean();

            $html .= $GLOBALS['nextendbuffer'];

            $html .= N2Html::closeTag("div");
        }

        $html .= N2Html::closeTag("div");

        $html .= N2Html::closeTag("div");
        N2JS::addInline('
            (function(){
                var matrix = $("#' . $id . '"),
                    views = matrix.find("> .n2-form-matrix-views > div"),
                    panes = matrix.find("> .n2-tabs > div");
                views.on("click", function(){
                    views.removeClass("n2-active");
                    panes.removeClass("n2-active");
                    var i = views.index(this);
                    views.eq(i).addClass("n2-active");
                    panes.eq(i).addClass("n2-active");
                });

                views.find(":visible").first().trigger("click");
            })();
        ');

        return $html . parent::fetchElement();
    }

    protected abstract function getPlugins();


    public static function sort($a, $b) {
        return $a->ordering - $b->ordering;
    }
}