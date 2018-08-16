<?php
N2Loader::import('libraries.image.manager');

class N2SystemImageModel extends N2SystemVisualModel {

    public $type = 'image';

    public function __construct() {
        $this->storage = new N2StorageImage();
    }

    public function renderForm() {
        $form = new N2Form();

        $desktop = new N2Tab($form, 'desktop', n2_('Desktop'));
        new N2ElementContainer($desktop, 'desktop-preview', n2_('Preview'));
        $size = new N2ElementMixed($desktop, 'desktop-size', n2_('Size'), '0|*|0', array(
            'rowClass' => 'n2-expert'
        ));
        new N2ElementNumber($size, 'desktop-size-1', n2_('Width'), '', array(
            'min'  => 0,
            'wide' => 5
        ));
        new N2ElementNumber($size, 'desktop-size-2', n2_('Height'), '', array(
            'min'  => 0,
            'wide' => 5
        ));

        $this->renderDeviceTab($form, 'desktop-retina', n2_('Desktop Retina'));
        $this->renderDeviceTab($form, 'tablet', n2_('Tablet'));
        $this->renderDeviceTab($form, 'tablet-retina', n2_('Tablet Retina'));
        $this->renderDeviceTab($form, 'mobile', n2_('Mobile'));
        $this->renderDeviceTab($form, 'mobile-retina', n2_('Mobile Retina'));

        $form->render('n2-image-editor');
    }

    /**
     * @param N2Form $form
     */
    private function renderDeviceTab($form, $name, $label) {

        $tab = new N2Tab($form, $name, $label);
        new N2ElementImage($tab, $name . '-image', n2_('Image'));
        new N2ElementContainer($tab, $name . '-preview', n2_('Preview'));
        $size = new N2ElementMixed($tab, $name . '-size', n2_('Size'), '0|*|0', array(
            'rowClass' => 'n2-expert'
        ));
        new N2ElementNumber($size, $name . '-size-1', n2_('Width'), '', array(
            'min'  => 0,
            'wide' => 5
        ));
        new N2ElementNumber($size, $name . '-size-2', n2_('Height'), '', array(
            'min'  => 0,
            'wide' => 5
        ));

    }

    public function addVisual($image, $visual) {

        $visualId = $this->storage->add($image, $visual);

        $visual = $this->storage->getById($visualId);
        if (!empty($visual)) {
            return $visual;
        }

        return false;
    }

    public function getVisual($image) {
        return $this->storage->getByImage($image);
    }

    public function deleteVisual($id) {
        $visual = $this->storage->getById($id);
        $this->storage->deleteById($id);

        return $visual;
    }

    public function changeVisual($id, $value) {
        if ($this->storage->setById($id, $value)) {
            return $this->storage->getById($id);
        }

        return false;
    }

    public function getVisuals($setId) {
        return $this->storage->getAll();
    }
}