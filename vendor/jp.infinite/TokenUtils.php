<?php
/**
* Token Utilities class
*
* The part of this PHP code is based on the product of BaseX Team.
* https://github.com/BaseXdb/basex/blob/master/src/main/java/org/basex/util/Token.java
*
* @package Sugina
* @author BaseX Team 2005-12, BSD License
* @author Christian Gruen
* @author Toshio HIRAI (Porting to PHP)
* @license http://opensource.org/licenses/BSD-3-Clause The BSD License
*/

class TokenUtils {
	private function __construct(){}
	
	public static function startsWith($haystack, $needle) {
		if (($c = count($haystack)) < ($cc = count($needle))) {
			return false;
		}
		if ($c == 0) return false;
		if ($cc == 0) return false;
		for($i = 0; $i < $cc; $i++) {
			if ($haystack[$i] != $needle[$i]) {
				return false;
			}
		}
		return true;
	}

	public static function endsWith($haystack, $needle) {
		if (($c = count($haystack)) < ($cc = count($needle))) {
			return false;
		}
		if ($c == 0) return false;
		if ($cc == 0) return false;
		for($j = 0, $i = ($c-$cc); $i < $c; $i++) {
			if ($haystack[$i] != $needle[$j++]) {
				return false;
			}
		}
		return true;
	}
	
	public static function equals($chars1, $chars2) {
//		return $chars1 === $chars2;
		if (($c = count($chars1)) != count($chars2)) {
			return false;
		}
		if ($c == 0) return false;
		for($i = 0; $i < $c; $i++) {
			if ($chars1[$i] != $chars2[$i]) {
				return false;
			}
		}
		return true;
	}

	public static function contains($haystack, $needle) {
		if (($c = count($haystack)) < ($cc = count($needle))) {
			return false;
		}
		$j = 0;
		$f = false;
		if ($c == 0) return false;
		if ($cc == 0) return false;
		for($i = 0; $i < $c; $i++) {
			if ($haystack[$i] == $needle[$j]) {
				$f = true;
				for($j = 1; $j < $cc; $j++) {
					if ($haystack[++$i] != $needle[$j]) {
						return false;
					}
				}
			}
		}
		if ($f) {
			return true;
		}
		return false;
	}

	public static function toChars($str, $encoding=null) {
		if ($str == "" || $str == null) return array();
		if ($encoding == null) {
			$encoding = mb_detect_encoding($str);
		}
		$dicEnc = "UTF-16LE";
		return array_values(unpack("S*", mb_convert_encoding($str, $dicEnc, $encoding)));
	}

	public static function toChar($str) {
		//Evaluate only the beginning of one character
		$chars = self::toChars($str);
		return $chars[0];
	}

	public static function toString(array $chars, $encoding=null) {
		if ($encoding == null) {
			$encoding = mb_internal_encoding();
		}
		$dicEnc = "UTF-16LE";
		$s = "";
		foreach($chars as $c) {
			$s .= mb_convert_encoding(pack("S*", $c), $encoding, $dicEnc);;
		}
		return $s;
	}

	public static function s($val) {
		//Convert one character.
		if (!is_numeric($val)) return "";
		return self::toString(array($val));
	}

	public static function toHiragana(array $chars) {
		$r = array();
		foreach($chars as $c) {
			if ($c >= 12448 && $c <= 12543) {
				$r[] = $c - 96;
			} else {
				$r[] = $c;
			}
		}
		return $r;
	}
	
	public static function toKatakana(array $chars) {
		$r = array();
		foreach($chars as $c) {
			if ($c >= 12352 && $c <= 12447) {
				$r[] = $c + 96;
			} else {
				$r[] = $c;
			}
		}
		return $r;
	}
	
	/** 
     * 0x3040-0x309F
     * @param array $chars 
     * @return bool
     */
	public static function hiragana(array $chars) {
		foreach($chars as $c) {
			if ($c < 12352) return false;
			if ($c > 12447) return false;
		}
		return true;
	}
	
	/** 
     * 0x30A0-0x30FF
     * @param array $chars
     * @return bool
     */
	public static function katakana(array $chars) {
		foreach($chars as $c) {
			if ($c < 12448) return false;
			if ($c > 12543) return false;
		}
		return true;
	}
	
	public static function lower(array $chars) {
		$r = array();
		foreach($chars as $c) {
			$r[] = self::lc($c);
		}
		return $r;
	}
	
	public static function lc($chr) {
		if ($chr >= 65 && $chr <= 90) return $chr + 32;
		else return $chr;
	}

	/** 
     * 0x0041-0x005A, 0x0061-0x007A
     * @param array $chars
     * @return bool
     */
	public static function letter($chars) {
		foreach($chars as $c) {
			if (($c < 65 || $c > 90) && ($c < 97 || $c > 122)) return false;
		}
		return true;
	}

	/** 
     * 0x0030-0x0039
     * @param array $chars
     * @return bool
     */
	public static function digit($chars) {
		foreach($chars as $c) {
			if ($c < 48 || $c > 57) return false;
		}
		return true;
	}

	public static function letterOrDigit($chars) {
		return self::letter($chars) || self::digit($chars);
	}

	public static function kanji($chars) {
		return !self::hiragana($chars) && !self::katakana($chars) && !self::western($chars);
	}
	
	/** 
     * 0x0000-0x007E
     * @param array $chars
     * @return bool
     */
	public static function ascii($chars) {
		foreach($chars as $c) {
			if ($c >= 127) return false;
		}
		return true;
	}

	/**
	 * ASCII制御文字(Control Character)か否か
	 * (0x09, 0x10, 0x13 は例外と見なす)
	 * @param int $c
	 * @return bool is control character
	 */
	public static function cc($c) {
		if ($c >= 0 && $c <= 31 && $c != 9 && $c != 10 && $c != 13) return true;
		if ($c == 127) return true;
		return false;
	}

	/** 
     * 0x0000-0x01FF
     * @param array $chars
     * @return bool
     */
	public static function western($chars) {
		foreach($chars as $c) {
			if ($c >= 512) return false;
		}
		return true;
	}

	/**
	 * Returns a normalized character without diacritics.
	 * This method supports all latin1 characters, including supplements.
     * @param array $chars
     * @return array
     * 
	 */
	public static function dia($chars) {
		if (!self::western($chars)) {
			return $chars;
		}
		$res = array();
		foreach($chars as $c) {
			if ($c < 192) {
				$res[] = $c;
			} else {
				$nc = self::NC();
				$r = ord($nc[self::hex($c)]);
				if (empty($r)) {
					$res[] = $c;
				} else {
					$res[] = $r;
				}
			}
		}
		return $res;
	}

	public static function hex($char) {
		$h = strtoupper(dechex($char));
		switch(strlen($h)){
			case 0:  return "0x0000";
			case 1:  return "0x000".$h;
			case 2:  return "0x00" .$h;
			case 3:  return "0x0"  .$h;
			default: return "0x"   .$h;
		}
	}

  	/** Normalized characters. */
	public static function NC() {
		return array(
			'0x00C0'=>'A' ,  '0x00C1'=>'A' ,  '0x00C2'=>'A' ,  '0x00C3'=>'A' ,
		    '0x00C4'=>'A' ,  '0x00C5'=>'A' ,  '0x00C6'=>'A' ,  '0x00C7'=>'C' ,
		    '0x00C8'=>'E' ,  '0x00C9'=>'E' ,  '0x00CA'=>'E' ,  '0x00CB'=>'E' ,
		    '0x00CC'=>'I' ,  '0x00CD'=>'I' ,  '0x00CE'=>'I' ,  '0x00CF'=>'I' ,
		    '0x00D0'=>'D' ,  '0x00D1'=>'N' ,  '0x00D2'=>'O' ,  '0x00D3'=>'O' ,
		    '0x00D4'=>'O' ,  '0x00D5'=>'O' ,  '0x00D6'=>'O' ,  '0x00D8'=>'O' ,
		    '0x00D9'=>'U' ,  '0x00DA'=>'U' ,  '0x00DB'=>'U' ,  '0x00DC'=>'U' ,
		    '0x00DD'=>'Y' ,  '0x00DE'=>'d' ,  '0x00DF'=>'s' ,  '0x00E0'=>'a' ,
		    '0x00E1'=>'a' ,  '0x00E2'=>'a' ,  '0x00E3'=>'a' ,  '0x00E4'=>'a' ,
		    '0x00E5'=>'a' ,  '0x00E6'=>'a' ,  '0x00E7'=>'c' ,  '0x00E8'=>'e' ,
		    '0x00E9'=>'e' ,  '0x00EA'=>'e' ,  '0x00EB'=>'e' ,  '0x00EC'=>'i' ,
		    '0x00ED'=>'i' ,  '0x00EE'=>'i' ,  '0x00EF'=>'i' ,  '0x00F0'=>'d' ,
		    '0x00F1'=>'n' ,  '0x00F2'=>'o' ,  '0x00F3'=>'o' ,  '0x00F4'=>'o' ,
		    '0x00F5'=>'o' ,  '0x00F6'=>'o' ,  '0x00F8'=>'o' ,  '0x00F9'=>'u' ,
		    '0x00FA'=>'u' ,  '0x00FB'=>'u' ,  '0x00FC'=>'u' ,  '0x00FD'=>'y' ,
		    '0x00FE'=>'d' ,  '0x00FF'=>'y' ,  '0x0100'=>'A' ,  '0x0101'=>'a' ,
		    '0x0102'=>'A' ,  '0x0103'=>'a' ,  '0x0104'=>'A' ,  '0x0105'=>'a' ,
		    '0x0106'=>'C' ,  '0x0107'=>'c' ,  '0x0108'=>'C' ,  '0x0109'=>'c' ,
		    '0x010A'=>'C' ,  '0x010B'=>'c' ,  '0x010C'=>'C' ,  '0x010D'=>'c' ,
		    '0x010E'=>'D' ,  '0x010F'=>'d' ,  '0x0110'=>'D' ,  '0x0111'=>'d' ,
		    '0x0112'=>'E' ,  '0x0113'=>'e' ,  '0x0114'=>'E' ,  '0x0115'=>'e' ,
		    '0x0116'=>'E' ,  '0x0117'=>'e' ,  '0x0118'=>'E' ,  '0x0119'=>'e' ,
		    '0x011A'=>'E' ,  '0x011B'=>'e' ,  '0x011C'=>'G' ,  '0x011D'=>'g' ,
		    '0x011E'=>'G' ,  '0x011F'=>'g' ,  '0x0120'=>'G' ,  '0x0121'=>'g' ,
		    '0x0122'=>'G' ,  '0x0123'=>'g' ,  '0x0124'=>'H' ,  '0x0125'=>'h' ,
		    '0x0126'=>'H' ,  '0x0127'=>'h' ,  '0x0128'=>'I' ,  '0x0129'=>'i' ,
		    '0x012A'=>'I' ,  '0x012B'=>'i' ,  '0x012C'=>'I' ,  '0x012D'=>'i' ,
		    '0x012E'=>'I' ,  '0x012F'=>'i' ,  '0x0130'=>'I' ,  '0x0131'=>'i' ,
		    '0x0132'=>'I' ,  '0x0133'=>'i' ,  '0x0134'=>'J' ,  '0x0135'=>'j' ,
		    '0x0136'=>'K' ,  '0x0137'=>'k' ,  '0x0138'=>'k' ,  '0x0139'=>'L' ,
		    '0x013A'=>'l' ,  '0x013B'=>'L' ,  '0x013C'=>'l' ,  '0x013D'=>'L' ,
		    '0x013E'=>'l' ,  '0x013F'=>'L' ,  '0x0140'=>'l' ,  '0x0141'=>'L' ,
		    '0x0142'=>'l' ,  '0x0143'=>'N' ,  '0x0144'=>'n' ,  '0x0145'=>'N' ,
		    '0x0146'=>'n' ,  '0x0147'=>'N' ,  '0x0148'=>'n' ,  '0x0149'=>'n' ,
		    '0x014A'=>'N' ,  '0x014B'=>'n' ,  '0x014C'=>'O' ,  '0x014D'=>'o' ,
		    '0x014E'=>'O' ,  '0x014F'=>'o' ,  '0x0150'=>'O' ,  '0x0151'=>'o' ,
		    '0x0152'=>'O' ,  '0x0153'=>'o' ,  '0x0154'=>'R' ,  '0x0155'=>'r' ,
		    '0x0156'=>'R' ,  '0x0157'=>'r' ,  '0x0158'=>'R' ,  '0x0159'=>'r' ,
		    '0x015A'=>'S' ,  '0x015B'=>'s' ,  '0x015C'=>'S' ,  '0x015D'=>'s' ,
		    '0x015E'=>'S' ,  '0x015F'=>'s' ,  '0x0160'=>'S' ,  '0x0161'=>'s' ,
		    '0x0162'=>'T' ,  '0x0163'=>'t' ,  '0x0164'=>'T' ,  '0x0165'=>'t' ,
		    '0x0166'=>'T' ,  '0x0167'=>'t' ,  '0x0168'=>'U' ,  '0x0169'=>'u' ,
		    '0x016A'=>'U' ,  '0x016B'=>'u' ,  '0x016C'=>'U' ,  '0x016D'=>'u' ,
		    '0x016E'=>'U' ,  '0x016F'=>'u' ,  '0x0170'=>'U' ,  '0x0171'=>'u' ,
		    '0x0172'=>'U' ,  '0x0173'=>'u' ,  '0x0174'=>'W' ,  '0x0175'=>'w' ,
		    '0x0176'=>'Y' ,  '0x0177'=>'y' ,  '0x0178'=>'Y' ,  '0x0179'=>'Z' ,
		    '0x017A'=>'z' ,  '0x017B'=>'Z' ,  '0x017C'=>'z' ,  '0x017D'=>'Z' ,
		    '0x017E'=>'z' ,  '0x01FA'=>'A' ,  '0x01FB'=>'a' ,  '0x01FC'=>'A' ,
		    '0x01FD'=>'a' ,  '0x01FE'=>'O' ,  '0x01FF'=>'o'
		);
	}
}
