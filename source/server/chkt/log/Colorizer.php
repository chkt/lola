<?php

namespace chkt\log;



class Colorizer {
	
	static private $_ANSI_FORMAT = [
		'f-black'   => 30,
		'f-white'   => 37,
		'f-red'     => 31,
		'f-yellow'  => 33,
		'f-green'   => 32,
		'f-cyan'    => 36,
		'f-blue'    => 34,
		'f-magenta' => 35,
		'b-black'   => 40,
		'b-white'   => 47,
		'b-red'     => 41,
		'b-yellow'  => 43,
		'b-green'   => 42,
		'b-cyan'    => 46,
		'b-blue'    => 44,
		'b-magenta' => 45,
		'bright'    => 1,
		'faint'     => 2,
		'italic'    => 3,
		'underline' => 4,
		'blink'     => 5,
	];
	
	
	static public function encode($str) {
		$res = '';
		$escape = '';
		$formats = func_get_args();
		
		foreach($formats as $format) {
			if (!array_key_exists($format, self::$_ANSI_FORMAT)) continue;
			
			$res .= "\033[" . self::$_ANSI_FORMAT[$format] . 'm';
			$escape = true;
		}
		
		return $res . $str . ($escape ? "\033[0m" : '');
	}
}
