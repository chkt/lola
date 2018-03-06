<?php

namespace test\common\uri;

use PHPUnit\Framework\TestCase;

use eve\common\ITokenizer;
use lola\common\uri\KeyValueTokenizer;



final class KeyValueTokenizerTest
extends TestCase
{

	private $_parser;


	protected function setUp() {
		$this->_parser = new KeyValueTokenizer();
	}

	public function testInheritance() {
		$this->assertInstanceOf(ITokenizer::class, $this->_parser);
	}

	public function testParse_e() {
		$this->expectException(\ErrorException::class);
		$this->expectExceptionMessage('PRS empty keyless value "" in ""');

		$this->_parser->parse('');
	}

	public function testParse_v() {
		$this->assertEquals([ 'foo' ], $this->_parser->parse('foo'));
	}

	public function testParse_vv() {
		$this->assertEquals([ 'foo', 'bar' ], $this->_parser->parse('foo,bar'));
	}

	public function testParse_v2() {
		$this->assertEquals([ 'foo'], $this->_parser->parse('foo,foo'));
	}


	public function testParse_k() {
		$this->assertEquals([ 'foo' => '' ], $this->_parser->parse('foo='));
	}

	public function testParse_b() {
		$this->expectException(\ErrorException::class);
		$this->expectExceptionMessage('PRS empty key "=" in "="');

		$this->_parser->parse('=');
	}

	public function testParse_kv() {
		$this->assertEquals([ 'foo' => 'bar' ], $this->_parser->parse('foo=bar'));
	}

	public function testParse_ke() {
		$this->assertEquals([ 'foo' => '' ], $this->_parser->parse('foo='));
	}

	public function testParse_kvb() {
		$this->expectException(\ErrorException::class);
		$this->expectExceptionMessage('PRS empty key "=baz" in "foo=bar&=baz"');

		$this->_parser->parse('foo=bar&=baz');
	}

	public function testParse_ekv() {
		$this->expectException(\ErrorException::class);
		$this->expectExceptionMessage('PRS empty keyless value "foo," in "foo,&bar=baz"');

		$this->_parser->parse('foo,&bar=baz');
	}

	public function testParse_kvv() {
		$this->assertEquals([ 'foo' => [ 'bar', 'baz' ]], $this->_parser->parse('foo=bar,baz'));
	}

	public function testParse_kv2() {
		$this->assertEquals([ 'foo' => [ 'bar', 'bar' ]], $this->_parser->parse('foo=bar,bar'));
	}

	public function testParse_kev() {
		$this->assertEquals([ 'foo' => [ '', 'bar' ]], $this->_parser->parse('foo=,bar'));
	}

	public function testParse_kvkv() {
		$this->assertEquals([ 'foo' => [ 'bar','baz' ]], $this->_parser->parse('foo=bar&foo=baz'));
	}

	public function testParse_kekv() {
		$this->assertEquals([ 'foo' => ['', 'bar' ]], $this->_parser->parse('foo=&foo=bar'));
	}

}
