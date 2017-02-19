<?php

use PHPUnit\Framework\TestCase;

use lola\io\mime\parser\JSONMimeParser;



final class JSONMimeParserTest
extends TestCase
{

	public function testParse() {
		$ins = new JSONMimeParser();

		$this->assertEquals([], $ins->parse('[]'));
		$this->assertEquals([], $ins->parse('{}'));
		$this->assertEquals([
			'foo' => 1,
			'bar' => 2,
			'baz' => [
				'a',
				'b',
				'c'
			]
		], $ins->parse('{"foo":1,"bar":2,"baz":["a","b","c"]}'));
	}

	public function testStringify() {
		$ins = new JSONMimeParser();

		$this->assertEquals('[]', $ins->stringify([]));
		$this->assertEquals('{"foo":1}', $ins->stringify([ 'foo' => 1 ]));
	}
}
