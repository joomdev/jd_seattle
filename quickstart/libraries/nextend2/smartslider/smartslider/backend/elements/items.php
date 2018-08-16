<?php

class N2ElementItems extends N2ElementHidden {


    protected function fetchElement() {
        /** @var N2SSPluginItemFactoryAbstract[] $items */
        $items = N2SmartSliderItemsFactory::getItems();

        ob_start();
        ?>
        <div id="smartslider-slide-toolbox-item" class="nextend-clearfix smartslider-slide-toolbox-view">
            <?php
            /** @var N2SmartSliderRenderableAbstract $renderable */
            $renderable = $this->getForm()
                               ->getContext()
                               ->get('renderable');

            foreach ($items AS $type => $item) {
                $item->loadResources($renderable);

                echo N2Html::openTag("div", array(
                    "id"              => "smartslider-slide-toolbox-item-type-{$type}",
                    "style"           => "display:none",
                    "data-itemvalues" => json_encode($item->getValues())
                ));

                $form = new N2Form(N2Base::getApplication('smartslider')
                                         ->getApplicationType('backend'));

                $item->renderFields($form);

                echo $form->render('item_' . $type);

                echo N2Html::closeTag("div");
            }
            ?>
        </div>
        <?php
        return ob_get_clean();
    }
}