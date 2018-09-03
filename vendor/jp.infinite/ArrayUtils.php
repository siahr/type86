<?php

class ArrayUtils {
    private function __construct() {
    }

    public static function implode($glue, $pieces) {
        $imploded = array();
        foreach ($pieces as $v) if (strlen(trim($v))) $imploded[] = $v;
        return implode($glue, $imploded);
    }

}