<?php

use PHPUnit\Framework\TestCase;

use lola\io\mime\parser\FormMimeParser;



final class FormMimeParserTest
extends TestCase
{

	public function testParse() {
		$ins = new FormMimeParser();

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
		$ins = new FormMimeParser();

		$this->assertEquals('foo=1&bar=2&baz=3', $ins->stringify([
			'foo' => 1,
			'bar' => 2,
			'baz' => 3
		]));
	}
}
