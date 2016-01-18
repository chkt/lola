<?php

namespace chkt\log;



class Colorizer {
	
	const F_BLACK = 30;
	const F_WHITE = 37;
	const F_RED = 31;
	const F_YELLOW = 33;
	const F_GREEN = 32;
	const F_CYAN = 36;
	const F_BLUE = 34;
	const F_MAGENTA = 35;
	const B_BLACK = 40;
	const B_WHITE = 47;
	const B_RED = 41;
	const B_YELLOW = 43;
	const B_GREEN = 42;
	const B_CYAN = 46;
	const B_BLUE = 44;
	const B_MAGENTA = 45;
	const MOD_BRIGHT = 1;
	const MOD_FAINT = 2;
	const MOD_ITALIC = 3;
	const MOD_UNDERLINE = 4;
	const MOD_BLINK = 5;
	
	
	static public function isCode($code) {
		return in_array($code, [
			self::F_BLACK,
			self::F_WHITE,
			self::F_RED,
			self::F_YELLOW,
			self::F_GREEN,
			self::F_CYAN,
			self::F_BLUE,
			self::F_MAGENTA,
			self::B_BLACK,
			self::B_WHITE,
			self::B_RED,
			self::B_YELLOW,
			self::B_GREEN,
			self::B_CYAN,
			self::B_BLUE,
			self::B_MAGENTA,
			self::MOD_BRIGHT,
			self::MOD_FAINT,
			self::MOD_ITALIC,
			self::MOD_UNDERLINE,
			self::MOD_BLINK
		]);
	}
	
	
	static public function encode($str, Array $formats) {
		$res = '';
		$escape = false;
		
		foreach($formats as $format) {
			if (!self::isCode($format)) continue;
			
			$res .= "\033[" . $format . 'm';
			$escape = true;
		}
		
		return $res . $str . ($escape ? "\033[0m" : '');
	}
}
