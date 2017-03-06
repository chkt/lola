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
			->with($this->equalTo('foo_bar'))
			->willReturn(true);

		$resource
			->expects($this->at(1))
			->method('hasKey')
			->with($this->equalTo('foo_baz'))
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
			->with($this->equalTo('foo_bar'))
			->willReturn(true);

		$resource
			->expects($this->at(1))
			->method('getBool')
			->with($this->equalTo('foo_baz'))
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
		$this->assertEquals('foo_bar', $key);
		$this->assertTrue($value);
		$this->assertEquals($map, $map->setBool('baz', false));
		$this->assertEquals('foo_baz', $key);
		$this->assertFalse($value);
	}


	public function testGetInt() {
		$resource = $this->_mockResource();

		$resource
			->expects($this->at(0))
			->method('getInt')
			->with($this->equalTo('foo_bar'))
			->willReturn(0);

		$resource
			->expects($this->at(1))
			->method('getInt')
			->with($this->equalTo('foo_baz'))
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
		$this->assertEquals('foo_bar', $key);
		$this->assertEquals(1, $value);
		$this->assertEquals($map, $map->setInt('baz', 2));
		$this->assertEquals('foo_baz', $key);
		$this->assertEquals(2, $value);
	}


	public function testGetFloat() {
		$resource = $this->_mockResource();

		$resource
			->expects($this->at(0))
			->method('getFloat')
			->with($this->equalTo('foo_bar'))
			->willReturn(0.1);

		$resource
			->expects($this->at(1))
			->method('getFloat')
			->with($this->equalTo('foo_baz'))
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
		$this->assertEquals('foo_bar', $key);
		$this->assertEquals(1.5, $value);
		$this->assertEquals($map, $map->setFloat('baz', 2.5));
		$this->assertEquals('foo_baz', $key);
		$this->assertEquals(2.5, $value);
	}


	public function testGetString() {
		$resource = $this->_mockResource();

		$resource
			->expects($this->at(0))
			->method('getString')
			->with($this->equalTo('foo_bar'))
			->willReturn('quux');

		$resource
			->expects($this->at(1))
			->method('getString')
			->with($this->equalTo('foo_baz'))
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
		$this->assertEquals('foo_bar', $key);
		$this->assertEquals('quux', $value);
		$this->assertEquals($map, $map->setString('baz', 'bang'));
		$this->assertEquals('foo_baz', $key);
		$this->assertEquals('bang', $value);
	}


	public function testGetList() {
		$list0 = ['foo','bar','baz'];
		$list1 = ['foo','bang','quux'];
		$resource = $this->_mockResource();

		$resource
			->expects($this->at(0))
			->method('getList')
			->with($this->equalTo('foo_bar'))
			->willReturn($list0);

		$resource
			->expects($this->at(1))
			->method('getList')
			->with($this->equalTo('foo_baz'))
			->willReturn($list1);

		$map = $this->_mockMap($resource);

		$this->assertEquals($list0, $map->getList('bar'));
		$this->assertEquals($list1, $map->getList('baz'));
	}

	public function testSetList() {
		$list0 = [ 'foo', 'bar', 'baz' ];
		$list1 = [ 'foo', 'quux', 'bang' ];

		$key = '';
		$value = null;
		$resource = $this->_mockResource();

		$resource
			->expects($this->any())
			->method('setList')
			->with($this->isType('string'), $this->isType('array'))
			->willReturnCallback(function (string $k, array $v) use (& $key, & $value, & $resource) : IResourceMap {
				$key = $k;
				$value = $v;

				return $resource;
			});

		$map = $this->_mockMap($resource);

		$this->assertEquals($map, $map->setList('bar', $list0));
		$this->assertEquals('foo_bar', $key);
		$this->assertEquals($list0, $value);
		$this->assertEquals($map, $map->setList('baz', $list1));
		$this->assertEquals('foo_baz', $key);
		$this->assertEquals($list1, $value);
	}


	public function testGetSet() {
		$set0 = [ 'foo', 'bar', 'baz' ];
		$set1 = [ 'quux', 'bang', 'foo' ];
		$resource = $this->_mockResource();

		$resource
			->expects($this->at(0))
			->method('getSet')
			->with($this->equalTo('foo_bar'))
			->willReturn($set0);

		$resource
			->expects($this->at(1))
			->method('getSet')
			->with($this->equalTo('foo_baz'))
			->willReturn($set1);

		$map = $this->_mockMap($resource);

		$this->assertEquals($set0, $map->getSet('bar'));
		$this->assertEquals($set1, $map->getSet('baz'));
	}

	public function testSetSet() {
		$set0 = [ 'foo', 'bar', 'baz' ];
		$set1 = [ 'quux', 'bang', 'foo' ];

		$key = '';
		$value = null;
		$resource = $this->_mockResource();

		$resource
			->expects($this->any())
			->method('setSet')
			->with($this->isType('string'), $this->isType('array'))
			->willReturnCallback(function(string $k, array $v) use (& $key, & $value, & $resource) : IResourceMap {
				$key = $k;
				$value = $v;

				return $resource;
			});

		$map = $this->_mockMap($resource);

		$this->assertEquals($map, $map->setSet('bar', $set0));
		$this->assertEquals('foo_bar', $key);
		$this->assertEquals($set0, $value);
		$this->assertEquals($map, $map->setSet('baz', $set1));
		$this->assertEquals('foo_baz', $key);
		$this->assertEquals($set1, $value);
	}


	public function testGetMap() {
		$map0 = [ 'foo' => 1, 'bar' => 2 ];
		$map1 = [ 'baz' => 3, 'bang' => 4 ];

		$resource = $this->_mockResource();

		$resource
			->expects($this->at(0))
			->method('getMap')
			->with($this->equalTo('foo_bar'))
			->willReturn($map0);

		$resource
			->expects($this->at(1))
			->method('getMap')
			->with($this->equalTo('foo_baz'))
			->willReturn($map1);

		$map = $this->_mockMap($resource);

		$this->assertEquals($map0, $map->getMap('bar'));
		$this->assertEquals($map1, $map->getMap('baz'));
	}

	public function testSetMap() {
		$map0 = [ 'foo' => 1, 'bar' => 2 ];
		$map1 = [ 'baz' => 3, 'quux' => 4 ];

		$key = '';
		$value = null;
		$resource = $this->_mockResource();

		$resource
			->expects($this->any())
			->method('setMap')
			->with($this->isType('string'), $this->isType('array'))
			->willReturnCallback(function(string $k, array $v) use (& $key, & $value, & $resource) : IResourceMap {
				$key = $k;
				$value = $v;

				return $resource;
			});

		$map = $this->_mockMap($resource);

		$this->assertEquals($map, $map->setMap('bar', $map0));
		$this->assertEquals('foo_bar', $key);
		$this->assertEquals($map0, $value);
		$this->assertEquals($map, $map->setMap('baz', $map1));
		$this->assertEquals('foo_baz', $key);
		$this->assertEquals($map1, $value);
	}


	public function testRemoveKey() {
		$props = [
			'bang_foo' => 1,
			'bang_bar' => 2
		];

		$resource = $this->_mockResource();

		$resource
			->expects($this->any())
			->method('removeKey')
			->with($this->isType('string'))
			->willReturnCallback(function(string $key) use (& $resource, & $props) {
				if (array_key_exists($key, $props)) unset($props[$key]);

				return $resource;
			});

		$map = $this->_mockMap($resource, 'bang');

		$this->assertEquals($map, $map->removeKey('foo'));
		$this->assertArrayNotHasKey('bang_foo', $props);
		$this->assertEquals($map, $map->removeKey('bar'));
		$this->assertArrayNotHasKey('bang_bar', $props);
	}

	public function testRenameKey() {
		$props = [
			'bang_foo' => 1,
			'bang_bar' => 2
		];

		$resource = $this->_mockResource();

		$resource
			->expects($this->any())
			->method('renameKey')
			->with($this->isType('string'), $this->isType('string'))
			->willReturnCallback(function(string $key, string $to) use (& $resource, & $props) {
				if (array_key_exists($key, $props)) {
					$props[$to] = $props[$key];
					unset($props[$key]);
				}

				return $resource;
			});

		$map = $this->_mockMap($resource, 'bang');

		$this->assertEquals($map, $map->renameKey('foo', 'baz'));
		$this->assertArrayNotHasKey('bang_foo', $props);
		$this->assertArrayHasKey('bang_baz', $props);
		$this->assertEquals(1, $props['bang_baz']);
		$this->assertEquals($map, $map->renameKey('bar', 'quux'));
		$this->assertArrayNotHasKey('bang_bar', $props);
		$this->assertArrayHasKey('bang_quux', $props);
		$this->assertEquals(2, $props['bang_quux']);
	}
}
