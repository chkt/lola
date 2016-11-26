<?php

namespace lola\io\http\payload;



interface IPayloadParser
{

	public function stringify(array $payload) : string;

	public function parse(string $string) : array;
}
