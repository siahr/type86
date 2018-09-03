<?php

class Logger {

    protected static $instance;

    /**
     * Method to return the Monolog instance
     *
     * @return \Monolog\Logger
     */
    static public function getLogger() {
        if (!self::$instance) {
            self::configureInstance();
        }

        return self::$instance;
    }

    /**
     * Configure Monolog to use a rotating files system.
     */
    protected static function configureInstance() {
        $log = new Monolog\Logger("TYPE86");
        $handler = new Monolog\Handler\StreamHandler(
            LOG_DESTINATION . date("Ymd") . ".log", LOG_LEVEL
        );
        $formatter = new Monolog\Formatter\LineFormatter(null, null, true);
        $handler->setFormatter($formatter);
        $log->pushHandler($handler);
        self::$instance = $log;
    }

    public static function debug($message, array $context = array()) {
        self::getLogger()->addDebug($message, $context);
    }

    public static function info($message, array $context = array()) {
        self::getLogger()->addInfo($message, $context);
    }

    public static function notice($message, array $context = array()) {
        self::getLogger()->addNotice($message, $context);
    }

    public static function warning($message, array $context = array()) {
        self::getLogger()->addWarning($message, $context);
    }

    public static function error($message, array $context = array()) {
        self::getLogger()->addError($message, $context);
    }

    public static function critical($message, array $context = array()) {
        self::getLogger()->addCritical($message, $context);
    }

    public static function alert($message, array $context = array()) {
        self::getLogger()->addAlert($message, $context);
    }

    public static function emergency($message, array $context = array()) {
        self::getLogger()->addEmergency($message, $context);
    }

}