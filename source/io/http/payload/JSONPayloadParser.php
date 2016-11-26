<?php

namespace lola\io\http\payload;

use lola\io\http\payload\IPayloadParser;



final class JSONPayloadParser
implements IPayloadParser
{

	const VERSION = '0.5.0';



	public function stringify(array $payload) : string {
		return json_encode($payload);
	}

	public function parse(string $string) : array {
		$res = json_decode($string, true);

		if (is_null($res)) throw new \ErrorException();

		return $res;
	}
}
