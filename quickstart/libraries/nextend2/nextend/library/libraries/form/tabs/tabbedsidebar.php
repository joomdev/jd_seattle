<?php
N2Loader::import('libraries.form.tab');

class N2TabTabbedSidebar extends N2Tab {

    protected $classes = '';

    protected $active = 1;

    protected $underlined = false;

    /** @var N2TabGrouppedSidebar[] */
    protected $tabs = array();

    public function render($control_name) {

        $count  = count($this->tabs);
        $id     = 'n2-tabbed-' . $this->name;
        $active = $this->active - 1;


        ?>

        <div id="<?php echo $id; ?>">
            <div
                    class="n2-table n2-table-fixed n2-labels <?php echo $this->classes . ($this->underlined ? ' n2-has-underline' : ''); ?>">
                <div class="n2-tr">
                    <?php
                    $i = 0;
                    foreach ($this->tabs AS $tabName => $tab) {
                        echo N2Html::tag('div', array(
                            'data-tab' => $tab->getName(),
                            'class'    => "n2-td n2-h3 n2-uc n2-has-underline" . ($i == $active ? ' n2-active' : '')
                        ), $this->getLabel2($tab));
                        $i++;
                    }
                    ?>
                </div>
            </div>
            <div class="n2-tabs">
                <?php
                $tabs = array();
                $i    = 0;
                foreach ($this->tabs AS $tabName => $tab) {
                    $display = 'none';
                    if ($i == $active) {
                        $display = 'block';
                    }
                    $tabs[] = "$('#" . $id . '_' . $i . "')";
                    echo N2Html::openTag('div', array(
                        'id'       => $id . '_' . $i,
                        'style'    => 'display:' . $display . ';',
                        'data-tab' => $tab->getName()
                    ));
                    $tab->render($control_name);
                    echo N2Html::closeTag('div');
                    $i++;
                }
                ?>
            </div>
        </div>
        <script type="text/javascript">
            N2R('documentReady', function ($) {
                new N2Classes.NextendHeadingPane($('#<?php echo $id; ?>'), $('#<?php echo $id; ?> > .n2-labels .n2-td'), [
                    <?php echo implode(',', $tabs); ?>
                ]);
            });
        </script>
        <?php
    }

    /**
     * @param N2TabGrouppedSidebar $tab
     *
     * @return string
     */
    private function getLabel2($tab) {
        $icon = $tab->getIcon();
        if (!empty($icon)) {
            $attributes = array(
                'class' => 'n2-i ' . $icon
            );
            $tip        = $tab->getTip();
            if (!empty($tip)) {
                $attributes['data-n2tip'] = n2_($tip);
            }

            return N2Html::tag('div', $attributes, '');
        }
        $class = ($this->underlined ? 'n2-underline' : '');

        return N2Html::tag('span', array(
            'class' => $class
        ), $tab->getLabel());
    }

    /**
     * @param string $classes
     */
    public function setClasses($classes) {
        $this->classes = $classes;
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

}
