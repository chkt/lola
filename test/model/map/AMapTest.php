<?php

namespace test\model\map;

use PHPUnit\Framework\TestCase;

use lola\model\map\IResourceMap;
use lola\model\map\AMap;



final class AMapTest
extends TestCase
{

	private function _mockResource() {
		return $this
			->getMockBuilder(IResourceMap::class)
			->getMock();
	}

	private function _mockMap(IResourceMap& $resource, string $base = 'foo') {
		return $this
			->getMockBuilder(AMap::class)
			->setConstructorArgs([ & $resource, $base ])
			->getMockForAbstractClass();
	}

	public function testHasKey() {
		$resource = $this->_mockResource();

		$resource
			->expects($this->at(0))
			->method('hasKey')
			->with($this->equalTo('foo.bar'))
			->willReturn(true);

		$resource
			->expects($this->at(1))
			->method('hasKey')
			->with($this->equalTo('foo.baz'))
			->willReturn(false);

		$map = $this->_mockMap($resource);

		$this->assertTrue($map->hasKey('bar'));
		$this->assertFalse($map->hasKey('baz'));
	}


	public function testGetBool() {
		$resource = $this->_mockResource();

		$resource
			->expects($this->at(0))
			->method('getBool')
			->with($this->equalTo('foo.bar'))
			->willReturn(true);

		$resource
			->expects($this->at(1))
			->method('getBool')
			->with($this->equalTo('foo.baz'))
			->willReturn(false);

		$map = $this->_mockMap($resource);

		$this->assertTrue($map->getBool('bar'));
		$this->assertFalse($map->getBool('baz'));
	}

	public function testSetBool() {
		$key = '';
		$value = false;

		$resource = $this->_mockResource();

		$resource
			->expects($this->any())
			->method('setBool')
			->with($this->isType('string'), $this->isType('bool'))
			->willReturnCallback(function(string $k, bool $v) use (& $resource, & $key, & $value) {
				$key = $k;
				$value = $v;

				return $resource;
			});

		$map = $this->_mockMap($resource);

		$this->assertEquals($map, $map->setBool('bar', true));
		$this->assertEquals('foo.bar', $key);
		$this->assertTrue($value);
		$this->assertEquals($map, $map->setBool('baz', false));
		$this->assertEquals('foo.baz', $key);
		$this->assertFalse($value);
	}


	public function testGetInt() {
		$resource = $this->_mockResource();

		$resource
			->expects($this->at(0))
			->method('getInt')
			->with($this->equalTo('foo.bar'))
			->willReturn(0);

		$resource
			->expects($this->at(1))
			->method('getInt')
			->with($this->equalTo('foo.baz'))
			->willReturn(1);

		$map = $this->_mockMap($resource);

		$this->assertEquals(0, $map->getInt('bar'));
		$this->assertEquals(1, $map->getInt('baz'));
	}

	public function testSetInt() {
		$key = '';
		$value = 0;

		$resource = $this->_mockResource();

		$resource
			->expects($this->any())
			->method('setInt')
			->with($this->isType('string'), $this->isType('int'))
			->willReturnCallback(function(string $k, string $v) use (& $resource, & $key, & $value) {
				$key = $k;
				$value = $v;

				return $resource;
			});

		$map = $this->_mockMap($resource);

		$this->assertEquals($map, $map->setInt('bar', 1));
		$this->assertEquals('foo.bar', $key);
		$this->assertEquals(1, $value);
		$this->assertEquals($map, $map->setInt('baz', 2));
		$this->assertEquals('foo.baz', $key);
		$this->assertEquals(2, $value);
	}


	public function testGetFloat() {
		$resource = $this->_mockResource();

		$resource
			->expects($this->at(0))
			->method('getFloat')
			->with($this->equalTo('foo.bar'))
			->willReturn(0.1);

		$resource
			->expects($this->at(1))
			->method('getFloat')
			->with($this->equalTo('foo.baz'))
			->willReturn(1.1);

		$map = $this->_mockMap($resource);

		$this->assertEquals(0.1, $map->getFloat('bar'));
		$this->assertEquals(1.1, $map->getFloat('baz'));
	}

	public function testSetFloat() {
		$key = '';
		$value = 0.5;

		$resource = $this->_mockResource();

		$resource
			->expects($this->any())
			->method('setFloat')
			->with($this->isType('string'), $this->isType('float'))
			->willReturnCallback(function(string $k, float $v) use (& $resource, & $key, & $value) {
				$key = $k;
				$value = $v;

				return $resource;
			});

		$map = $this->_mockMap($resource);

		$this->assertEquals($map, $map->setFloat('bar', 1.5));
		$this->assertEquals('foo.bar', $key);
		$this->assertEquals(1.5, $value);
		$this->assertEquals($map, $map->setFloat('baz', 2.5));
		$this->assertEquals('foo.baz', $key);
		$this->assertEquals(2.5, $value);
	}


	public function testGetString() {
		$resource = $this->_mockResource();

		$resource
			->expects($this->at(0))
			->method('getString')
			->with($this->equalTo('foo.bar'))
			->willReturn('quux');

		$resource
			->expects($this->at(1))
			->method('getString')
			->with($this->equalTo('foo.baz'))
			->willReturn('bang');

		$map = $this->_mockMap($resource);

		$this->assertEquals('quux', $map->getString('bar'));
		$this->assertEquals('bang', $map->getString('baz'));
	}

	public function testSetString() {
		$key = '';
		$value = '';

		$resource = $this->_mockResource();

		$resource
			->expects($this->any())
			->method('setString')
			->with($this->isType('string'), $this->isType('string'))
			->willReturnCallback(function(string $k, string $v) use (& $resource, & $key, & $value) {
				$key = $k;
				$value = $v;

				return $resource;
			});

		$map = $this->_mockMap($resource);

		$this->assertEquals($map, $map->setString('bar', 'quux'));
		$this->assertEquals('foo.bar', $key);
		$this->assertEquals('quux', $value);
		$this->assertEquals($map, $map->setString('baz', 'bang'));
		$this->assertEquals('foo.baz', $key);
		$this->assertEquals('bang', $value);
	}
}
