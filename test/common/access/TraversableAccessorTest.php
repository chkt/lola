<?php

namespace test\common\access;

use PHPUnit\Framework\TestCase;
use eve\common\base\IMethodProxy;
use eve\common\projection\IProjectable;
use lola\common\access\IAccessorSelector;
use lola\common\access\TraversableAccessor;



final class TraversableAccessorTest
extends TestCase
{

	private function _mockInterface(string $qname) {
		$ins = $this
			->getMockBuilder($qname)
			->getMock();

		return $ins;
	}

	private function _mockProjectable(array $data) {
		$ins = $this->_mockInterface(IProjectable::class);

		$ins
			->method('getProjection')
			->willReturn($data);

		return $ins;
	}

	private function _produceAccessor(
		array& $data = [],
		IMethodProxy $proxy = null,
		IAccessorSelector $selector = null
	) {
		if (is_null($proxy)) $proxy = $this->_mockInterface(IMethodProxy::class);
		if (is_null($selector)) $selector = $this->_mockInterface(IAccessorSelector::class);

		return new TraversableAccessor($proxy, $selector, $data);
	}


	public function testInheritance() {
		$access = $this->_produceAccessor();

		$this->assertInstanceOf(\lola\common\access\ItemAccessor::class, $access);
		$this->assertInstanceOf(\eve\common\access\ITraversableAccessor::class, $access);
		$this->assertInstanceOf(\lola\common\projection\IFilterProjectable::class, $access);
	}


	public function test_getSelector() {
		$data = [];
		$proxy = $this->_mockInterface(IMethodProxy::class);
		$access = $this->_produceAccessor($data, $proxy);
		$method = new \ReflectionMethod($access, '_getMethodProxy');
		$method->setAccessible(true);

		$this->assertSame($proxy, $method->invoke($access));
	}


	public function testIsEqual() {
		$data = [ 'foo' => 1 ];
		$access = $this->_produceAccessor($data);

		$this->assertTrue($access->isEqual($access));
		$this->assertTrue($access->isEqual($this->_mockProjectable($data)));
		$this->assertTrue($access->isEqual($this->_mockProjectable([ 'foo' => 1 ])));
		$this->assertFalse($access->isEqual($this->_mockProjectable([ 'foo' => 2 ])));
	}

	public function testIterate() {
		$data = [ 1, 2, 3];
		$proxy = $this->_mockInterface(IMethodProxy::class);

		$proxy
			->method('callMethod')
			->with(
				$this->equalto(\lola\common\base\ArrayOperation::class),
				$this->equalTo('iterate'),
				$this->logicalAnd(
					$this->isType('array'),
					$this->countOf(1)
				)
			)
			->willReturnCallback(function& (string $qname, string $method, array $args) use ($data) {
				$this->assertEquals($data, $args[0]);

				$one = 1;
				$two = 2;

				yield 'foo' => $one;
				yield 'bar' => $two;
			});

		$access = $this->_produceAccessor($data, $proxy);
		$gen = $access->iterate();

		$this->assertInstanceOf(\Generator::class, $gen);
		$this->assertEquals('foo', $gen->key());
		$this->assertEquals(1, $gen->current());
		$gen->next();
		$this->assertTrue($gen->valid());
		$this->assertEquals('bar', $gen->key());
		$this->assertEquals(2, $gen->current());
		$gen->next();
		$this->assertFalse($gen->valid());

	}

	public function testGetProjection_unfiltered() {
		$data = [ 'foo' => 1, 'bar' => 2];
		$access = $this->_produceAccessor($data);

		$this->assertSame($data, $access->getProjection());

		$data['foo'] = 3;

		$this->assertEquals($data, $access->getProjection());
	}

	public function testGetProjection_filtered() {
		$data = [ 'foo' => 1, 'bar' => 2];
		$selector = $this->_mockInterface(IAccessorSelector::class);

		$selectorRef = new \stdClass();

		$selector
			->method('select')
			->with(
				$this->isType('array'),
				$this->isType('string')
			)
			->willReturnCallback(function(array & $data, string $key) use ($selectorRef, $selector) {
				if (!array_key_exists($key, $data)) $data[$key] = null;

				$selectorRef->item =& $data[$key];

				return $selector;
			});

		$selector
			->method('isResolved')
			->willReturn(true);

		$selector
			->method('linkAll')
			->willReturn($selector);

		$selector
			->method('getResolvedItem')
			->willReturnCallback(function() use ($selectorRef) {
				return $selectorRef->item;
			});

		$selector
			->method('setResolvedItem')
			->with($this->isType('int'))
			->willReturnCallback(function($value) use ($selectorRef, $selector) {

				$selectorRef->item = $value;

				return $selector;
			});

		$access = $this->_produceAccessor($data, null, $selector);

		$proj = $access->getProjection([ 'foo' ]);

		$this->assertArrayHasKey('foo', $proj);
		$this->assertEquals(1, $proj['foo']);
		$this->assertArrayNotHasKey('bar', $proj);
	}
}
