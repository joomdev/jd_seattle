<?php

N2Loader::import("libraries.slider.abstract", "smartslider");

class N2SmartsliderSlidersXrefModel extends N2Model {

    public function __construct() {
        parent::__construct("nextend2_smartslider3_sliders_xref");
    }

    public function add($groupID, $sliderID) {
        try {
            $this->db->insert(array(
                'group_id'  => $groupID,
                'slider_id' => $sliderID,
                'ordering'  => $this->getMaximalOrderValue($groupID)
            ));

            N2SS3::sliderChanged();

            return true;
        } catch (Exception $e) {
            return false;
        }
    }

    public function deleteGroup($groupID) {
        $sliders = $this->getSliders($groupID);

        $this->db->deleteByAttributes(array(
            'group_id' => $groupID
        ));

        $slidersModel = new N2SmartsliderSlidersModel();
        foreach ($sliders AS $slider) {
            // Delete if no group left for the slider
            if (!count($this->getGroups($slider['slider_id']))) {
                $slidersModel->delete($slider['slider_id']);
            }
        }

        N2SS3::sliderChanged();
    }

    public function deleteSlider($sliderID) {

        N2SmartsliderSlidersModel::markChanged($sliderID);

        N2SS3::sliderChanged();

        return $this->db->deleteByAttributes(array(
            'slider_id' => $sliderID
        ));
    }

    public function deleteXref($groupID, $sliderID) {

        N2SmartsliderSlidersModel::markChanged($sliderID);
        N2SmartsliderSlidersModel::markChanged($groupID);

        N2SS3::sliderChanged();

        return $this->db->deleteByAttributes(array(
            'group_id'  => $groupID,
            'slider_id' => $sliderID
        ));
    }

    public function getSliders($groupID) {
        return $this->db->queryAll("
            SELECT slider_id
            FROM " . $this->getTable() . "
            WHERE group_id = '" . $groupID . "'
            ORDER BY ordering ASC");
    }

    public function getGroups($sliderID) {
        $slidersModel = new N2SmartsliderSlidersModel();

        return $this->db->queryAll("
            SELECT xref.group_id, sliders.title
            FROM " . $this->getTable() . " AS xref
            JOIN " . $slidersModel->getTable() . " AS sliders ON sliders.id = xref.group_id
            WHERE xref.slider_id = '" . $sliderID . "'
            ORDER BY xref.ordering ASC");
    }

    protected function getMaximalOrderValue($groupID) {

        $query  = "SELECT MAX(ordering) AS ordering FROM " . $this->getTable() . " WHERE group_id = '" . intval($groupID) . "'";
        $result = $this->db->queryRow($query);

        if (isset($result['ordering'])) return $result['ordering'] + 1;

        return 0;
    }
}