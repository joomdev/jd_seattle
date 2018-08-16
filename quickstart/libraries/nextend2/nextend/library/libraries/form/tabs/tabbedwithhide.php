<?php

N2Loader::import('libraries.form.tabs.tabbed');

class N2TabTabbedWithHide extends N2TabTabbed {


    protected function getActive() {
        return -1;
    }

    protected function addScript($id) {
        N2JS::addInline('
            (function(){
                var matrix = $("#' . $id . '"),
                    views = matrix.find("> .n2-form-matrix-views > div"),
                    externalViews = $("#' . $id . '-external-tab > a"),
                    panes = matrix.find("> .n2-tabs > div");

                if(externalViews.length){
                    views = externalViews;
                }

                views.on("click", function(e){
                    e.preventDefault();
                    if($(this).hasClass("n2-active")){
                        views.removeClass("n2-active");
                        panes.removeClass("n2-active");
                    }else{
                        views.removeClass("n2-active");
                        panes.removeClass("n2-active");
                        var i = $(this).data("tab");
                        views.eq(i).addClass("n2-active");
                        panes.eq(i).addClass("n2-active");
                    }
                });
            })();
        ');
    }
}