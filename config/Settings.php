<?php

define("DEBUG_MODE", true);
if (defined("ROOT_DIR")) {
    define("LOG_DESTINATION", ROOT_DIR . "logs" . DS);
    define("LOG_LEVEL", DEBUG_MODE ? Monolog\Logger::DEBUG : Monolog\Logger::INFO);
    if (!is_dir(LOG_DESTINATION)) {mkdir(LOG_DESTINATION); chmod(LOG_DESTINATION, 0777);}
}

/*
 * Static variables.
 */
class Settings {

    const PDO_DSN_DBMS = "pgsql";
    const PDO_DSN_HOST = "localhost";
    const PDO_DSN_DB = "type86";
    const PDO_USER = "postgres";
    const PDO_PASSWD = "";

    const PATH_OFFSET = 1;
    const SUB_DIR = "type86/";
    const ADDITIONAL_DIR = "";

    const DEFAULT_CONTROLLER = "welcome";
    const DEFAULT_ACTION = "index";

}

