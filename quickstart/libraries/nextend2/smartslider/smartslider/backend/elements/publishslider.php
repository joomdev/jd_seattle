<?php
N2Loader::import('libraries.form.elements.hidden');

class N2ElementPublishSlider extends N2ElementHidden {

    protected function fetchElement() {
        ob_start();
        ?>
        <script type="text/javascript">
            function selectText(container) {
                if (document.selection) {
                    var range = document.body.createTextRange();
                    range.moveToElementText(container);
                    range.select();
                } else if (window.getSelection) {
                    var range = document.createRange();
                    range.selectNode(container);
                    var selection = window.getSelection();
                    selection.removeAllRanges();
                    selection.addRange(range);
                }
                return false;
            }

            document.addEventListener('copy', function (e) {
                if ($(e.target).hasClass('n2-has-copy')) {
                    try {
                        e.clipboardData.setData('text/plain', window.getSelection().toString());
                        e.clipboardData.setData('text/html', '<div>' + window.getSelection().toString() + '</div>');
                        e.preventDefault();
                    } catch (e) {

                    }
                }
            });
        </script>
        <?php

        $sliderid = N2Get::getInt('sliderid');
        include N2Loader::toPath('backend.inline', 'smartslider.platform') . '/publish.phtml';

        return ob_get_clean();
    }
}
