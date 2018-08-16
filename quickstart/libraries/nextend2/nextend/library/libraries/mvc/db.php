<?php

abstract class N2DBConnectorAbstract {

    public $tableName;

    protected $_prefixJoker = '#__';

    protected $_prefix = '';

    public function setTableName($name = "") {
        if (empty($name)) {
            $name = $this->tableName;
        }

        $this->tableName = $this->_prefix . $name;
    }

    public function parsePrefix($query) {
        return str_replace($this->_prefixJoker, $this->_prefix, $query);
    }

    abstract public function query($query, $attributes = false);

    abstract public function findByPk($primaryKey);

    abstract public function findByAttributes(array $attributes, $fields = false, $order = false);

    abstract public function findAll($order = false);

    /**
     * Return with all row by attributes
     *
     * @param array       $attributes
     * @param bool|array  $fields
     * @param bool|string $order
     *
     * @return mixed
     */
    abstract public function findAllByAttributes(array $attributes, $fields = false, $order = false);

    /**
     * Return with one row by query string
     *
     * @param string     $query
     * @param array|bool $attributes for parameter binding
     *
     * @return mixed
     */
    abstract public function queryRow($query, $attributes = false);

    abstract public function queryAll($query, $attributes = false, $type = "assoc", $key = null);

    /**
     * Insert new row
     *
     * @param array $attributes
     *
     * @return mixed|void
     */
    abstract public function insert(array $attributes);

    abstract public function insertId();

    /**
     * Update row(s) by param(s)
     *
     * @param array $attributes
     * @param array $conditions
     *
     * @return mixed
     */
    abstract public function update(array $attributes, array $conditions);

    /**
     * Update one row by primary key with $attributes
     *
     * @param mixed $primaryKey
     * @param array $attributes
     *
     * @return mixed
     */
    abstract public function updateByPk($primaryKey, array $attributes);

    /**
     * Delete one with by primary key
     *
     * @param mixed $primaryKey
     *
     * @return mixed
     */
    abstract public function deleteByPk($primaryKey);

    /**
     * Delete all rows by attributes
     *
     * @param array $conditions
     *
     * @return mixed
     */
    abstract public function deleteByAttributes(array $conditions);

    /**
     * @param string $text
     * @param bool   $escape
     *
     * @return string
     */
    abstract public function quote($text, $escape = true);

    /**
     * @param string $name
     * @param null   $as
     *
     * @return mixed
     */
    abstract public function quoteName($name, $as = null);
}

N2Loader::import("libraries.mvc.db", "platform");