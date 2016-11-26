<?php

use PHPUnit\Framework\TestCase;
use lola\io\http\payload\FormPayloadParser;



final class FormPayloadParserTest
extends TestCase
{

	public function testParse() {
		$ins = new FormPayloadParser();

		$this->assertEquals([
			'foo' => '1',
			'bar' => '2',
			'baz' => '3'
		], $ins->parse('foo=1&bar=2&baz=3'));

		$this->assertEquals([
			'foo' => '1,2,3',
			'bar' => '4,5,6'
		], $ins->parse('foo=1,2,3&bar=4,5,6'));
	}

	public function testStringify() {
		$ins = new FormPayloadParser();

		$this->assertEquals('foo=1&bar=2&baz=3', $ins->stringify([
			'foo' => 1,
			'bar' => 2,
			'baz' => 3
		]));
	}
}
