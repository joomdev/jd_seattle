<?php
N2Loader::import('libraries.form.tab');

class N2TabTabbed extends N2Tab {

    protected $active = 1;

    protected $underlined = false;

    protected $external = false;

    protected $classes = '';

    public function render($control_name) {

        $id = 'n2-form-matrix-' . $this->name;

        $active = $this->getActive();
        ?>

        <div id="<?php echo $id; ?>" class="n2-form-tab n2-form-matrix">

            <?php
            if (!$this->external) {
                ?>
                <div class="n2-h2 n2-content-box-title-bg n2-form-matrix-views <?php echo $this->classes; ?>">
                    <?php
                    $i     = 0;
                    $class = ($this->underlined ? 'n2-underline' : '') . ' n2-h4 n2-uc n2-has-underline n2-form-matrix-menu';

                    foreach ($this->tabs AS $tabName => $tab) {


                        echo N2Html::tag("div", array(
                            "class"    => $class . ($i == $active ? ' n2-active' : '') . ' n2-fm-' . $tabName,
                            "data-tab" => $tabName
                        ), N2Html::tag("span", array("class" => "n2-underline"), $tab->getLabel()));

                        $i++;
                    }
                    ?>
                </div>
                <?php
            }
            ?>

            <div class="n2-tabs">
                <?php
                $i = 0;
                foreach ($this->tabs AS $tabName => $tab) {
                    echo N2Html::openTag('div', array(
                        'class' => 'n2-form-matrix-pane' . ($i == $active ? ' n2-active' : '') . ' n2-fm-' . $tabName
                    ));
                    $tab->render($control_name);
                    echo N2Html::closeTag('div');
                    $i++;
                }
                ?>
            </div>
        </div>
        <?php
        $this->addScript($id);
    }

    protected function getActive() {
        return max(1, $this->active) - 1;
    }

    protected function addScript($id) {
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
            })();
        ');
    }

    /**
     * @param int $active
     */
    public function setActive($active) {
        $this->active = $active;
    }

    /**
     * @param bool $underlined
     */
    public function setUnderlined($underlined) {
        $this->underlined = $underlined;
    }

    /**
     * @param bool $external
     */
    public function setExternal($external) {
        $this->external = $external;
    }

    /**
     * @param string $classes
     */
    public function setClasses($classes) {
        $this->classes = $classes;
    }
}
