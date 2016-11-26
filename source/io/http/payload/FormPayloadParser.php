<?php

namespace lola\io\http\payload;

use lola\io\http\payload\IPayloadParser;



class FormPayloadParser
implements IPayloadParser
{

	const VERSION = '0.5.0';



	public function stringify(array $payload) : string {
		return http_build_query($payload, '', '&', PHP_QUERY_RFC3986);
	}

	public function parse(string $string) : array {
		$res = [];

		parse_str($string, $res);

		return $res;
	}
}
