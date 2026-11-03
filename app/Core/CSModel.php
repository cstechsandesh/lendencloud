<?php

namespace App\Core;

/*
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */

/**
 * Description of CSModel
 *
 * @author Sandesh
 */
class CSModel extends \CodeIgniter\Model {

    protected $returnType = 'object';
    protected $useSoftDeletes = false;

    /**
     * Database Connection
     *
     * @var ConnectionInterface
     */
//    public $db;
    protected $allowedFields = [];
    protected $useTimestamps = false;
    protected $useUpdatedBy = false;
    protected $activeField = null;
    public $enableOrderBy = false;
    public $orderDirection = 'ASC';
    protected $orderField = '';
    public $withRelatedTableJoins = true;
    public $joins = [];
    protected $selectAllTableFileds = true;
    protected $skipValidation = true;

    public function __construct(\CodeIgniter\Database\ConnectionInterface &$db = null, \CodeIgniter\Validation\ValidationInterface $validation = null) {
        parent::__construct($db, $validation);
    }

    public function hash($str, $u_name = null) {
        empty($u_name) && $u_name = session('username');
        return hash('sha512', $str . config('App')->salt . strtolower($u_name));
    }

    public function lastQuery() {
        return $this->db->showLastQuery();
    }

    public function insert($data = null, $returnID = true) {
        if ($this->useUpdatedBy && empty($data['created_by'])) {
            $data = array_merge($data, ['created_by' => session('u_id')??1]);
        }
        return parent::insert($data, $returnID);
    }

    public function mySave($data) {
        if (empty($data[$this->primaryKey])) {
            if ($this->useUpdatedBy && empty($data['created_by'])) {
                $data = array_merge($data, ['created_by' => session('u_id')??0]);
            }
            return parent::insert($data, true);
        } else {
            if ($this->useUpdatedBy && empty($data['updated_by'])) {
                $data = array_merge($data, ['updated_by' => session('u_id')]);
            }
            parent::save($data);
            return $data[$this->primaryKey];
        }
    }

    public function selectAllField() {
        $this->selectAllTableFileds = true;
    }

    public function selectCurrentTableFields() {
        $this->selectAllTableFileds = false;
    }

    public function myUpdate($id = null, $data = null) {
        if ($this->useUpdatedBy) {
            $data = array_merge($data, ['updated_by' => session('u_id')]);
        }
        return parent::update($id, $data);
    }

    public function select($fields) {
        if (!empty($fields)) {
            $this->builder()->select($fields);
        }
        return $this;
    }

    public function myFirst($condition = NULL) {
        if (!empty($this->joins)) {
            foreach ($this->joins as $join) {
                $this->join($join['table'], $join['condition'], $join['type'] ?? '');
            }
            $this->joins = null;
        }
        if (!empty($condition)) {
            $this->builder()->where($condition);
        }
        return parent::first();
    }

    /**
     * Fetches the row of database from $this->table with a primary key
     * matching $id.
     *
     * @param mixed|array|null $id One primary key or an array of primary keys
     *
     * @return array|object|null    The resulting row of data, or null.
     */
    public function myFind($id = null, $condition = null) {
        if (!empty($this->joins)) {
            foreach ($this->joins as $join) {
                $this->join($join['table'], $join['condition'], $join['type'] ?? '');
            }
            $this->joins = null;
        }
        if (!empty($condition)) {
            $this->where($condition);
        }
        $this->enableOrderBy && $this->orderBy($this->orderField, $this->orderDirection);
        $res = parent::find($id);
        return is_array($res) ? current($res) : $res;
    }

    public function myFindAll($condition = null, int $limit = 0, int $offset = 0) {
        if (!empty($this->joins)) {
            foreach ($this->joins as $join) {
                $this->join($join['table'], $join['condition'], $join['type'] ?? '');
            }
            $this->joins = null;
        }
        !empty($condition) && $this->where($condition);
        $this->enableOrderBy && $this->orderBy($this->table . '.' . $this->orderField, $this->orderDirection);
        return parent::findAll($limit, $offset);
    }

    public function getActiveRecord($id = null, $condition = null) {
        if (!empty($condition)) {
            $this->where($condition);
        }
        empty($this->activeField) || $this->where([$this->activeField => 1]);
        $this->enableOrderBy && $this->orderBy($this->orderField, $this->orderDirection);
        $res = parent::find($id);
        return is_array($res) ? current($res) : $res;
    }

    public function getActiveRecords($condition = null, int $limit = 0, int $offset = 0) {
        if (!empty($condition)) {
            $this->where($condition);
        }
        empty($this->activeField) || $this->where([$this->activeField => 1]);
        $this->enableOrderBy && $this->orderBy($this->orderField, $this->orderDirection);
        return parent::findAll($limit, $offset);
    }

    public function myPaginate($perPage = null, $group = 'default', $page = null, $condition = null, $segment = 0) {
        if (!empty($this->joins)) {
            foreach ($this->joins as $join) {
                $this->join($join['table'], $join['condition'], $join['type'] ?? '');
            }
            $this->joins = null;
        }
        empty($condition) || $this->where($condition);
        $this->enableOrderBy && $this->orderBy($this->table . '.' . $this->orderField, $this->orderDirection);
        empty($perPage) && $perPage = session('perPage');
        return parent::paginate($perPage, $group, $page, $segment);
    }

    public function lockTable($type = 'write') {
        $prefix = defined('DBPREFIX') ? DBPREFIX : ($this->db->getPrefix() ?? '');
        $sql = 'LOCK TABLE ' . $prefix . $this->table . " " . $type;
        $this->db->query($sql);
    }

    public function unLockTables() {
        $sql = 'UNLOCK TABLES';
        $this->db->query($sql);
    }
    
    public function resetAutoIncrement() {
        $prefix = defined('DBPREFIX') ? DBPREFIX : ($this->db->getPrefix() ?? '');
        $this->selectMax($this->primaryKey);
        $row = $this->first();
        $latestId = is_object($row) ? ($row->{$this->primaryKey} ?? 0) : ($row[$this->primaryKey] ?? 0);
        $this->db->query('ALTER TABLE ' . $prefix . $this->table . ' AUTO_INCREMENT = ' . (intval($latestId) + 1));
    }

}
