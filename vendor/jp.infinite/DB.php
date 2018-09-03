<?php

/**
 * Class DB
 *
 * DB abstraction class, also functionary as utility class.
 *
 * @license http://www.wtfpl.net/txt/copying/ WTFPL
 *
 */
abstract class DB {

    private function __construct() {}

    public static function dot($table, $name) {
        $divided = self::splitSymbol($name);
        if ($divided["type"] != "none") return $name;
        return $table . "." . $name;
    }

    public static function key($table, $name) {
        $divided = self::splitSymbol($name);
        if ($divided["type"] != "none") return $name;
        return $table . "__" . $name;
    }

    public static function swapQualifiedName($symbol, $swapTo, $interconversion=true) {
        if ($interconversion)  $symbol = self::interconversion($symbol);
        $divided = self::splitSymbol($symbol);
        switch($divided["type"]) {
            case "dot":
                $swaped = $swapTo . "." . $divided["name"];
                break;

            case "key":
                $swaped = $swapTo . "__" . $divided["name"];
                break;

            default:
                throw new Exception("Malformed symbol specified.");
        }
        return $swaped;

    }

    public static function interconversion($symbol) {
        $divided = self::splitSymbol($symbol);
        switch($divided["type"]) {
            case "dot":
                $converted = $divided["table"] . "__" . $divided["name"];
                break;

            case "key":
                $converted = $divided["table"] . "." . $divided["name"];
                break;

            default:
                throw new Exception("Unable interconversion.");
        }
        return $converted;
    }

    public static function itemName($symbol) {
        $divided = self::splitSymbol($symbol);
        return $divided["name"];
    }

    public static function qualifiedName($symbol) {
        $divided = self::splitSymbol($symbol);
        return $divided["table"];
    }

    public static function splitSymbol($symbol) {
        $type = null;
        if (\StringUtils::contains($symbol, ".")) $type = "dot";
        if (\StringUtils::contains($symbol, "__")) $type = "key";
        if ($type === null) {
            return array("type"=>"none", "table"=>"", "name"=>$symbol);
        }
        $elements = array();
        switch($type) {
            case "dot":
                $elements = explode(".", $symbol);
                break;

            case "key":
                $elements = explode("__", $symbol);
                break;
        }
        list($table, $name) = $elements;

        return array("type"=>$type, "table"=>$table, "name"=>$name);
    }

    public static function alias($table, $name) {
        return self::dot($table, $name) . " as " . self::key($table, $name);
    }

    public static function fullyQualifiedOrderBy($orderBy, $qualifiedName) {
        if (empty($orderBy)) return "";
        $qualified = array();
        $elements = explode(",", $orderBy);
        foreach($elements as $elemant) {
            list($column, $direction) = explode(" ", $elemant);
            $column = self::dot($qualifiedName, trim($column));
            $direction = trim($direction) == "" ? "asc" : trim($direction);
            $qualified[] = array("column"=>$column, "direction"=>$direction);
        }
        $orderBy = array();
        foreach($qualified as $def) {
            $orderBy[] .=  $def["column"] . " " . $def["direction"];
        }
        return implode(", ", $orderBy);
    }

    /**
     * Return instances without going through Model class.
     *
     * Usage: Both of the following codes are valid.
     * $db = DB::table("tablename");
     * $db = DB::table("tablename", "id");
     *
     * @param string $table
     * @param string $pk
     * @return Reticent
     * @throws Exception
     */
    public static function table($table="", $pk="id", $pdo=null) {
        $instance = new Reticent($table, $pdo);
        $instance->setPk($pk);
        $instance->newBinding();
        return $instance;
    }

    public static function quote($value) {
        $instance = new Reticent();
        return $instance->qt($value);
    }

    public static function matchQuote($value, $forward=true, $backward=true) {
        $instance = new Reticent();
        $qt = $instance->qt($value, true);
        $qt = trim($qt, "'");
        return "'" . ($backward ? "%" : "") . $qt . ($forward ? "%" : "") . "'";
    }

    public static function forwardMatchQuote($value) {return self::matchQuote($value, true, false);} 	// like '...%'

    public static function backwardMatchQuote($value) {return self::matchQuote($value, false, true);} 	// like '%...'

    public static function exec($sql) {
        $instance = new Reticent();
        $stmt = $instance->query($sql);
        return $stmt->rowCount();
    }

    public static function read($sql, $asStatement=false) {
        $instance = new Reticent();
        if (!$asStatement) return $instance->get($sql);
        return $instance->getAsStatement($sql);
    }

    public static function __callStatic($name, $arguments) {
        self::table(strtolower($name));
    }

    public static function connect($dbms, $host, $db, $user, $passwd) {
        $dsn = $dbms . ":host=" . $host . "; dbname=" . $db;
        try {
            $pdo = new PDO($dsn, $user, $passwd, array(PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION));
        } catch (Exception $e) {
            print_r($e);
        }
        return $pdo;
    }

    protected abstract function newBinding();
}
