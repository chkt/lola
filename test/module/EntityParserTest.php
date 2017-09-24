<?php

use PHPUnit\Framework\TestCase;

use eve\entity\IEntityParser;
use lola\module\EntityParser;



final class EntityParserTest
extends TestCase
{


	private function _produceParser() {
		return new EntityParser();
	}


	public function testInheritance() {
		$ins = $this->_produceParser();

		$this->assertInstanceOf(IEntityParser::class, $ins);
	}


	public function testParse_type() {
		$ins = $this->_produceParser();

		$this->assertEquals([
			EntityParser::COMPONENT_TYPE => 'foo',
			EntityParser::COMPONENT_LOCATION => 'bar'
		], $ins->parse('foo:bar'));

		$this->assertEquals([
			EntityParser::COMPONENT_TYPE => '',
			EntityParser::COMPONENT_LOCATION => 'bar'
		], $ins->parse('bar'));
	}

	public function testParse_module() {
		$ins = $this->_produceParser();

		$this->assertEquals([
			EntityParser::COMPONENT_TYPE => 'foo',
			EntityParser::COMPONENT_MODULE => 'bar',
			EntityParser::COMPONENT_DESCRIPTOR => '/baz'
		], $ins->parse('foo://bar/baz', EntityParser::COMPONENT_MODULE));

		$this->assertEquals([
			EntityParser::COMPONENT_TYPE => '',
			EntityParser::COMPONENT_MODULE => 'bar',
			EntityParser::COMPONENT_DESCRIPTOR => '/baz'
		], $ins->parse('//bar/baz', EntityParser::COMPONENT_MODULE));

		$this->assertEquals([
			EntityParser::COMPONENT_TYPE => 'foo',
			EntityParser::COMPONENT_MODULE => '',
			EntityParser::COMPONENT_DESCRIPTOR => '/baz'
		], $ins->parse('foo:/baz', EntityParser::COMPONENT_MODULE));

		$this->assertEquals([
			EntityParser::COMPONENT_TYPE => '',
			EntityParser::COMPONENT_MODULE => '',
			EntityParser::COMPONENT_DESCRIPTOR => '/baz'
		], $ins->parse('/baz', EntityParser::COMPONENT_MODULE));

		$this->assertEquals([
			EntityParser::COMPONENT_TYPE => '',
			EntityParser::COMPONENT_MODULE => '',
			EntityParser::COMPONENT_DESCRIPTOR => 'baz'
		], $ins->parse('baz', EntityParser::COMPONENT_MODULE));
	}

	public function testParse_nameConfig() {
		$ins = $this->_produceParser();

		$this->assertEquals([
			EntityParser::COMPONENT_TYPE => 'foo',
			EntityParser::COMPONENT_MODULE => 'bar',
			EntityParser::COMPONENT_NAME => '/baz',
			EntityParser::COMPONENT_CONFIG => 'id=quux'
		], $ins->parse('foo://bar/baz?id=quux', EntityParser::COMPONENT_NAME));

		$this->assertEquals([
			EntityParser::COMPONENT_TYPE => '',
			EntityParser::COMPONENT_MODULE => '',
			EntityParser::COMPONENT_NAME => '/baz',
			EntityParser::COMPONENT_CONFIG => ''
		], $ins->parse('/baz', EntityParser::COMPONENT_NAME));

		$this->assertEquals([
			EntityParser::COMPONENT_TYPE => '',
			EntityParser::COMPONENT_MODULE => '',
			EntityParser::COMPONENT_NAME => 'baz',
			EntityParser::COMPONENT_CONFIG => ''
		], $ins->parse('baz', EntityParser::COMPONENT_NAME));

		$this->assertEquals([
			EntityParser::COMPONENT_TYPE => '',
			EntityParser::COMPONENT_MODULE => '',
			EntityParser::COMPONENT_NAME => '',
			EntityParser::COMPONENT_CONFIG => 'id=quux'
		], $ins->parse('?id=quux', EntityParser::COMPONENT_NAME));
	}

	public function testParse_empty() {
		$ins = $this->_produceParser();

		$this->expectException(\ErrorException::class);

		$ins->parse('');
	}

	public function testParse_malformed() {
		$ins = $this->_produceParser();

		$this->expectException(\ErrorException::class);
		$this->expectExceptionMessage('ENT malformed entity ":"');

		$ins->parse(':');
	}
}
