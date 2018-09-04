<?php

/**
 * Class Reticent
 *
 * Reticent (not *Eloquent*) query builder.
 * (Optimized for PostgreSQL)
 *
 * @package type86
 * @author	Toshio HIRAI <toshio.hirai@gmail.com>
 * @copyright	Copyright (c) 2017, Infinite Corporation.
 * @license	http://opensource.org/licenses/MIT	MIT License
 *
 */
class Reticent extends DB {
    const PDO_DSN_DBMS = Settings::PDO_DSN_DBMS;
    const PDO_DSN_HOST = Settings::PDO_DSN_HOST;
    const PDO_DSN_DB = Settings::PDO_DSN_DB;
    const PDO_USER = Settings::PDO_USER;
    const PDO_PASSWD = Settings::PDO_PASSWD;

    protected $table = "";

    protected $pk = "";

    private $select = "";

    private $from;

    private $joins = array();

    private $where = "";

    private $orders = array();

    private $offset = null;

    private $limit = null;

    /** @var Reticent $binding */
    private $binding;

    /** @var PDO $pdo */
    private $pdo;

    private $tableInfo;

    /** @var array $columnMeta */
    private $columnMeta;

    public static $lastQuery;

    protected function __construct($table="", PDO $pdo=null) {
        if ($pdo === null) {
            $pdo = DB::connect(static::PDO_DSN_DBMS, static::PDO_DSN_HOST, static::PDO_DSN_DB, static::PDO_USER, static::PDO_PASSWD);
        }
        $this->pdo = $pdo;
        if (!empty($table)) {
            $this->table = $table;
            $this->columnMeta = $this->getColumnMeta();
            $this->from = $this->table;
            $this->from($this->from);
        }
    }

    private function getColumnMeta() {
        $stmt = $this->getAsStatement("select * from " . $this->table . " limit 0");
        $columns = array();
        for ($i = 0; $i < $stmt->columnCount(); $i++) {
            $meta = $stmt->getColumnMeta($i);
            $columns[$meta["name"]] = $meta;
        }
        return $columns;
    }

    private function printingSql($sql, $name, $value, $isNumeric = false) {
        if (is_null($value) || strtoupper($value) == "NULL") $value = "NULL";
        elseif (true === $value) $value = "true";
        elseif (false === $value) $value = "false";
        else {
            if (!$isNumeric){
                $value = $this->pdo->quote($value);
            }
        }
        $sql = str_replace($name . ",", $value . ",",  $sql, $c);
        if ($c == 0) {
            $sql = str_replace($name, $value,  $sql);
        }
        return $sql;
    }

    public function delete($id) {
        if (empty($this->table)) throw new Exception("Table name can not be resolved.");

        $sql = "delete from " . $this->table;
        $sql .= " where " . $this->pk . " = :"  . $this->pk;
        $printingSql = $sql;

        $stmt = $this->pdo->prepare($sql);

        if ($this->needQuote($this->pk)) {
            $stmt->bindParam(":" . $this->pk, $id, PDO::PARAM_STR);
            $printingSql = $this->printingSql($printingSql, ":" . $this->pk, $id);
        } else {
            $stmt->bindValue(":" . $this->pk, $id, PDO::PARAM_INT);
            $printingSql = $this->printingSql($printingSql, ":" . $this->pk, $id, true);
        }
        if (!$stmt->execute()) throw new Exception();
        Logger::notice("Executed SQL[".$stmt->rowCount()."]: " . $printingSql);
    }

    public function update($id, array $data) {
        if (empty($this->table)) throw new Exception("Table name can not be resolved.");

        $data["modified"] = date("Y-m-d H:i:s");
        $orderd = $this->putInOrder($data);
        $cols = $orderd["cols"];
        $placeHolder = $orderd["placeHolder"];
        $vals = $orderd["vals"];
        $exp = array();
        foreach($cols as $i => $col) {
            if ($col == $this->pk) continue;
            $exp[] = $col . " = " . $placeHolder[$i];
        }

        $sql = "update " . $this->table . " set ";
        $sql .= implode(", ", $exp);
        $sql .= " where " . $this->pk . " = :"  . $this->pk;
        $printingSql = $sql;

        $stmt = $this->pdo->prepare($sql);

        foreach($cols as $i=>$col) {
            if ($col == $this->pk) continue;
            if ($this->needQuote($col)) {
                $stmt->bindParam($placeHolder[$i], $vals[$i], PDO::PARAM_STR);
                $printingSql = $this->printingSql($printingSql, $placeHolder[$i], $vals[$i]);
            } else {
                $stmt->bindValue($placeHolder[$i], $vals[$i], PDO::PARAM_INT);
                $printingSql = $this->printingSql($printingSql, $placeHolder[$i], $vals[$i], true);
            }
        }

        if ($this->needQuote($this->pk)) {
            $stmt->bindParam(":" . $this->pk, $id, PDO::PARAM_STR);
            $printingSql = $this->printingSql($printingSql, ":" . $this->pk, $id);
        } else {
            $stmt->bindValue(":" . $this->pk, $id, PDO::PARAM_INT);
            $printingSql = $this->printingSql($printingSql, ":" . $this->pk, $id, true);
        }

        if (!$stmt->execute()) throw new Exception();

        Logger::notice("Executed SQL[".$stmt->rowCount()."]: " . $printingSql);

        return $id;
    }

    public function insert(array $data) {
        if (empty($this->table)) throw new Exception("Table name can not be resolved.");

        $data["created"] = date("Y-m-d H:i:s");
        $data["modified"] = date("Y-m-d H:i:s");
        unset($data[$this->pk]);
        $orderd = $this->putInOrder($data);
        $cols = $orderd["cols"];
        $placeHolder = $orderd["placeHolder"];
        $vals = $orderd["vals"];

        $sql = "insert into " . $this->table . "(";
        $sql .= implode(", ", $cols);
        $sql .= ") values (";
        $sql .= implode(", ", $placeHolder);
        $sql .= ")";
        $printingSql = $sql;

        $stmt = $this->pdo->prepare($sql);

        foreach($cols as $i=>$col) {
            if ($this->needQuote($col)) {
                $stmt->bindParam($placeHolder[$i], $vals[$i], PDO::PARAM_STR);
                $printingSql = $this->printingSql($printingSql, $placeHolder[$i], $vals[$i]);
            } else {
                $stmt->bindValue($placeHolder[$i], $vals[$i], PDO::PARAM_INT);
                $printingSql = $this->printingSql($printingSql, $placeHolder[$i], $vals[$i], true);
            }
        }

        $stmt->execute();
        $id = $this->pdo->lastInsertId();

        Logger::notice("Executed SQL[".$stmt->rowCount()."]: " . $printingSql . ", LAST_INSERT_ID: " . $id);

        return $id;

    }

    private function putInOrder(array $data) {
        $cols = array();
        $placeHolder = array();
        $vals = array();
        foreach($data as $key=>$val) {
            $cols[] = $key;
            $placeHolder[] = ":" . $key;
            $vals[] = $val;
        }
        return array("cols"=>$cols, "placeHolder"=>$placeHolder, "vals"=>$vals);
    }

    public function save(array $data, $pre=true) {
        if (empty($this->table)) throw new Exception("Table name can not be resolved.");
        try {
            $data = $this->normalize($data);
            if ($pre) $data = $this->preSave($data);

            if (count($data) == 0) throw new Exception("Nothing data.");

            $isCreation = false;
            if (is_array($this->pk)) {
                throw new Exception("Multi Primary-key is not supported.");
            } else {
                if (array_key_exists($this->pk, $data)) {
                    if (empty($data[$this->pk])) {
                        $isCreation = true;
                    }
                } else {
                    $isCreation = true;
                }
            }
            if ($isCreation) {
                return $this->insert($data);
            }

            return $this->update($data[$this->pk], $data);

        } catch (Exception $e) {
            throw new Exception("Database Error.");
        }
    }

    protected function normalize(array $data) {
        return $data;
    }

    protected function preSave(array $data) {
        $tableItems = array();
        $columnNames = $this->getColumnNames();
        foreach($data as $key=>$val) {
            if (in_array($key, $columnNames)) {
                $tableItems[$key] = $val;
            }
        }
        return $tableItems;
    }

    public function getColumnNames() {
        $names = array();
        foreach($this->columnMeta as $meta) {
            $names[] = $meta['name'];
        }
        return $names;
    }

    public function needQuote($col) {
        $type = strtolower($this->columnMeta[$col]["native_type"]);
        if ($type == "long" || $type == "integer" || $type == "int4" || $type == "int8") {
            return false;
        }
        return true;
    }

    protected function newBinding() {
        $this->binding = $this->newQuery();
        $this->from($this->from);
    }

    public function newQuery() {
        return new static($this->table);
    }

    public function getQuery($name=null) {
        if ($name === null) {
            return $this->binding;
        }
        return $this->binding->{$name};
    }

    public function qt($value, $esc=false) {
        if (is_null($value)) return "NULL";
        if (is_int($value)) return $this->pdo->quote($value, PDO::PARAM_INT);
        if (is_float($value)) return $this->pdo->quote($value, PDO::PARAM_STR) . "::float4";
        if (is_bool($value)) return $this->pdo->quote($value, PDO::PARAM_BOOL);
        if (is_string($value)) {
            if ($esc) {
                $value = str_replace("%", "\\\\%", $value);
                $value = str_replace("_", "\\\\_", $value);
            }
            return $this->pdo->quote($value);
        }
        throw new Exception("Unacceptable type value.");
    }

    public function matchQt($value, $forward=true, $backward=true) {
        $qt = $this->qt($value, true);
        $qt = trim($qt, "'");
        return "'" . ($backward ? "%" : "") . $qt . ($forward ? "%" : "") . "'";
    }

    public function forwardMatchQt($value) {return $this->matchQt($value, true, false);} 	// like '...%'

    public function backwardMatchQt($value) {return $this->matchQt($value, false, true);} 	// like '%...'

    public function getTableInfo(){ return $this->tableInfo; }

    public function pdo() { return $this->pdo; }

    public function select($select="*") {
        if (is_string($select)) {
            if (trim($select) != "") {
                $this->binding->select = $select;
            }
        }
        if (is_array($select)) {
            if (!empty($select)) {
                $this->binding->select = implode(", " , $select);
            }
        }
        return $this;
    }

    public function from($from) {
        if (!empty($from)) $this->binding->from = $from;
        return $this;
    }

    public function join($table, $on, $fk, $type="inner", $where="") {
        if (empty($table)) return $this;
        $_table = $table;
        $_on = $on;
        $_fk = $fk;
        $_operator = "=";
        $_type = !empty($type) ? $type : "inner";
        $_where = !empty($where) ? $where : "";
        $this->binding->joins[] = $_type . " join " . $_table
            . " on " . $_on . " " . trim($_operator) . " " . $_fk
            . (!empty($_where) ? " and " . $_where : "");
        return $this;
    }

    public function like($where, $lop="and") {
        return $this->where($where, $lop, "like");
    }

    public function whereBetween($value, array $range, $lop="and") {
        list($from, $to) = $range;
        if (empty($value) || empty($from) || empty($to)) return $this;
        $expr = $value . " between " . $from . " and " . $to;

        if (!empty($this->binding->where)) {
            $this->binding->where .= " " . $lop . " " . $expr;
        } else {
            $this->binding->where = $expr;
        }
        return $this;
    }

    public function whereNotIn($id, $query, $lop="and") {
        return $this->whereIn($id, $query, $lop, true);
    }

    /**
     * @param string $id
     * @param array|string $query
     * @param string $lop
     * @param bool $not
     * @return Reticent $this
     */
    public function whereIn($id, $query, $lop="and", $not=false) {
        $expr = $id;
        if ($not) $expr .= " not ";
        $expr .= " in ";
        if (is_array($query)) {
            // id の配列
            $expr .= "(" . explode(", ", $query) . ")";
        } else {
            // サブクエリ
            $expr .= "( " . $query . " )";
        }
        if (!empty($this->binding->where)) {
            $this->binding->where .= " " . $lop . " " . $expr;
        } else {
            $this->binding->where = $expr;
        }
        return $this;
    }

    public function where($where, $lop="and", $operator="=") {
        if (empty($where)) return $this;
        if (is_string($where) && trim($where) != "") {
            $expr = "( " . $where . " )";
        } elseif(is_array($where)) {
            list($column, $value) = $where;
            $expr = "( " . $column . " " . $operator . " " . $value . " )";
        } else {
            throw new Exception("Malformed type speified.");
        }
        if (!empty($this->binding->where)) {
            $this->binding->where .= " " . $lop . " " . $expr;
        } else {
            $this->binding->where = $expr;
        }
        return $this;
    }

    public function id($id) {
        if (empty($this->pk)) throw new Exception("Can not perform caused by empty pk.");
        $this->where(array($this->pk, $id));

        return $this;
    }

    public function orderBy($column, $direction="asc", $collate=null) {
        $orders = array();
        if (!is_array($column)) {
            $orders[] = array($column, $direction);
        } else {
            $orders = $column;
        }
        foreach($orders as $order) {
            $direction = "asc";
            if (is_array($order)) {
                $column = $order[0];
                if (isset($order[1])) {
                    $direction = $order[1];
                }
            } else {
                $column = $order;
            }
            $this->binding->orders[] = $column . ($collate === null ? " " : " collate \"".$collate."\" ") . (strtolower($direction) == "asc" ? "asc" : "desc");
        }
        return $this;
    }

    public function offset($offset=0) {
        if ($offset < 0) $offset = 0;
        $this->binding->offset = $offset;
        return $this;
    }

    public function limit($limit="all") {
        if ($limit < 0) $limit = "all";
        $this->binding->limit = $limit;
        return $this;
    }

    public function toSql() {
        $sql = "";
        if (!empty($this->binding->select)) {
            $sql .= "select" . PHP_EOL .  StringUtils::indent(4) . $this->binding->select;
        } else {
            $sql .= "select *";
        }
        $sql .= PHP_EOL;
        $sql .= "from" . PHP_EOL . StringUtils::indent(4) . $this->binding->from;
        if (!empty($this->binding->joins)) {
            foreach($this->binding->joins as $join) {
                $sql .= PHP_EOL . $join;
            }
        }
        if (!empty($this->binding->where)) {
            $sql .= PHP_EOL . "where" . PHP_EOL . StringUtils::indent(4) . $this->binding->where;
        }
        if (!empty($this->binding->orders)) {
            $sql .= PHP_EOL . "order by" . PHP_EOL . StringUtils::indent(4) . implode(", ", $this->binding->orders);
        }
        if ($this->binding->limit !== null) {
            $sql .= PHP_EOL . "limit" . PHP_EOL . StringUtils::indent(4) . $this->binding->limit;
        }
        if ($this->binding->offset !== null) {
            $sql .= PHP_EOL . "offset" . PHP_EOL . StringUtils::indent(4) . $this->binding->offset;
        }
        $this->newBinding();
        self::$lastQuery = $sql;
        Logger::debug("lastSQl", array("SQL" => preg_replace('/\s{2,}/', ' ', preg_replace('/\n|\r|\r\n/', ' ', $sql))));
        return $sql;
    }

    public function count($where=array()) {
        return $this->select("count(*)")->where($where)->value();
    }

    public function first($sql=null) {
        $stmt = self::getAsStatement($sql);

        return $stmt->fetch(PDO::FETCH_ASSOC);
    }

    public function find($id) {
        if (empty($this->pk)) throw new Exception("Can not perform caused by empty pk.");
        return $this->where(array($this->pk, $id))->first();
    }

    public function exists($id) {
        if (empty($this->pk)) throw new Exception("Can not perform caused by empty pk.");
        if ($this->where(array($this->pk, $id))->count() == 0) return false;
        return true;
    }

    public function set($id, $column, $value) {
        if (!$this->exists($id)) throw new Exception("Non-existence id specified.");
        if (empty($this->pk)) throw new Exception("Can not perform caused by empty pk.");
        $sql = "update " . $this->table . " set " . $column . " = " . $value . " where " . $this->pk . " = " . $id;
        return $this->query($sql);
    }

    public function value($column=null) {
        if ($column !== null and empty($this->binding->select)) $this->select($column);
        $stmt = self::getAsStatement();
        $style = $column === null ? PDO::FETCH_NUM : pdo::FETCH_ASSOC;
        $row = $stmt->fetch($style);
        if (empty($row)) return "";
        return $column === null ? current($row) : $row[$column];
    }

    public function get($sqlOrStatement=null) {
        if ($sqlOrStatement instanceof PDOStatement) {
            $stmt = $sqlOrStatement;
        } else {
            $stmt = $this->getAsStatement($sqlOrStatement);
        }
        $d = $stmt->fetchAll(PDO::FETCH_ASSOC);
        return $d;
    }

    public function getAsStatement($sql=null) {
        if ($sql === null) $sql = $this->toSql();

        return $this->query($sql);
    }

    public function query($sql) {
        return $this->pdo->query($sql);
    }

    public function getIdByRowNum($where, $order, $rownum) {
        $sql = "with d as (select " . $this->pk . ", row_number() over(order by " . $order . ") as " .
            "rownum from " . $this->table . " where " . $where . ")" .
            "select " . $this->pk . " from d where rownum = " . $rownum;
        self::$lastQuery = $sql;
        $stmt = $this->query($sql);
        $row = $stmt->fetch(PDO::FETCH_ASSOC);
        if (empty($row)) return 0;
        return current($row);
    }

    public function rowNum($where, $order, $id) {
        $sql = "with d as (select " . $this->pk . ", row_number() over(order by " . $order . ") as " .
               "rownum from " . $this->table . " where " . $where . ") " .
               "select rownum from d where " . $this->pk . " = " . $id;
        self::$lastQuery = $sql;
        $stmt = $this->query($sql);
        $row = $stmt->fetch(PDO::FETCH_ASSOC);
        if (empty($row)) return 0;
        return current($row);
    }

    public function tableName() {
        return $this->table;
    }

    public function setPk($pk) {
        $this->pk = $pk;
    }

}