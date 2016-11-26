<?php

use PHPUnit\Framework\TestCase;
use lola\io\http\payload\JSONPayloadParser;



final class JSONPayloadParserTest
extends TestCase
{

	public function testParse() {
		$ins = new JSONPayloadParser();

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
		$ins = new JSONPayloadParser();

		$this->assertEquals('[]', $ins->stringify([]));
		$this->assertEquals('{"foo":1}', $ins->stringify([ 'foo' => 1 ]));
	}
}
