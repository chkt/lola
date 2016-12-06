<?php

use PHPUnit\Framework\TestCase;

use lola\module\EntityParser;



final class EntityParserTest
extends TestCase
{

	public function testParse() {
		$this->assertEquals([
			EntityParser::PROP_TYPE => 'injector',
			EntityParser::PROP_MODULE => '',
			EntityParser::PROP_NAME => '',
			EntityParser::PROP_ID => ''
		], EntityParser::parse('injector:'));

		$this->assertEquals([
			EntityParser::PROP_TYPE => '',
			EntityParser::PROP_MODULE => 'module',
			EntityParser::PROP_NAME => '',
			EntityParser::PROP_ID => ''
		], EntityParser::parse('//module'));

		$this->assertEquals([
			EntityParser::PROP_TYPE => '',
			EntityParser::PROP_MODULE => '',
			EntityParser::PROP_NAME => 'name',
			EntityParser::PROP_ID => ''
		], EntityParser::parse('name'));

		$this->assertEquals([
			EntityParser::PROP_TYPE => '',
			EntityParser::PROP_MODULE => '',
			EntityParser::PROP_NAME => 'name',
			EntityParser::PROP_ID => ''
		], EntityParser::parse('/name/'));

		$this->assertEquals([
			EntityParser::PROP_TYPE => '',
			EntityParser::PROP_MODULE => '',
			EntityParser::PROP_NAME => 'path\\to\\name',
			EntityParser::PROP_ID => ''
		], EntityParser::parse('/path/to/name/'));

		$this->assertEquals([
			EntityParser::PROP_TYPE => '',
			EntityParser::PROP_MODULE => '',
			EntityParser::PROP_NAME => '',
			EntityParser::PROP_ID => 'foo'
		], EntityParser::parse('?foo'));

		$this->assertEquals([
			EntityParser::PROP_TYPE => 'service',
			EntityParser::PROP_MODULE => 'module',
			EntityParser::PROP_NAME => 'path\\to\\service',
			EntityParser::PROP_ID => 'foo'
		], EntityParser::parse('service://module/path/to/service?foo'));
	}

	public function testExtractType() {
		$this->assertEquals([
			EntityParser::PROP_TYPE => 'injector',
			EntityParser::PROP_LOCATION => ''
		], EntityParser::extractType('injector:'));

		$this->assertEquals([
			EntityParser::PROP_TYPE => '',
			EntityParser::PROP_LOCATION => '//module/path/to/service?foo'
		], EntityParser::extractType('//module/path/to/service?foo'));

		$this->assertEquals([
			EntityParser::PROP_TYPE => 'service',
			EntityParser::PROP_LOCATION => '//module/path/to/service?foo'
		], EntityParser::extractType('service://module/path/to/service?foo'));
	}
}
