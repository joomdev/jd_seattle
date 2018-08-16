<?php

class N2ImageHelper extends N2ImageHelperAbstract
{

    public static function initLightbox() {
        static $inited = false;
        if (!$inited) {

            JHtml::_('behavior.modal');
            $inited = true;
        }
    }

    public static function getLightboxFunction() {
        return 'function (callback) {
            var jInsertFieldValue = window.jInsertFieldValue;

            window.jInsertFieldValue = $.proxy(function (value) {
                    callback("$/" + value);
                    this.joomlaModal.hide();
                    window.jInsertFieldValue = jInsertFieldValue;
                }, this);

            this.joomlaModal = new N2Classes.NextendModal({
                zero: {
                    fit: true,
                    size: [
                        980,
                        680
                    ],
                    title: "' . n2_('Media Library') . '",
                    controls: [\'<a href="#" class="n2-a n2-uc n2-h5">Use legacy image selector</a>\'],
                    content: \'\',
                    fn: {
                        show: function () {
                            this.content.append(nextend.browse.getNode("single", $.proxy(function(image){
                                callback(image);
                                this.hide(null);
                                window.jInsertFieldValue = jInsertFieldValue;
                            }, this)));
                            this.controls.find(".n2-a")
                                .on("click", $.proxy(function (e) {
                                    e.preventDefault();
                                    this.loadPane("legacy");
                                }, this));
                        }
                    }
                },
                legacy: {
                    size: [
                        920,
                        680
                    ],
                    back: "zero",
                    title: "' . n2_('Images') . '",
                    content: \'<iframe src="index.php?option=com_media&view=images&tmpl=component&asset=com_content&author=&fieldid=notused&folder=" width="900" height="590" frameborder="0" style="margin:10px -10px 10px -10px;"></iframe>\'
                }
            }, true);
        }';
    }

    public static function getLightboxMultipleFunction() {
        return 'function (callback) {
            var jInsertFieldValue = window.jInsertFieldValue;

            window.jInsertFieldValue = $.proxy(function (value) {
                    callback([{
                        image: "$/" + value,
                        title: value,
                        description: ""
                    }]);
                    this.joomlaModal.hide();
                    window.jInsertFieldValue = jInsertFieldValue;
                }, this);

            this.joomlaModal = new N2Classes.NextendModal({
                zero: {
                    fit: true,
                    size: [
                        980,
                        680
                    ],
                    title: "' . n2_('Media Library') . '",
                    controlsClass: "n2-modal-controls-side",
                    controls: [\'<a href="#" class="n2-a n2-uc n2-h5">Use legacy image selector</a>\', \'<a href="#" class="n2-button n2-button-normal n2-button-l n2-button-green n2-h4 n2-uc">' . n2_('Select') . '</a>\'],
                    content: \'\',
                    fn: {
                        show: function () {
                            this.content.append(nextend.browse.getNode("multiple"));
                            this.controls.find(".n2-button-green")
                                .on("click", $.proxy(function (e) {
                                    e.preventDefault();
                                    var selected = nextend.browse.getSelected(),
                                        images = [];

                                        for(var i = 0; i < selected.length; i++){
                                            images[i] = {
                                                image: selected[i],
                                                title: selected[i].split("/").pop(),
                                                description: ""
                                            };
                                        }
                                    this.hide(e);
                                    callback(images);
                                    window.jInsertFieldValue = jInsertFieldValue;
                                }, this));
                            this.controls.find(".n2-a")
                                .on("click", $.proxy(function (e) {
                                    e.preventDefault();
                                    this.loadPane("legacy");
                                }, this));
                        }
                    }
                },
                legacy: {
                    size: [
                        920,
                        680
                    ],
                    back: "zero",
                    title: "' . n2_('Images') . '",
                    content: \'<iframe src="index.php?option=com_media&view=images&tmpl=component&asset=com_content&author=&fieldid=notused&folder=" width="900" height="590" frameborder="0" style="margin:10px -10px 10px -10px;"></iframe>\'
                }
            }, true);
        }';
    }
}