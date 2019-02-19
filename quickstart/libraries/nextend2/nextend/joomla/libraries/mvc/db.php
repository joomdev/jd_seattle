<?php

class N2DBConnector extends N2DBConnectorAbstract
{

    /**
     * @var JDatabaseDriver
     */
    private $db;

    /**
     * Primary key name in table
     *
     * @var string
     */
    public $primaryKeyColumn = "id";

    public function __construct($class) {
        $this->db = JFactory::getDbo();
        $this->_prefix = $this->db->getPrefix();
        
        $this->setTableName($class);
    }

    public function query($query, $attributes = false) {
        if ($attributes) {
            foreach ($attributes as $key => $value) {
                $replaceTo = is_numeric($value) ? $value : $this->db->quote($value);
                $query     = str_replace($key, $replaceTo, $query);
            }
        }
        $this->db->setQuery($query);
        return $this->db->execute();
    }

    /**
     * @param mixed $primaryKey primary key value
     *
     * @return mixed
     */
    public function findByPk($primaryKey) {
        $query = $this->db->getQuery(true);

        $query->select('*');
        $query->from($this->quoteName($this->tableName));
        $query->where($this->quoteName($this->primaryKeyColumn) . ' = ' . (is_numeric($primaryKey) ? $primaryKey : $this->db->quote($primaryKey)));

        // Reset the query using our newly populated query object.
        $this->db->setQuery($query);

        // Load the results as a list of stdClass objects (see later for more options on retrieving data).
        return $this->db->loadAssoc();
    }

    public function findByAttributes(array $attributes, $fields = false, $order = false) {
        $query = $this->db->getQuery(true);
        if ($fields) {
            $query->select($this->quoteName($fields));
        } else {
            $query->select(array('*'));
        }
        $query->from($this->quoteName($this->tableName));
        foreach ($attributes as $key => $val) {
            $query->where($this->quoteName($key) . ' = ' . (is_numeric($val) ? $val : $this->quote($val)));
        }

        if ($order) {
            $query->order($order);
        }

        $this->db->setQuery($query);

        return $this->db->loadAssoc();
    }

    /**
     * @param string|bool $order
     *
     * @return mixed
     */
    public function findAll($order = false) {
        $query = $this->db->getQuery(true);
        $query->select('*');
        $query->from($this->db->quoteName($this->tableName));

        if ($order) {
            $query->order($order);
        }

        $this->db->setQuery($query);

        return $this->db->loadAssocList();
    }

    /**
     * Return with all row by attributes
     *
     * @param array       $attributes
     * @param bool|array  $fields
     * @param bool|string $order
     *
     * @return mixed
     */
    public function findAllByAttributes(array $attributes, $fields = false, $order = false) {
        $query = $this->db->getQuery(true);
        if ($fields) {
            $query->select($this->quoteName($fields));
        } else {
            $query->select('*');
        }
        $query->from($this->db->quoteName($this->tableName));
        foreach ($attributes as $key => $val) {
            $query->where($this->db->quoteName($key) . ' = ' . (is_numeric($val) ? $val : $this->db->quote($val)));
        }

        if ($order) {
            $query->order($order);
        }

        $this->db->setQuery($query);

        return $this->db->loadAssocList();
    }

    /**
     * Return with one row by query string
     *
     * @param string     $query
     * @param array|bool $attributes for parameter binding
     *
     * @return mixed
     */
    public function queryRow($query, $attributes = false) {
        if ($attributes) {
            foreach ($attributes as $key => $value) {
                $replaceTo = is_numeric($value) ? $value : $this->db->quote($value);
                $query     = str_replace($key, $replaceTo, $query);
            }
        }
        $nextend = $this->db->setQuery($query);
        return $nextend->loadAssoc();
    }

    public function queryAll($query, $attributes = false, $type = "assoc", $key = null) {
        if ($attributes) {
            foreach ($attributes as $key => $value) {
                $replaceTo = is_numeric($value) ? $value : $this->db->quote($value);
                $query     = str_replace($key, $replaceTo, $query);
            }
        }

        $nextend = $this->db->setQuery($query);

        if ($type == "assoc") {
            return $nextend->loadAssocList($key);
        } else {
            return $nextend->loadObjectList($key);
        }

    }

    /**
     * Insert new row
     *
     * @param array $attributes
     *
     * @return bool|mixed|void
     */
    public function insert(array $attributes) {
        $object = new stdClass();
        foreach ($attributes as $key => $value) {
            $object->$key = $value;
        }

        // Insert the object into the user profile table.
        try {
            return $this->db->insertObject($this->tableName, $object);
        } catch (Exception $e) {
            return false;
        }
    }

    public function insertId() {
        return $this->db->insertid();
    }

    /**
     * Update row(s) by param(s)
     *
     * @param array $attributes
     * @param array $conditions
     *
     * @return mixed
     */
    public function update(array $attributes, array $conditions) {
        $query = $this->db->getQuery(true);

        $fields = array();

        foreach ($attributes as $akey => $avalue) {
            $fields[] = $this->quoteName($akey) . ' = ' . (is_numeric($avalue) ? intval($avalue) : $this->quote($avalue));
        }

        $where = array();
        foreach ($conditions as $ckey => $cvalue) {
            $where[] = $this->quoteName($ckey) . ' = ' . (is_numeric($cvalue) ? intval($cvalue) : $this->quote($cvalue));
        }

        $query->update($this->quoteName($this->tableName))->set($fields)->where($where);

        $this->db->setQuery($query);

        return $this->db->execute();
    }

    /**
     * Update one row by primary key with $attributes
     *
     * @param mixed $primaryKey
     * @param array $attributes
     *
     * @return mixed
     */
    public function updateByPk($primaryKey, array $attributes) {
        $query = $this->db->getQuery(true);

        $fields = array();

        foreach ($attributes as $akey => $avalue) {
            $fields[] = $this->quoteName($akey) . ' = ' . (is_numeric($avalue) ? intval($avalue) : $this->quote($avalue));
        }

        $conditions = $this->quoteName($this->primaryKeyColumn) . ' = ' . (is_numeric($primaryKey) ? $primaryKey : $this->quote($primaryKey));

        $query->update($this->quoteName($this->tableName))->set($fields)->where($conditions);

        $this->db->setQuery($query);

        return $this->db->execute();
    }

    /**
     * Delete one with by primary key
     *
     * @param mixed $primaryKey
     *
     * @return mixed
     */
    public function deleteByPk($primaryKey) {
        $query = $this->db->getQuery(true);

        $conditions = array($this->quoteName($this->primaryKeyColumn) . ' = ' . (is_numeric($primaryKey) ? $primaryKey : $this->quote($primaryKey)));

        $query->delete($this->quoteName($this->tableName));
        $query->where($conditions);

        $this->db->setQuery($query);

        return $this->db->execute();
    }

    /**
     * Delete all rows by attributes
     *
     * @param array $conditions
     *
     * @return mixed
     */
    public function deleteByAttributes(array $conditions) {
        $query = $this->db->getQuery(true);

        $where = array();
        foreach ($conditions as $ckey => $cvalue) {
            $where[] = $this->quoteName($ckey) . ' = ' . (is_numeric($cvalue) ? intval($cvalue) : $this->quote($cvalue));
        }

        $query->delete($this->quoteName($this->tableName));
        $query->where($where);

        $this->db->setQuery($query);

        return $this->db->execute();
    }

    /**
     * @param string $text
     * @param bool   $escape
     *
     * @return string
     */
    public function quote($text, $escape = true) {
        return $this->db->quote($text, $escape);
    }

    /**
     * @param string $name
     * @param null   $as
     *
     * @return mixed
     */
    public function quoteName($name, $as = null) {
        return $this->db->quoteName($name, $as);
    }
}