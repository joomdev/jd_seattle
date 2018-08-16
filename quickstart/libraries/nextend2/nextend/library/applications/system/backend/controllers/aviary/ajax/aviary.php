<?php

class N2SystemBackendAviaryControllerAjax extends N2BackendControllerAjax {

    public function actionGetHighResolutionAuth() {
        N2Loader::import('libraries.image.aviary');

        $this->response->respond(array(
            'highResolutionAuth' => N2ImageAviary::getHighResolutionAuth()
        ));
    }

    public function actionSaveImage() {
        $this->validateToken();
        N2Loader::import('libraries.image.aviary');

        $image = N2Request::getVar('aviaryUrl');
        $this->validateVariable(!empty($image), 'image');

        require_once dirname(__FILE__) . '/../../browse/ajax/browse.php';


        $root   = N2Filesystem::getImagesFolder();
        $folder = 'aviary';
        $path   = N2Filesystem::realpath($root . '/' . $folder);

        if ($path === false || $path == '') {
            N2Filesystem::createFolder($root . '/' . $folder);
            $path = N2Filesystem::realpath($root . '/' . $folder);
        }

        $folder = sys_get_temp_dir();
        if (!is_writable($folder)) {
            $folder = N2Filesystem::getNotWebCachePath();
        }

        $tmp = tempnam($folder, 'image-');
        file_put_contents($tmp, file_get_contents($image));

        $src = null;

        // Set variables for storage
        // fix file filename for query strings
        preg_match('/([^\?]+)\.(jpe?g|gif|png)\b/i', $image, $matches);
        $file_array['name']     = basename($matches[1]);
        $file_array['tmp_name'] = $tmp;
        $file_array['size']     = filesize($tmp);
        $file_array['error']    = 0;

        try {
            $fileName = preg_replace('/[^a-zA-Z0-9_-]/', '', $file_array['name']);

            $upload = new N2BulletProof();
            $file   = $upload->uploadDir($path)
                             ->upload($file_array, $fileName);
            $src    = N2ImageHelper::dynamic(N2Filesystem::pathToAbsoluteURL($file));

        } catch (Exception $e) {
            N2Message::error($e->getMessage());
            $this->response->error();
        }


        if ($src) {
            $this->response->respond(array(
                'image' => $src
            ));
        } else {
            N2Message::error(sprintf(n2_('Unexpected error: %s'), $image));
            $this->response->error();
        }
    }
}