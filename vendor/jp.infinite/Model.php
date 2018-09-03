<?php

class Model extends Reticent {
    public static $pdo;

    protected $table = "";  // Reqired

    protected $pk = "";  // Reqired

    /**
     * @param $name
     * @return Model
     * @throws Exception
     */
    public static function createInstance($name) {
        $name = Inflector::camelize($name);
        $fileName = ROOT_DIR . "/model/" . $name . ".php";
        if (!is_file($fileName)) {
            throw new Exception("Non-existence Model name specified.");
        }
        /** @noinspection PhpIncludeInspection */
        require_once ROOT_DIR . "/model/" . $name . ".php";
        /** @var Model $instance */
        $instance = new $name();
        $instance->newBinding();
        return new $instance;
    }

    protected function __construct($table = "", PDO $pdo = null) {
        if ($pdo === null ) $pdo = self::$pdo;
        parent::__construct($this->table, $pdo);
        if (empty($this->table)) throw new Exception("Table name not specified.");
        if (empty($this->pk)) throw new Exception("Descriptor of primary key is empty.");
    }

    public function getById($id, $select="") {
        return $this
            ->select($select)
            ->find($id);
    }

    public function search($select, $where, $orderBy, $limit, $offset) {
        return parent::get(
            $this->searchAsStatement($select, $where, $orderBy, $limit, $offset)
        );
    }

    public function searchAsStatement($select, $where, $orderBy, $limit, $offset) {
        return $this
            ->select($select)
            ->where($where)
            ->orderBy($orderBy)
            ->limit($limit)
            ->offset($offset)
            ->getAsStatement();
    }

    public static function init() {
        self::$pdo = DB::connect(static::PDO_DSN_DBMS, static::PDO_DSN_HOST, static::PDO_DSN_DB, static::PDO_USER, static::PDO_PASSWD);
    }
}
Model::init();
