<?php

namespace lola\io\mime;



interface IMimeParser
{

	public function stringify(array $payload) : string;

	public function parse(string $string) : array;
}
