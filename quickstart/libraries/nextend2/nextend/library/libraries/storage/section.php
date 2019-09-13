<?php

class N2StorageSectionAdmin {

    /**
     * @var N2Model
     */
    public static $model;

    public static function get($application, $section, $referenceKey = null) {
        $attributes = array(
            "application" => $application,
            "section"     => $section
        );

        if ($referenceKey !== null) {
            $attributes['referencekey'] = $referenceKey;
        }

        return self::$model->db->findByAttributes($attributes);
    }

    public static function getById($id, $section = null) {
        static $cache = array();
        if ($id === 0) {
            return null;
        }
        if (!isset($cache[$section])) {
            $cache[$section] = array();
        } else if (isset($cache[$section][$id])) {
            return $cache[$section][$id];
        }

        $cache[$section][$id] = null;
        if ($section) {
            N2Pluggable::doAction($section, array(
                $id,
                &$cache[$section][$id]
            ));
            if ($cache[$section][$id]) {
                return $cache[$section][$id];
            }
        }

        $cache[$section][$id] = self::$model->db->findByAttributes(array(
            "id" => $id
        ));
        if ($section && $cache[$section][$id]['section'] != $section) {
            $cache[$section][$id] = null;

            return $cache[$section][$id];
        }

        return $cache[$section][$id];
    }

    public static function getAll($application, $section, $referenceKey = null) {
        $attributes = array(
            "application" => $application,
            "section"     => $section
        );

        if ($referenceKey !== null) {
            $attributes['referencekey'] = $referenceKey;
        }

        $rows = self::$model->db->findAllByAttributes($attributes, array(
            "id",
            "referencekey",
            "value",
            "system",
            "editable"
        ));

        N2Pluggable::doAction($application . $section, array(
            $referenceKey,
            &$rows
        ));

        return $rows;
    }

    public static function add($application, $section, $referenceKey, $value, $system = 0, $editable = 1) {
        $row = array(
            "application" => $application,
            "section"     => $section,
            "value"       => $value,
            "system"      => $system,
            "editable"    => $editable
        );

        if ($referenceKey !== null) {
            $row["referencekey"] = $referenceKey;
        }

        self::$model->db->insert($row);

        return self::$model->db->insertId();
    }


    public static function set($application, $section, $referenceKey, $value, $system = 0, $editable = 1) {

        $result = self::getAll($application, $section, $referenceKey);

        if (empty($result)) {
            return self::add($application, $section, $referenceKey, $value, $system, $editable);
        } else {
            $attributes = array(
                "application" => $application,
                "section"     => $section
            );

            if ($referenceKey !== null) {
                $attributes['referencekey'] = $referenceKey;
            }
            self::$model->db->update(array('value' => $value), $attributes);

            return true;
        }
    }

    public static function setById($id, $value) {

        $result = self::getById($id);

        if ($result !== null && $result['editable']) {
            self::$model->db->update(array('value' => $value), array(
                "id" => $id
            ));

            return true;
        }

        return false;
    }

    public static function delete($application, $section, $referenceKey = null) {

        $attributes = array(
            "application" => $application,
            "section"     => $section,
            "system"      => 0
        );

        if ($referenceKey !== null) {
            $attributes['referencekey'] = $referenceKey;
        }

        self::$model->db->deleteByAttributes($attributes);

        return true;
    }

    public static function deleteById($id) {

        self::$model->db->deleteByAttributes(array(
            "id"     => $id,
            "system" => 0
        ));

        return true;
    }
}

N2StorageSectionAdmin::$model = new N2Model("nextend2_section_storage");

class N2StorageSection {

    private $application = 'system';

    /**
     * Quick cache implementation to prevent duplicate queries. It might have bugs.
     *
     * @var array
     */
    protected $cache = array();

    public function __construct($application) {
        $this->application = $application;
    }

    public function getById($id, $section) {
        return N2StorageSectionAdmin::getById($id, $section);
    }

    public function setById($id, $value) {
        $this->cache = array();

        return N2StorageSectionAdmin::setById($id, $value);
    }

    public function get($section, $referenceKey = null, $default = null) {

        if (isset($this->cache[$section . '///' . $referenceKey])) {
            return $this->cache[$section . '///' . $referenceKey];
        }

        $attributes = array(
            "application" => $this->application,
            "section"     => $section
        );

        if ($referenceKey !== null) {
            $attributes['referencekey'] = $referenceKey;
        }
        $result = N2StorageSectionAdmin::$model->db->findByAttributes($attributes);
        if (is_array($result)) {
            $this->cache[$section . '///' . $referenceKey] = $result['value'];

            return $result['value'];
        }

        return $default;
    }

    public function getAll($section, $referenceKey = null) {
        return N2StorageSectionAdmin::getAll($this->application, $section, $referenceKey);
    }

    public function set($section, $referenceKey, $value) {
        if (isset($this->cache[$section . '///' . $referenceKey])) {
            unset($this->cache[$section . '///' . $referenceKey]);
        }
        N2StorageSectionAdmin::set($this->application, $section, $referenceKey, $value);
    }

    public function add($section, $referenceKey, $value) {
        if (isset($this->cache[$section . '///' . $referenceKey])) {
            unset($this->cache[$section . '///' . $referenceKey]);
        }

        return N2StorageSectionAdmin::add($this->application, $section, $referenceKey, $value);
    }

    public function delete($section, $referenceKey = null) {
        if (isset($this->cache[$section . '///' . $referenceKey])) {
            unset($this->cache[$section . '///' . $referenceKey]);
        }

        return N2StorageSectionAdmin::delete($this->application, $section, $referenceKey);
    }

    public function deleteById($id) {
        return N2StorageSectionAdmin::deleteById($id);
    }
}