<div class="n2-ss-available-layers-container">

    <?php
    $structuresWithGroup = array(
        'structure' => array(
            n2_x('Basic', 'Structure')   => array(
                "1col",
                "2col",
                "3col",
                "4col",
                "2col-60-40",
                "2col-40-60",
                "2col-80-20",
                "2col-20-80",
                "3col-20-60-20",
            ),
            n2_x('Special', 'Structure') => array(
                "special",
            )
        )
    );

    foreach ($structuresWithGroup As $item => $structuresGroup) {
        foreach ($structuresGroup As $group => $structures) {
            echo N2HTML::tag('div', array(
                'class' => 'n2-h5 n2-uc n2-ss-slide-item-group'
            ), $group);
            foreach ($structures AS $structure) {

                echo N2HTML::tag('div', array(
                    'class'     => 'n2-ss-core-item',
                    'data-item' => $item,
                    'data-sstype' => $structure
                ));
            }
        }
    }
    ?>
</div>