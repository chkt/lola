<?php

namespace lola\io\mime\parser;

use lola\io\mime\IMimeParser;



class FormMimeParser
implements IMimeParser
{

	const VERSION = '0.6.1';



	public function stringify(array $payload) : string {
		return http_build_query($payload, '', '&', PHP_QUERY_RFC3986);
	}

	public function parse(string $string) : array {
		$res = [];

		parse_str($string, $res);

		return $res;
	}
}
