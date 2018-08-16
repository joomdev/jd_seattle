<?php
N2Loader::import('libraries.form.elements.imagelist');

class N2ElementImageListFromFolder extends N2ElementImageList {

    public function setFolder($folder) {
        $this->folder = $folder;
    }
}