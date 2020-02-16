<?php
namespace lib;

class coreModel{
    protected $_table = '';
    protected $_where = null;
    protected $_field = '*';
    protected $_join = [];
    protected $_sql = '';
    protected $_mode = ''; //select insert update delete
    protected $_page = 0;
    protected $_limit = 0;
    protected $_order = [];
    protected $_alias = '';
    protected $_data = [];
    protected $_group;

    public function clear()
    {
        $this->_table = '';
        $this->_where = null;
        $this->_field = '*';
        $this->_join = [];
        $this->_sql = '';
        $this->_mode = ''; //select insert update delete
        $this->_page = 0;
        $this->_limit = 0;
        $this->_order = [];
        $this->_alias = '';
        $this->_data = [];
        $this->_group = '';
    }

    public function table($table)
    {
        $this->clear();
        $this->_table = $table;
        return $this;
    }

    public function field($fields)
    {
        $this->_field = $fields;
        return $this;
    }

    public function where($where)
    {
        if (is_array($where)) {
            $this->_where = implode(' AND ', $where);
        } else if (is_string($where)) {
            $this->_where = $where;
        }

        return $this;
    }

    public function mode($mode)
    {
        if (in_array($mode, ['select', 'insert', 'update', 'delete'])) {
            $this->_mode = $mode;
        } else {
            $this->_mode = 'select';
        }

        return $this;
    }

    public function data($data)
    {
        $this->_data = $data;
        return $this;
    }

    public function order($order)
    {
        $this->_order = $order;
        return $this;
    }

    public function page($page, $limit = false)
    {
        $this->_page = $page;
        if ($limit) {
            $this->limit($limit);
        }
        return $this;
    }

    public function limit($limit)
    {
        $this->_limit = $limit;
        return $this;
    }

    public function join($joinStr)
    {
        $this->_join[] = $joinStr;
        return $this;
    }

    public function alias($alias)
    {
        $this->_alias = $alias;
        return $this;
    }

    public function beginTrans()
    {
        db::init()->query('START TRANSACTION');
        return $this;
    }

    public function commitTrans()
    {
        db::init()->query('COMMIT');
        return $this;
    }

    public function rollbackTrans()
    {
        db::init()->query('ROLLBACK');
        return $this;
    }

    public function group($group)
    {
        $this->_group = $group;
        return $this;
    }

    private function _buildSql()
    {
        switch ($this->_mode) {
            case 'select': {
                $sql = "SELECT {$this->_field} FROM {$this->_table} ".((function($a){
                        if (!empty($a)) {
                            return 'AS '.$a.' ';
                        } else {
                            return ' ';
                        }
                    })($this->_alias)).((function($j){
                        if (!empty($j)) {
                            return implode(' ', $j).' ';
                        } else {
                            return ' ';
                        }
                    })($this->_join))."WHERE ".($this->buildWhere())." ".((function($g){
                        if (!empty($g)) {
                            return "GROUP BY {$g} ";
                        } else {
                            return ' ';
                        }
                    })($this->_group)).((function($o){

                        if (!empty($o)) {
                            if (is_string($o)) {
                                return "ORDER BY {$o} ";
                            } else {
                                return 'ORDER BY '.implode(',', $o).' ';
                            }
                        } else {
                            return ' ';
                        }
                    })($this->_order)).((function($p, $l){
                        if (!empty($p)) {
                            if (empty($l)) {
                                return ' ';
                            } else {
                                $pageIndex = ($p - 1) * $l;
                                return "LIMIT {$pageIndex},{$l} ";
                            }
                        } else {
                            return ' ';
                        }
                    })($this->_page, $this->_limit));
            };break;

            case 'insert': {
                $colArr = [];
                $valArr = [];
                foreach ($this->_data as $k => $v) {
                    $colArr[] = $k;
                    $valArr[] = ((function($v){
                        if (is_null($v)) {
                            return "''";
                        } else if (is_string($v)) {
                            return "'{$v}'";
                        } else {
                            return $v;
                        }
                    })($v));
                }
                $cols = implode(',', $colArr);
                $vals = implode(',', $valArr);

                $sql = "INSERT INTO {$this->_table}({$cols}) VALUES({$vals});";
            };break;

            case 'update': {
                $updateMapArr = [];
                foreach ($this->_data as $k => $v) {
                    $updateMapArr[] = is_string($v)?"{$k}='{$v}'":$k.'='.$v;
                }
                $updateMapStr = implode(',', $updateMapArr);
                $sql = "UPDATE {$this->_table} SET {$updateMapStr} WHERE ".($this->buildWhere()).';';
            }break;

            case 'delete': {
                $sql = "DELETE {$this->_table} WHERE ".$this->buildWhere().";";
            }break;
        }

        $this->_sql = $sql;
        return $this;
    }

    private function buildWhere()
    {
        if (is_array($this->_where)) {
            return implode(' AND ', $this->_where);
        } else if (is_string($this->_where)) {
            return $this->_where;
        } else {
            return '1=1';
        }
    }

    public function query()
    {
        $this->_buildSql();

        if (!$this->_table) {
            error('没有指定表');
        }

        try {
            if ($this->_mode == 'select') {
                $dbRes = db::init()->query($this->_sql, true);
            } else if ($this->_mode == 'insert') {
                $dbRes = db::init()->query($this->_sql);
                if ($dbRes) {
                    $insertRes = db::init()->query('SELECT LAST_INSERT_ID();', true);
                    return $insertRes[0]['LAST_INSERT_ID()'];
                } else {
                    return false;
                }
            } else{
                $dbRes = db::init()->query($this->_sql);
            }
        } catch(\Exception $e) {
            ajax(500, 'sql exec error', $e);
        }

        return $dbRes;
    }

    public function find()
    {
        $dbRes = $this->query();
        if (!empty($dbRes)) {
            return $dbRes[0];
        } else {
            return [];
        }
    }

    public function sql()
    {
        $this->_buildSql();
        return $this->_sql;
    }
}
