<?php

namespace lola\io\mime\parser;

use lola\io\mime\IMimeParser;



final class JSONMimeParser
implements IMimeParser
{

	const VERSION = '0.6.1';



	public function stringify(array $payload) : string {
		return json_encode($payload);
	}

	public function parse(string $string) : array {
		$res = json_decode($string, true);

		if (is_null($res)) throw new \ErrorException();

		return $res;
	}
}
