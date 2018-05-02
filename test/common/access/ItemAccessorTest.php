<?php

namespace test\common\access;

use PHPUnit\Framework\TestCase;
use lola\common\access\IAccessorSelector;
use lola\common\access\ItemAccessor;
use lola\common\access\exception\AccessorException;



final class ItemAccessorTest
extends TestCase
{

	private function _mockAccessorSelector(array& $data = []) {
		$prop = '';

		$selector = $this
			->getMockBuilder(IAccessorSelector::class)
			->getMock();

		$selector
			->method('select')
			->with(
				$this->equalTo($data),
				$this->isType('string')
			)
			->willReturnCallback(function(array& $data, string $key) use (& $prop, $selector) {
				$prop = $key;

				return $selector;
			});

		$selector
			->method('isResolved')
			->willReturnCallback(function() use (& $data, & $prop) {
				return array_key_exists($prop, $data);
			});

		$selector
			->method('getResolvedItem')
			->willReturnCallback(function() use (& $data, & $prop) {
				$item = array_key_exists($prop, $data) ? $data[$prop] : $data;

				return $item;
			});

		return $selector;
	}

	private function _produceAccessor(IAccessorSelector $selector = null, array& $data = []) {
		if (is_null($selector)) $selector = $this->_mockAccessorSelector();

		return new ItemAccessor($selector, $data);
	}

	public function testInheritance() {
		$access = $this->_produceAccessor();

		$this->assertInstanceOf(\eve\common\access\IKeyAccessor::class, $access);
		$this->assertInstanceOF(\eve\common\access\IItemAccessor::class, $access);
	}


	public function test_useData() {
		$data = [ 'foo' => 1 ];
		$access = $this->_produceAccessor(null,$data);
		$method = new \ReflectionMethod($access, '_useData');
		$method->setAccessible(true);

		$ref =& $method->getClosure($access)();

		$this->assertSame($data, $ref);

		$ref['bar'] = 2;

		$this->assertSame($data, $ref);

	}

	public function test_getSelector() {
		$select = $this->_mockAccessorSelector();
		$access = $this->_produceAccessor($select);
		$method = new \ReflectionMethod($access, '_getSelector');
		$method->setAccessible(true);

		$this->assertSame($select, $method->invoke($access));
	}


	public function test_select() {
		$data = [];
		$select = $this
			->getMockBuilder(IAccessorSelector::class)
			->getMock();

		$select
			->expects($this->once())
			->method('select')
			->with(
				$this->equalTo($data),
				$this->equalTo('foo')
			)
			->willReturnSelf();

		$access = $this->_produceAccessor($select, $data);
		$method = new \ReflectionMethod($access, '_select');
		$method->setAccessible(true);

		$this->assertSame($select, $method->invoke($access, 'foo'));
	}


	public function testHasKey() {
		$data = [ 'foo' => true ];
		$select = $this->_mockAccessorSelector($data);
		$access = $this->_produceAccessor($select, $data);

		$this->assertTrue($access->hasKey('foo'));
		$this->assertFalse($access->hasKey('bar'));
	}

	public function testGetItem() {
		$data = [ 'foo' => 1, 'bar' => 2 ];
		$select = $this->_mockAccessorSelector($data);
		$access = $this->_produceAccessor($select, $data);

		$this->assertEquals(1, $access->getItem('foo'));
		$this->assertEquals(2, $access->getItem('bar'));
	}

	public function testGetItem_noProp() {
		$data = [];
		$select = $this
			->getMockBuilder(IAccessorSelector::class)
			->getMock();

		$select
			->method('select')
			->willReturnSelf();

		$select
			->method('isResolved')
			->willReturn(false);

		$select
			->method('hasAccessFailure')
			->willReturn(true);

		$select
			->method('hasBranchFailure')
			->willReturn(false);

		$select
			->method('getPath')
			->willReturn('foo');

		$access = $this->_produceAccessor($select,$data);

		$this->expectException(AccessorException::class);
		$this->expectExceptionMessage('ACC no property "foo"');

		$access->getItem('foo');
	}

	public function testGetItem_noBranch() {
		$data = [];
		$select = $this
			->getMockBuilder(IAccessorSelector::class)
			->getMock();

		$select
			->method('select')
			->willReturnSelf();

		$select
			->method('isResolved')
			->willReturn(false);

		$select
			->method('hasAccessFailure')
			->willReturn(false);

		$select
			->method('hasBranchFailure')
			->willReturn(true);

		$select
			->method('getPath')
			->willReturn('foo');


		$access = $this->_produceAccessor($select,$data);

		$this->expectException(AccessorException::class);
		$this->expectExceptionMessage('ACC no branch "foo"');

		$access->getItem('foo');
	}
}
