<?php
class StringUtils {
    const WHITE_SPACE = " ";

	private function __construct() {}

	public static function dia($str) {
		$chars = TokenUtils::toChars($str);
		$dia = array();
		foreach($chars as $c) {
			$d = TokenUtils::dia(array($c));
			$dia[] = $d[0];
		}
		return TokenUtils::toString($dia);
	}

	public static function startsWith($haystack, $needle){
	    if (is_array($needle)) {
	        $find = false;
	        foreach($needle as $n) {
                $find = strpos($haystack, $n, 0) === 0;
                if ($find) return $find;
            }
            return $find;
        }
		return strpos($haystack, $needle, 0) === 0;
	}

	public static function endsWith($haystack, $needle){
		$length = (strlen($haystack) - strlen($needle));
		if($length < 0) return FALSE;
		return strpos($haystack, $needle, $length) !== FALSE;
	}

	public static function contains($haystack, $needle){
		return strpos($haystack, $needle) !== FALSE;
	}

	public static function equals($s1, $s2){
		return $s1 === $s2;
	}

	public static function indent($number) {
	    $indent = "";
	    for($i = 0; $i < $number; $i++) $indent .= self::WHITE_SPACE;
        return $indent;
    }
}
